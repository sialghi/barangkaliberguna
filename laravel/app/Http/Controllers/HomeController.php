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

        usort($userRole, function ($a, $b) use ($priority) {
            return ($priority[$a] ?? 99) <=> ($priority[$b] ?? 99);
        });

        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with('role', 'programStudi', 'fakultas')
            ->orderBy('id_role', 'desc')
            ->get();

        // 2. Inisialisasi Default
        $compactData = [
            'userRole' => $userRole,
            'userPivot' => $userPivot,
            'bimbingan' => collect([]),
            'totalBimbingan' => 0,
            'bimbinganOngoing' => 0,
            'bimbinganSelesai' => 0,
            'totalSuratPT' => 0,
            'PTdiproses' => 0,
            'PTditerima' => 0,
            'PTditolak' => 0,
            'totalSuratTTD' => 0,
            'belumTTD' => 0,
            'sudahTTD' => 0,
            'ditolakTTD' => 0,
        ];

        // 3. Definisi Nama Tabel
        $tabelNilaiSkripsi = 'nilai_skripsi'; 
        $tabelSempro = 'nilai_sempro';        

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
                
                $suratTTD = Letter::whereHas('mahasiswa.fakultas', function ($q) use ($fakultas) { $q->where('fakultas.id', $fakultas); })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.fakultas', function ($q) use ($fakultas) { $q->where('fakultas.id', $fakultas); })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                // Chart Data
                $prodiFakultas = \App\Models\ProgramStudi::where('id_fakultas', $fakultas)->get();
                $chartLabels = []; $chartData = [];
                
                foreach ($prodiFakultas as $p) {
                    $count = DB::table($tabelSempro)
                        ->join('users_pivot', "$tabelSempro.id_mahasiswa", '=', 'users_pivot.id_user')
                        ->where('users_pivot.id_program_studi', $p->id)
                        ->where("$tabelSempro.status", 'Diterima') 
                        ->distinct("$tabelSempro.id_mahasiswa")
                        ->count();
                    $chartLabels[] = $p->nama; $chartData[] = $count;
                }

                // Monitoring Dosen
                $allDosenFakultas = User::whereHas('pivot', function ($q) use ($fakultas) {
                    $q->where('id_fakultas', $fakultas)->whereHas('role', function ($rq) { $rq->where('nama', 'dosen'); });
                })->with('pivot.programStudi')->get();
                
                $idDosenArray = $allDosenFakultas->pluck('id')->toArray();

                $bimbinganAgg = DB::table('bimbingan_skripsi')
                    ->whereNull('deleted_at')
                    ->select(
                        'id_mahasiswa',
                        'id_pembimbing',
                        DB::raw('COUNT(*) as jumlah_bimbingan'),
                        DB::raw('MAX(judul_skripsi) as judul_skripsi')
                    )
                    ->groupBy('id_mahasiswa', 'id_pembimbing');

                $bimbinganRecords = DB::table($tabelSempro)
                    ->join('users as mhs', "$tabelSempro.id_mahasiswa", '=', 'mhs.id')
                    // Relasi Dosen: Ambil dari nilai_sempro
                    ->join('users as dsn', "$tabelSempro.id_pembimbing_1", '=', 'dsn.id')
                    // Left Join
                    ->leftJoinSub($bimbinganAgg, 'bimbingan_skripsi', function ($join) use ($tabelSempro) {
                        $join->on("$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                            ->on("$tabelSempro.id_pembimbing_1", '=', 'bimbingan_skripsi.id_pembimbing');
                    })
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
                    ->where("$tabelSempro.status", 'Diterima')
                    ->whereIn("$tabelSempro.id_pembimbing_1", $idDosenArray)
                    
                    ->select(
                        // PERBAIKAN JUDUL: Ambil dari bimbingan, kalau null ambil dari proposal sempro
                        DB::raw("COALESCE(bimbingan_skripsi.judul_skripsi, $tabelSempro.judul_proposal) as judul_skripsi"),
                        DB::raw('COALESCE(bimbingan_skripsi.jumlah_bimbingan, 0) as sesi'),
                        'mhs.name',
                        'mhs.nim_nip_nidn',
                        'dsn.id as id_pembimbing_real',    
                        'dsn.name as nama_dosen',          
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->get();

                $monitoringDekanat = $allDosenFakultas->map(function ($dosen) use ($bimbinganRecords) {
                    $mhs = $bimbinganRecords->where('id_pembimbing_real', $dosen->id);
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

                $bimbinganAgg = DB::table('bimbingan_skripsi')
                    ->whereNull('deleted_at')
                    ->select(
                        'id_mahasiswa',
                        'id_pembimbing',
                        DB::raw('COUNT(*) as jumlah_bimbingan'),
                        DB::raw('MAX(judul_skripsi) as judul_skripsi')
                    )
                    ->groupBy('id_mahasiswa', 'id_pembimbing');

                $allBimbingan = DB::table($tabelSempro)
                    ->join('users as mhs', "$tabelSempro.id_mahasiswa", '=', 'mhs.id')
                    ->join('users as dsn', "$tabelSempro.id_pembimbing_1", '=', 'dsn.id')
                    ->leftJoinSub($bimbinganAgg, 'bimbingan_skripsi', function ($join) use ($tabelSempro) {
                        $join->on("$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                            ->on("$tabelSempro.id_pembimbing_1", '=', 'bimbingan_skripsi.id_pembimbing');
                    })
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
                    ->where("$tabelSempro.status", 'Diterima')
                    ->whereIn("$tabelSempro.id_pembimbing_1", $idDosenArray)
                    
                    ->select(
                        // PERBAIKAN JUDUL
                        DB::raw("COALESCE(bimbingan_skripsi.judul_skripsi, $tabelSempro.judul_proposal) as judul_skripsi"),
                        DB::raw('COALESCE(bimbingan_skripsi.jumlah_bimbingan, 0) as sesi'),
                        'mhs.name as nama_mahasiswa', 'mhs.nim_nip_nidn as nim_mahasiswa',
                        'dsn.name as nama_dosen', 'dsn.id as id_dosen_real',
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->get();

                $monitoringDosen = $dosenProdi->map(function ($dosen) use ($allBimbingan) {
                    $mhsBimbingan = $allBimbingan->where('id_dosen_real', $dosen->id);
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

                $allUniqueMhs = $allBimbingan->unique('nama_mahasiswa');

                $statsProdi = [
                    'totalDosen' => $dosenProdi->count(),
                    'totalMhs' => $allUniqueMhs->count(),
                    'prodiOngoing' => $allUniqueMhs->where('is_finished', 0)->count(),
                    'prodiSelesai' => $allUniqueMhs->where('is_finished', 1)->count(),
                    'monitoringDosen' => $monitoringDosen
                ];

                // ==========================================
                // CHART TAMBAHAN (ADMIN_PRODI): Tepat Waktu Smt 9, Kategori TA, Beban Dosen
                // ==========================================
                $extraCharts = [];
                if (in_array('admin_prodi', $userRole, true)) {
                    // 1) Persentase lulus tepat waktu (<= 9 semester) berdasarkan NIM angkatan + waktu_ujian pendaftaran_skripsi
                    $angkatanExpr = "(2000 + CAST(SUBSTRING(u.nim_nip_nidn, 3, 2) AS UNSIGNED))";
                    $acadStartExpr = "(CASE WHEN MONTH(ps.waktu_ujian) <= 6 THEN YEAR(ps.waktu_ujian) - 1 ELSE YEAR(ps.waktu_ujian) END)";
                    $semesterExpr = "(({$acadStartExpr} - {$angkatanExpr}) * 2 + (CASE WHEN MONTH(ps.waktu_ujian) <= 6 THEN 2 ELSE 1 END))";

                    $onTimeRow = DB::table('pendaftaran_skripsi as ps')
                        ->join('users as u', 'ps.id_mahasiswa', '=', 'u.id')
                        ->join('users_pivot as up', 'ps.id_mahasiswa', '=', 'up.id_user')
                        ->whereNull('ps.deleted_at')
                        ->whereNull('u.deleted_at')
                        ->whereNull('up.deleted_at')
                        ->where('up.id_program_studi', $programStudi)
                        ->where('ps.status', 'Diterima')
                        ->whereNotNull('ps.waktu_ujian')
                        ->selectRaw("SUM(CASE WHEN {$semesterExpr} <= 9 THEN 1 ELSE 0 END) as tepat")
                        ->selectRaw("SUM(CASE WHEN {$semesterExpr} > 9 THEN 1 ELSE 0 END) as terlambat")
                        ->first();

                    $tepat = (int) ($onTimeRow->tepat ?? 0);
                    $terlambat = (int) ($onTimeRow->terlambat ?? 0);

                    // 2) Sebaran jenis tugas akhir (kategori_ta) dari pendaftaran_skripsi
                    $kategoriRows = DB::table('kategori_ta as k')
                        ->leftJoin('pendaftaran_skripsi as ps', function ($join) {
                            $join->on('ps.id_kategori_ta', '=', 'k.id')
                                ->whereNull('ps.deleted_at')
                                ->whereNotNull('ps.id_kategori_ta')
                                ->where('ps.status', 'Diterima');
                        })
                        ->leftJoin('users_pivot as up', function ($join) use ($programStudi) {
                            $join->on('ps.id_mahasiswa', '=', 'up.id_user')
                                ->whereNull('up.deleted_at')
                                ->where('up.id_program_studi', $programStudi);
                        })
                        ->select('k.nama')
                        ->selectRaw('COUNT(up.id_user) as total')
                        ->groupBy('k.id', 'k.nama')
                        ->orderBy('k.id')
                        ->get();

                    $extraCharts = [
                        'chartTepatWaktu' => [
                            'tepat' => $tepat,
                            'terlambat' => $terlambat,
                        ],
                        'chartKategoriTA' => [
                            'labels' => $kategoriRows->pluck('nama')->toArray(),
                            'data' => $kategoriRows->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
                        ],
                        'chartBebanDosen' => [
                            'labels' => $monitoringDosen->pluck('nama')->toArray(),
                            'finished' => $monitoringDosen->pluck('finished')->map(fn ($v) => (int) $v)->toArray(),
                            'ongoing' => $monitoringDosen->pluck('ongoing')->map(fn ($v) => (int) $v)->toArray(),
                        ],
                    ];
                }

                $compactData = array_merge($compactData, $statsSurat, $statsProdi, $extraCharts);
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

                $bimbinganAgg = DB::table('bimbingan_skripsi')
                    ->whereNull('deleted_at')
                    ->select(
                        'id_mahasiswa',
                        'id_pembimbing',
                        DB::raw('COUNT(*) as jumlah_bimbingan'),
                        DB::raw('MAX(judul_skripsi) as judul_skripsi')
                    )
                    ->groupBy('id_mahasiswa', 'id_pembimbing');

                $bimbingan = DB::table($tabelSempro)
                    ->join('users', "$tabelSempro.id_mahasiswa", '=', 'users.id')
                    ->leftJoinSub($bimbinganAgg, 'bimbingan_skripsi', function ($join) use ($tabelSempro) {
                        $join->on("$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
                            ->on("$tabelSempro.id_pembimbing_1", '=', 'bimbingan_skripsi.id_pembimbing');
                    })
                    ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
                    
                    ->where("$tabelSempro.status", 'Diterima')
                    // Filter: Dosen Pembimbing Sesuai Login
                    ->where("$tabelSempro.id_pembimbing_1", $user->id)
                    
                    ->select(
                        'users.name as nama_mahasiswa',
                        'users.nim_nip_nidn as nim',
                        // PERBAIKAN JUDUL
                        DB::raw("COALESCE(bimbingan_skripsi.judul_skripsi, $tabelSempro.judul_proposal) as judul_skripsi"),
                        DB::raw('COALESCE(bimbingan_skripsi.jumlah_bimbingan, 0) as jumlah_bimbingan'),
                        DB::raw("CASE WHEN 
                            $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                            $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                            THEN 1 ELSE 0 END as is_finished")
                    )
                    ->get();

                // Unique di PHP Collection
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

                // Logika Deadline
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