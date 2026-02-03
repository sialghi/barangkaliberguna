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

                // B. Chart Data (Logic Baru: Base Table = nilai_sempro)
                $prodiFakultas = \App\Models\ProgramStudi::where('id_fakultas', $fakultas)->get();
                $chartLabels = []; $chartData = [];
                
                foreach ($prodiFakultas as $p) {
                    $count = DB::table($tabelSempro)
                        ->join('users_pivot', "$tabelSempro.id_mahasiswa", '=', 'users_pivot.id_user')
                        ->where('users_pivot.id_program_studi', $p->id)
                        ->where("$tabelSempro.status", 'Diterima') // Filter: Hanya yang Diterima
                        ->distinct("$tabelSempro.id_mahasiswa")
                        ->count();
                    $chartLabels[] = $p->nama; $chartData[] = $count;
                }

                // C. Monitoring Dosen
                $allDosenFakultas = User::whereHas('pivot', function ($q) use ($fakultas) {
                    $q->where('id_fakultas', $fakultas)->whereHas('role', function ($rq) { $rq->where('nama', 'dosen'); });
                })->with('pivot.programStudi')->get();
                
                $idDosenArray = $allDosenFakultas->pluck('id')->toArray();

                // D. Query Bimbingan (Logic Baru: Base Table = nilai_sempro)
                $bimbinganRecords = DB::table($tabelSempro)
                    ->join('users as mhs', "$tabelSempro.id_mahasiswa", '=', 'mhs.id')
                    
                    // LEFT JOIN ke bimbingan_skripsi (Agar mhs tanpa pembimbing tetap terambil datanya secara teori)
                    // Namun nanti difilter whereIn id_pembimbing untuk mapping ke dosen.
                    ->leftJoin('bimbingan_skripsi', "$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                    
                    // LEFT JOIN ke nilai_skripsi (Untuk cek Finished)
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
                    // Filter Wajib: Sempro Diterima
                    ->where("$tabelSempro.status", 'Diterima')
                    
                    // Filter untuk List Dosen (Hanya ambil yang punya pembimbing di fakultas ini)
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    
                    ->select(
                        'bimbingan_skripsi.*', // Ambil data bimbingan (termasuk id_pembimbing)
                        'mhs.name',
                        'mhs.nim_nip_nidn',
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    // Pastikan ambil data terbaru jika ada duplikat di bimbingan_skripsi
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                // Mapping Data ke Dosen (Unique Name)
                $monitoringDekanat = $allDosenFakultas->map(function ($dosen) use ($bimbinganRecords) {
                    $mhs = $bimbinganRecords->where('id_pembimbing', $dosen->id);
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

                // Query Bimbingan (Base: Nilai Sempro)
                $allBimbingan = DB::table($tabelSempro)
                    ->join('users as mhs', "$tabelSempro.id_mahasiswa", '=', 'mhs.id')
                    // Left Join Bimbingan (Ambil info dosen)
                    ->leftJoin('bimbingan_skripsi', "$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                    // Join user Dosen (untuk ambil nama dosen)
                    ->join('users as dsn', 'bimbingan_skripsi.id_pembimbing', '=', 'dsn.id')
                    // Left Join Nilai Skripsi (Cek Finished)
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
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

                $monitoringDosen = $dosenProdi->map(function ($dosen) use ($allBimbingan) {
                    $mhsBimbingan = $allBimbingan->where('id_dosen', $dosen->id);
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

                // Hitung ulang statistik prodi
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

                // Query Bimbingan (Base: Nilai Sempro)
                $bimbingan = DB::table($tabelSempro)
                    ->join('users', "$tabelSempro.id_mahasiswa", '=', 'users.id')
                    ->leftJoin('bimbingan_skripsi', "$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
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