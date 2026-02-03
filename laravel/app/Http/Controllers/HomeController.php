<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\PermohonanTugas;
use App\Models\User;
use App\Models\UsersPivot;
use App\Models\PendaftaranSemhas;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSkripsi;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'email_verified']);
    }

    public function index()
    {
        // 1. Tentukan Prioritas Role
        $priority = [
            'dekan' => 1, 'wadek_satu' => 1, 'wadek_dua' => 1, 'wadek_tiga' => 1, 'admin_dekanat' => 1,
            'kaprodi' => 2, 'sekprodi' => 2, 'admin_prodi' => 2,
            'dosen' => 3, 'mahasiswa' => 4
        ];

        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();

        // Urutkan role user berdasarkan prioritas
        usort($userRole, function ($a, $b) use ($priority) {
            return ($priority[$a] ?? 99) <=> ($priority[$b] ?? 99);
        });

        // Ambil data Pivot User
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with('role', 'programStudi', 'fakultas')
            ->orderBy('id_role', 'desc')
            ->get();

        // 2. Inisialisasi Default Variable
        $compactData = [
            'userRole' => $userRole,
            'userPivot' => $userPivot,
            // Default Dosen
            'bimbingan' => collect([]),
            'totalBimbingan' => 0,
            'bimbinganOngoing' => 0,
            'bimbinganSelesai' => 0,
            'totalSuratPT' => 0,
            'PTdiproses' => 0,
            'PTditerima' => 0,
            'PTditolak' => 0,
            // Default Mahasiswa
            'totalSuratTTD' => 0,
            'belumTTD' => 0,
            'sudahTTD' => 0,
            'ditolakTTD' => 0,
        ];

        // 3. Definisi Nama Tabel
        $tabelNilaiSkripsi = 'nilai_skripsi'; // Tabel untuk cek kelulusan (4 nilai)
        $tabelSempro = 'nilai_sempro';        // Tabel untuk cek syarat Ongoing (Status Diterima)

        // 4. Flag Processed
        $isDekanatProcessed = false;
        $isKaprodiProcessed = false;
        $isDosenProcessed = false;
        $isMahasiswaProcessed = false;

        foreach ($userPivot as $pivot) {
            $programStudi = $pivot->id_program_studi;
            $fakultas = $pivot->id_fakultas;

            // ==========================================
            // LOGIKA ROLE: DEKANAT
            // ==========================================
            if (!$isDekanatProcessed && array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)) {
                
                // A. Statistik Surat
                $suratTTD = Letter::whereHas('mahasiswa.fakultas', function ($q) use ($fakultas) { $q->where('fakultas.id', $fakultas); })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.fakultas', function ($q) use ($fakultas) { $q->where('fakultas.id', $fakultas); })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                // B. Chart Data
                $prodiFakultas = \App\Models\ProgramStudi::where('id_fakultas', $fakultas)->get();
                $chartLabels = []; $chartData = [];
                
                foreach ($prodiFakultas as $p) {
                    $count = DB::table('bimbingan_skripsi')
                        ->join('users_pivot', 'bimbingan_skripsi.id_mahasiswa', '=', 'users_pivot.id_user')
                        ->join($tabelSempro, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelSempro.id_mahasiswa")
                        ->where('users_pivot.id_program_studi', $p->id)
                        ->where("$tabelSempro.status", 'Diterima') 
                        ->distinct('bimbingan_skripsi.id_mahasiswa')
                        ->count();
                    $chartLabels[] = $p->nama; $chartData[] = $count;
                }

                // C. Monitoring Dosen
                $allDosenFakultas = User::whereHas('pivot', function ($q) use ($fakultas) {
                    $q->where('id_fakultas', $fakultas)->whereHas('role', function ($rq) { $rq->where('nama', 'dosen'); });
                })->with('pivot.programStudi')->get();
                
                $idDosenArray = $allDosenFakultas->pluck('id')->toArray();

                // D. Query Bimbingan
                $bimbinganRecords = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->leftJoin($tabelNilaiSkripsi, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    ->join($tabelSempro, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelSempro.id_mahasiswa")
                    
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->where("$tabelSempro.status", 'Diterima') 
                    
                    ->select(
                        'bimbingan_skripsi.*',
                        'mhs.name',
                        'mhs.nim_nip_nidn',
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                // Mapping Data ke Dosen (DENGAN UNIQUE NAME)
                $monitoringDekanat = $allDosenFakultas->map(function ($dosen) use ($bimbinganRecords) {
                    // Filter berdasarkan dosen
                    $mhs = $bimbinganRecords->where('id_pembimbing', $dosen->id);
                    
                    // MODIFIKASI: Filter Unique berdasarkan Nama
                    // Jika ada nama sama (meski ID beda), ambil satu saja
                    $mhs = $mhs->unique('name'); 

                    return (object)[
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'prodi' => $dosen->pivot->first()->programStudi->nama ?? '-',
                        'total_mhs' => $mhs->count(),
                        'ongoing' => $mhs->where('is_finished', 0)->count(),
                        'finished' => $mhs->where('is_finished', 1)->count(),
                        'students' => $mhs
                    ];
                });

                $compactData = array_merge($compactData, $statsSurat, [
                    'chartLabels' => $chartLabels, 'chartData' => $chartData, 'monitoringDekanat' => $monitoringDekanat
                ]);
                
                $isDekanatProcessed = true;
            }

            // ==========================================
            // LOGIKA ROLE: KAPRODI / PRODI
            // ==========================================
            if (!$isKaprodiProcessed && array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
                
                $suratTTD = Letter::whereHas('mahasiswa.programStudi', function ($q) use ($programStudi) { $q->where('program_studi.id', $programStudi); })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.programStudi', function ($q) use ($programStudi) { $q->where('program_studi.id', $programStudi); })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                $dosenProdi = User::whereHas('pivot', function ($q) use ($programStudi) {
                    $q->where('id_program_studi', $programStudi)->whereHas('role', function ($rq) { $rq->where('nama', 'dosen'); });
                })->get();
                $idDosenArray = $dosenProdi->pluck('id')->toArray();

                $allBimbingan = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->join('users as dsn', 'bimbingan_skripsi.id_pembimbing', '=', 'dsn.id')
                    ->leftJoin($tabelNilaiSkripsi, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    ->join($tabelSempro, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelSempro.id_mahasiswa")
                    ->where("$tabelSempro.status", 'Diterima')

                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->select(
                        'bimbingan_skripsi.*',
                        'mhs.name as nama_mahasiswa', 'mhs.nim_nip_nidn as nim_mahasiswa',
                        'dsn.name as nama_dosen', 'dsn.id as id_dosen',
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                // Mapping Data ke Dosen (DENGAN UNIQUE NAME)
                $monitoringDosen = $dosenProdi->map(function ($dosen) use ($allBimbingan) {
                    $mhsBimbingan = $allBimbingan->where('id_dosen', $dosen->id);
                    
                    // MODIFIKASI: Filter Unique berdasarkan Nama Mahasiswa
                    $mhsBimbingan = $mhsBimbingan->unique('nama_mahasiswa');

                    return (object)[
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'total_mhs' => $mhsBimbingan->count(),
                        'ongoing' => $mhsBimbingan->where('is_finished', 0)->count(),
                        'finished' => $mhsBimbingan->where('is_finished', 1)->count(),
                        'students' => $mhsBimbingan
                    ];
                });

                // Hitung ulang statistik prodi berdasarkan data yang sudah di-unique
                // Kita ambil semua mahasiswa unik di prodi ini
                $allUniqueMhs = $allBimbingan->unique('nama_mahasiswa');

                $statsProdi = [
                    'totalDosen' => $dosenProdi->count(),
                    'totalMhs' => $allUniqueMhs->count(),
                    'prodiOngoing' => $allUniqueMhs->where('is_finished', 0)->count(),
                    'prodiSelesai' => $allUniqueMhs->where('is_finished', 1)->count(),
                    'monitoringDosen' => $monitoringDosen
                ];

                $compactData = array_merge($compactData, $statsSurat, $statsProdi);
                $isKaprodiProcessed = true;
            }

            // ==========================================
            // LOGIKA ROLE: DOSEN
            // ==========================================
            if (!$isDosenProcessed && in_array('dosen', $userRole)) {
                $suratPT = PermohonanTugas::where("id_user", $user->id)->get();
                $statsSuratDosen = [
                    'totalSuratPT' => $suratPT->count(),
                    'PTdiproses' => $suratPT->where("status", "Sedang Diproses")->count(),
                    'PTditerima' => $suratPT->where("status", "Diterima")->count(),
                    'PTditolak' => $suratPT->where("status", "Ditolak")->count(),
                ];

                $bimbingan = DB::table('bimbingan_skripsi')
                    ->join('users', 'bimbingan_skripsi.id_mahasiswa', '=', 'users.id')
                    ->leftJoin($tabelNilaiSkripsi, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    ->join($tabelSempro, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelSempro.id_mahasiswa")
                    ->where("$tabelSempro.status", 'Diterima')

                    ->where('bimbingan_skripsi.id_pembimbing', $user->id)
                    ->select(
                        'users.name as nama_mahasiswa',
                        'users.nim_nip_nidn as nim',
                        'bimbingan_skripsi.judul_skripsi',
                        'bimbingan_skripsi.sesi as jumlah_bimbingan',
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($user) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->where('id_pembimbing', $user->id)
                            ->groupBy('id_mahasiswa');
                    })->get();

                // MODIFIKASI: Filter Unique Dosen Dashboard
                $bimbingan = $bimbingan->unique('nama_mahasiswa');

                $statsBimbingan = [
                    'bimbingan' => $bimbingan,
                    'totalBimbingan' => $bimbingan->count(),
                    'bimbinganOngoing' => $bimbingan->where('is_finished', 0)->count(),
                    'bimbinganSelesai' => $bimbingan->where('is_finished', 1)->count(),
                ];

                $compactData = array_merge($compactData, $statsSuratDosen, $statsBimbingan);
                $isDosenProcessed = true;
            }

            // ==========================================
            // LOGIKA ROLE: MAHASISWA
            // ==========================================
            if (!$isMahasiswaProcessed && in_array('mahasiswa', $userRole)) {
                $suratTTD = Letter::where("id_mahasiswa", $user->id)->get();
                $compactData = array_merge($compactData, [
                    'totalSuratTTD' => $suratTTD->count(),
                    'belumTTD' => $suratTTD->where("status", 'Belum di TTD')->count(),
                    'sudahTTD' => $suratTTD->where("status", 'Sudah di TTD')->count(),
                    'ditolakTTD' => $suratTTD->where("status", 'Ditolak')->count(),
                ]);

                // Logika Deadline Mahasiswa
                $dataSempro = PendaftaranSempro::where('id_mahasiswa', $user->id)->where('status', 'diterima')->with('periodeSempro')->first();
                $deadlineSempro = null;
                if ($dataSempro) {
                    $rawDate = $dataSempro->periodeSempro->tanggal ?? $dataSempro->periodeSempro->updated_at;
                    $deadlineSempro = Carbon::parse($rawDate)->addDays(365);
                }

                $dataSemhas = PendaftaranSemhas::where('id_mahasiswa', $user->id)->first();
                $deadlineSemhas = null;
                if ($dataSemhas && $dataSemhas->waktu_seminar) {
                    $deadlineSemhas = Carbon::parse($dataSemhas->waktu_seminar)->addDays(90);
                }

                $dataSidang = PendaftaranSkripsi::where('id_mahasiswa', $user->id)->first();

                $compactData = array_merge($compactData, [
                    'dataSempro' => $dataSempro,
                    'deadlineSempro' => $deadlineSempro,
                    'dataSemhas' => $dataSemhas,
                    'deadlineSemhas' => $deadlineSemhas,
                    'dataSidang' => $dataSidang
                ]);

                $isMahasiswaProcessed = true;
            }
        }

        return view('home', $compactData);
    }

    private function getStats($suratTTD, $suratPT)
    {
        return [
            'totalSuratTTD' => $suratTTD->count(),
            'belumTTD'      => $suratTTD->where("status", 'Belum di TTD')->count(),
            'sudahTTD'      => $suratTTD->where("status", 'Sudah di TTD')->count(),
            'ditolakTTD'    => $suratTTD->where("status", 'Ditolak')->count(),
            'totalSuratPT'  => $suratPT->count(),
            'PTdiproses'    => $suratPT->where("status", 'Sedang Diproses')->count(),
            'PTditerima'    => $suratPT->where("status", 'Diterima')->count(),
            'PTditolak'     => $suratPT->where("status", 'Ditolak')->count(),
        ];
    }

    public function showMahasiswa($id)
    {
        $user = User::find($id);
        $authUser = Auth::user();
        $userRole = $authUser->roles->pluck('nama')->toArray();

        $response = [
            'name' => $user->name,
            'nim_nip_nidn' => $user->nim_nip_nidn,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        if (array_intersect(['dekan', 'kaprodi', 'admin_prodi', 'admin_dekanat'], $userRole)) {
            $response['role'] = $user->role;
            $response['ttd'] = $user->ttd;
        }

        return response()->json($response);
    }
}