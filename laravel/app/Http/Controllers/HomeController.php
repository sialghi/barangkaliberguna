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
        // Tentukan bobot prioritas (semakin kecil semakin tinggi jabatannya)
        $priority = [
            'dekan' => 1,
            'wadek_satu' => 1,
            'wadek_dua' => 1,
            'wadek_tiga' => 1,
            'admin_dekanat' => 1,
            'kaprodi' => 2,
            'sekprodi' => 2,
            'admin_prodi' => 2,
            'dosen' => 3,
            'mahasiswa' => 4
        ];



        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();

        usort($userRole, function ($a, $b) use ($priority) {
            return ($priority[$a] ?? 99) <=> ($priority[$b] ?? 99);
        });
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with('role', 'programStudi', 'fakultas')
            ->orderBy('id_role', 'desc')
            ->get();

        $compactData = [
            'userRole' => $userRole,
            'userPivot' => $userPivot,
        ];

        $tabelNilai = 'nilai_skripsi';

        foreach ($userPivot as $pivot) {
            $programStudi = $pivot->id_program_studi;
            $fakultas = $pivot->id_fakultas;

            // --- DEKANAT ---
            if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)) {
                $suratTTD = Letter::whereHas('mahasiswa.fakultas', function ($q) use ($fakultas) {
                    $q->where('fakultas.id', $fakultas);
                })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.fakultas', function ($q) use ($fakultas) {
                    $q->where('fakultas.id', $fakultas);
                })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                $prodiFakultas = \App\Models\ProgramStudi::where('id_fakultas', $fakultas)->get();
                $chartLabels = [];
                $chartData = [];
                foreach ($prodiFakultas as $p) {
                    $count = DB::table('bimbingan_skripsi')
                        ->join('users_pivot', 'bimbingan_skripsi.id_mahasiswa', '=', 'users_pivot.id_user')
                        ->where('users_pivot.id_program_studi', $p->id)
                        ->distinct('bimbingan_skripsi.id_mahasiswa')
                        ->count();
                    $chartLabels[] = $p->nama;
                    $chartData[] = $count;
                }

                $allDosenFakultas = User::whereHas('pivot', function ($q) use ($fakultas) {
                    $q->where('id_fakultas', $fakultas)->whereHas('role', function ($rq) {
                        $rq->where('nama', 'dosen');
                    });
                })->with('pivot.programStudi')->get();
                $idDosenArray = $allDosenFakultas->pluck('id')->toArray();

                $bimbinganRecords = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->leftJoin($tabelNilai, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilai.id_mahasiswa")
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->select(
                        'bimbingan_skripsi.*',
                        'mhs.name',
                        'mhs.nim_nip_nidn',
                        DB::raw("CASE WHEN 
                            $tabelNilai.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilai.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                $monitoringDekanat = $allDosenFakultas->map(function ($dosen) use ($bimbinganRecords) {
                    $mhs = $bimbinganRecords->where('id_pembimbing', $dosen->id);
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
                    'chartLabels' => $chartLabels,
                    'chartData' => $chartData,
                    'monitoringDekanat' => $monitoringDekanat
                ]);
                return view('home', $compactData);
            }

            // --- KAPRODI / PRODI ---
            else if (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
                $suratTTD = Letter::whereHas('mahasiswa.programStudi', function ($q) use ($programStudi) {
                    $q->where('program_studi.id', $programStudi);
                })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.programStudi', function ($q) use ($programStudi) {
                    $q->where('program_studi.id', $programStudi);
                })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                $dosenProdi = User::whereHas('pivot', function ($q) use ($programStudi) {
                    $q->where('id_program_studi', $programStudi)->whereHas('role', function ($rq) {
                        $rq->where('nama', 'dosen');
                    });
                })->get();
                $idDosenArray = $dosenProdi->pluck('id')->toArray();

                $allBimbingan = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->join('users as dsn', 'bimbingan_skripsi.id_pembimbing', '=', 'dsn.id')
                    ->leftJoin($tabelNilai, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilai.id_mahasiswa")
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->select(
                        'bimbingan_skripsi.*',
                        'mhs.name as nama_mahasiswa',
                        'mhs.nim_nip_nidn as nim_mahasiswa',
                        'dsn.name as nama_dosen',
                        'dsn.id as id_dosen',
                        DB::raw("CASE WHEN 
                            $tabelNilai.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilai.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                $monitoringDosen = $dosenProdi->map(function ($dosen) use ($allBimbingan) {
                    $mhsBimbingan = $allBimbingan->where('id_dosen', $dosen->id);
                    return (object)[
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'total_mhs' => $mhsBimbingan->count(),
                        'ongoing' => $mhsBimbingan->where('is_finished', 0)->count(),
                        'finished' => $mhsBimbingan->where('is_finished', 1)->count(),
                        'students' => $mhsBimbingan
                    ];
                });

                $statsProdi = [
                    'totalDosen' => $dosenProdi->count(),
                    'totalMhs' => $allBimbingan->unique('id_mahasiswa')->count(),
                    'prodiOngoing' => $allBimbingan->where('is_finished', 0)->count(),
                    'prodiSelesai' => $allBimbingan->where('is_finished', 1)->count(),
                    'monitoringDosen' => $monitoringDosen
                ];

                $compactData = array_merge($compactData, $statsSurat, $statsProdi);
                return view('home', $compactData);
            }

            // --- DOSEN ---
            else if (in_array('dosen', $userRole)) {
                $suratPT = PermohonanTugas::where("id_user", $user->id)->get();
                $statsSurat = [
                    'totalSuratPT' => $suratPT->count(),
                    'PTdiproses' => $suratPT->where("status", "Sedang Diproses")->count(),
                    'PTditerima' => $suratPT->where("status", "Diterima")->count(),
                    'PTditolak' => $suratPT->where("status", "Ditolak")->count(),
                ];

                $bimbingan = DB::table('bimbingan_skripsi')
                    ->join('users', 'bimbingan_skripsi.id_mahasiswa', '=', 'users.id')
                    ->leftJoin($tabelNilai, 'bimbingan_skripsi.id_mahasiswa', '=', "$tabelNilai.id_mahasiswa")
                    ->where('bimbingan_skripsi.id_pembimbing', $user->id)
                    ->select(
                        'users.name as nama_mahasiswa',
                        'users.nim_nip_nidn as nim',
                        'bimbingan_skripsi.judul_skripsi',
                        'bimbingan_skripsi.sesi as jumlah_bimbingan',
                        DB::raw("CASE WHEN 
                            $tabelNilai.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilai.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilai.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->whereIn('bimbingan_skripsi.id', function ($query) use ($user) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->where('id_pembimbing', $user->id)
                            ->groupBy('id_mahasiswa');
                    })->get();

                $statsBimbingan = [
                    'bimbingan' => $bimbingan,
                    'totalBimbingan' => $bimbingan->count(),
                    'bimbinganOngoing' => $bimbingan->where('is_finished', 0)->count(),
                    'bimbinganSelesai' => $bimbingan->where('is_finished', 1)->count(),
                ];

                $compactData = array_merge($compactData, $statsSurat, $statsBimbingan);
                return view('home', $compactData);
            }

            // --- MAHASISWA ---
            else if (in_array('mahasiswa', $userRole)) {
                $suratTTD = Letter::where("id_mahasiswa", $user->id)->get();
                $compactData = array_merge($compactData, [
                    'totalSuratTTD' => $suratTTD->count(),
                    'belumTTD' => $suratTTD->where("status", 'Belum di TTD')->count(),
                    'sudahTTD' => $suratTTD->where("status", 'Sudah di TTD')->count(),
                    'ditolakTTD' => $suratTTD->where("status", 'Ditolak')->count(),
                ]);

                // Perhitungan Deadline Tugas Akhir
                //  Deadline SEMPRO  //
                $dataSempro = PendaftaranSempro::where('id_mahasiswa', $user->id)->where('status', 'diterima')->with('periodeSempro')->first();
                // 2. Hitung Deadline di Controller
                $deadlineSempro = null;

                if ($dataSempro) {
                    // Ambil tanggal periode
                    $rawDate = $dataSempro->periodeSempro->tanggal ?? $dataSempro->periodeSempro->updated_at;
                    $tanggalSempro = Carbon::parse($rawDate);

                    // Tambah 365 hari (1 tahun)
                    $deadlineSempro = $tanggalSempro->addDays(365);
                }

                //  Deadline SEMHAS  //
                $dataSemhas = PendaftaranSemhas::where('id_mahasiswa', $user->id)->first();

                $deadlineSemhas = null;

                if ($dataSemhas && $dataSemhas->waktu_seminar) {

                    $tglSemhas = \Carbon\Carbon::parse($dataSemhas->waktu_seminar);
                    $deadlineSemhas = $tglSemhas->addDays(90);
                }
                $dataSidang = PendaftaranSkripsi::where('id_mahasiswa', $user->id)->first();

                // MASUKKAN SEMUA KE COMPACT DATA AGAR RAPI
                $compactData = array_merge($compactData, [
                    'dataSempro' => $dataSempro,
                    'deadlineSempro' => $deadlineSempro,
                    'dataSemhas' => $dataSemhas,
                    'deadlineSemhas' => $deadlineSemhas,
                    'dataSidang' => $dataSidang
                ]);

                return view('home', $compactData);
            }
        }
        return view('home', $compactData);
    }

    /**
     * Helper untuk merangkum statistik surat (Dekan & Prodi)
     */
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
            'name'         => $user->name,
            'nim_nip_nidn' => $user->nim_nip_nidn,
            'email'        => $user->email,
            'no_hp'        => $user->no_hp,
        ];

        if (array_intersect(['dekan', 'kaprodi', 'admin_prodi', 'admin_dekanat'], $userRole)) {
            $response['role'] = $user->role;
            $response['ttd']  = $user->ttd;
        }

        return response()->json($response);
    }
}
