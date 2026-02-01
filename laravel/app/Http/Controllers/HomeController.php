<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\PermohonanTugas;
use App\Models\User;
use App\Models\UsersPivot;
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
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with('role', 'programStudi', 'fakultas')
            ->orderBy('id_role', 'desc')
            ->get();

        // Variabel Default
        $compactData = [
            'userRole' => $userRole,
            'userPivot' => $userPivot,
        ];

        foreach ($userPivot as $pivot) {
            $programStudi = $pivot->id_program_studi;
            $fakultas = $pivot->id_fakultas;

            // --- LOGIKA ROLE: DEKANAT ---
            if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)) {
            // 1. Ambil Stats Surat (Existing)
                $suratTTD = Letter::whereHas('mahasiswa.fakultas', function ($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas);
                })->get();
                $suratPT = PermohonanTugas::whereHas('dosen.fakultas', function ($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas);
                })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                // 2. Data Grafik (Dinamis & Akurat)
                $prodiFakultas = \App\Models\ProgramStudi::where('id_fakultas', $fakultas)->get();
                $chartLabels = [];
                $chartData = [];

                foreach ($prodiFakultas as $p) {
                    $count = DB::table('bimbingan_skripsi')
                        ->join('users_pivot', 'bimbingan_skripsi.id_mahasiswa', '=', 'users_pivot.id_user')
                        ->where('users_pivot.id_program_studi', $p->id)
                        ->distinct('bimbingan_skripsi.id_mahasiswa')
                        ->count();
                    
                    // Hanya masukkan ke chart jika ada datanya (opsional) atau masukkan semua prodi
                    $chartLabels[] = $p->nama;
                    $chartData[] = $count;
                }

                // 3. Monitoring Seluruh Dosen di Fakultas
                $allDosenFakultas = User::whereHas('pivot', function($q) use ($fakultas) {
                    $q->where('id_fakultas', $fakultas)->whereHas('role', function($rq) {
                        $rq->where('nama', 'dosen');
                    });
                })->with('pivot.programStudi')->get();

                $idDosenArray = $allDosenFakultas->pluck('id')->toArray();

                $bimbinganRecords = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->select('bimbingan_skripsi.*', 'mhs.name', 'mhs.nim_nip_nidn')
                    ->whereIn('bimbingan_skripsi.id', function($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })->get();

                $monitoringDekanat = $allDosenFakultas->map(function($dosen) use ($bimbinganRecords) {
                    $mhs = $bimbinganRecords->where('id_pembimbing', $dosen->id);
                    return (object)[
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'prodi' => $dosen->pivot->first()->programStudi->nama ?? '-',
                        'total_mhs' => $mhs->count(),
                        'ongoing' => $mhs->whereNull('id_nilai_sempro')->count(),
                        'finished' => $mhs->whereNotNull('id_nilai_sempro')->count(),
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
            
            // --- LOGIKA ROLE: KAPRODI / PRODI ---
            else if (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
                // 1. Ambil Stats Surat (Existing Logic)
                $suratTTD = Letter::whereHas('mahasiswa.programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->get();
                
                $suratPT = PermohonanTugas::whereHas('dosen.programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->get();
                $statsSurat = $this->getStats($suratTTD, $suratPT);

                // 2. Ambil Daftar Dosen di Prodi ini
                $dosenProdi = User::whereHas('pivot', function($q) use ($programStudi) {
                    $q->where('id_program_studi', $programStudi)->whereHas('role', function($rq) {
                        $rq->where('nama', 'dosen');
                    });
                })->get();

                $idDosenArray = $dosenProdi->pluck('id')->toArray();

                // 3. Ambil Semua Data Bimbingan dari Dosen-Dosen tersebut (Sesi Terbaru)
                $allBimbingan = DB::table('bimbingan_skripsi')
                    ->join('users as mhs', 'bimbingan_skripsi.id_mahasiswa', '=', 'mhs.id')
                    ->join('users as dsn', 'bimbingan_skripsi.id_pembimbing', '=', 'dsn.id')
                    ->whereIn('bimbingan_skripsi.id_pembimbing', $idDosenArray)
                    ->select(
                        'bimbingan_skripsi.*',
                        'mhs.name as nama_mahasiswa',
                        'mhs.nim_nip_nidn as nim_mahasiswa',
                        'dsn.name as nama_dosen',
                        'dsn.id as id_dosen'
                    )
                    ->whereIn('bimbingan_skripsi.id', function($query) use ($idDosenArray) {
                        $query->selectRaw('MAX(id)')
                            ->from('bimbingan_skripsi')
                            ->whereIn('id_pembimbing', $idDosenArray)
                            ->groupBy('id_mahasiswa', 'id_pembimbing');
                    })
                    ->get();

                // 4. Mapping Data untuk Tabel Monitoring (Dosen -> Mahasiswa)
                $monitoringDosen = $dosenProdi->map(function($dosen) use ($allBimbingan) {
                    $mhsBimbingan = $allBimbingan->where('id_dosen', $dosen->id);
                    return (object)[
                        'id' => $dosen->id,
                        'nama' => $dosen->name,
                        'total_mhs' => $mhsBimbingan->count(),
                        'ongoing' => $mhsBimbingan->whereNull('id_nilai_sempro')->count(),
                        'finished' => $mhsBimbingan->whereNotNull('id_nilai_sempro')->count(),
                        'students' => $mhsBimbingan
                    ];
                });

                $statsProdi = [
                    'totalDosen' => $dosenProdi->count(),
                    'totalMhs' => $allBimbingan->unique('id_mahasiswa')->count(),
                    'prodiOngoing' => $allBimbingan->whereNull('id_nilai_sempro')->count(),
                    'prodiSelesai' => $allBimbingan->whereNotNull('id_nilai_sempro')->count(),
                    'monitoringDosen' => $monitoringDosen
                ];

                $compactData = array_merge($compactData, $statsSurat, $statsProdi);
                return view('home', $compactData);
            }
            
            // --- LOGIKA ROLE: DOSEN (INTEGRASI BARU) ---
            else if (in_array('dosen', $userRole)) {
                // 1. Statistik Surat Tugas Dosen
                $suratPT = PermohonanTugas::where("id_user", $user->id)->get();
                $statsSurat = [
                    'totalSuratPT' => $suratPT->count(),
                    'PTdiproses'   => $suratPT->where("status", "Sedang Diproses")->count(),
                    'PTditerima'   => $suratPT->where("status", "Diterima")->count(),
                    'PTditolak'    => $suratPT->where("status", "Ditolak")->count(),
                ];

                // 2. Data Mahasiswa Bimbingan (Ambil dari tabel bimbingan_skripsi)
                $bimbingan = DB::table('bimbingan_skripsi')
                    ->join('users', 'bimbingan_skripsi.id_mahasiswa', '=', 'users.id')
                    // Gunakan ID user yang sedang login, bukan angka 8
                    ->where('bimbingan_skripsi.id_pembimbing', $user->id) 
                    ->select(
                        'users.name as nama_mahasiswa', 
                        'users.nim_nip_nidn as nim', 
                        'bimbingan_skripsi.judul_skripsi',
                        'bimbingan_skripsi.sesi as jumlah_bimbingan',
                        'bimbingan_skripsi.id_nilai_sempro'
                    )
                    // PERBAIKAN LOGIKA: Subquery harus memfilter ID Pembimbing agar data tidak tertutup dosen lain
                    ->whereIn('bimbingan_skripsi.id', function($query) use ($user) {
                        $query->selectRaw('MAX(id)')
                            ->from('bimbingan_skripsi')
                            ->where('id_pembimbing', $user->id) // Cari sesi terbaru MILIK DOSEN INI
                            ->groupBy('id_mahasiswa');
                    })
                    ->get();

                $statsBimbingan = [
                    'bimbingan'         => $bimbingan,
                    'totalBimbingan'    => $bimbingan->count(),
                    'bimbinganOngoing'  => $bimbingan->whereNull('id_nilai_sempro')->count(),
                    'bimbinganSelesai'  => $bimbingan->whereNotNull('id_nilai_sempro')->count(),
                ];

                // Gabungkan data dan tampilkan ke view
                $compactData = array_merge($compactData, $statsSurat, $statsBimbingan);
                return view('home', $compactData);
            }
            
            // --- LOGIKA ROLE: MAHASISWA ---
            else if (in_array('mahasiswa', $userRole)) {
                $suratTTD = Letter::where("id_mahasiswa", $user->id)->get();
                
                $compactData = array_merge($compactData, [
                    'totalSuratTTD' => $suratTTD->count(),
                    'belumTTD'      => $suratTTD->where("status", 'Belum di TTD')->count(),
                    'sudahTTD'      => $suratTTD->where("status", 'Sudah di TTD')->count(),
                    'ditolakTTD'    => $suratTTD->where("status", 'Ditolak')->count(),
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