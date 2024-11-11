<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\PermohonanTugas;
use App\Models\User;
use App\Models\UsersPivot;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'email_verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // test code

        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $data = collect();

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudi = $pivot->id_program_studi;
            $fakultas = $pivot->id_fakultas;
            if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat',], $userRole)) {
                $suratTTD = Letter::whereHas('mahasiswa.fakultas', function($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas); // Adjust based on your column name
                })->get();
                $totalSuratTTD = $suratTTD->count();
                $belumTTD = $suratTTD->where("status", 'Belum di TTD')->count();
                $sudahTTD = $suratTTD->where("status", 'Sudah di TTD')->count();
                $ditolakTTD = $suratTTD->where("status", 'Ditolak')->count();

                $suratPT = PermohonanTugas::whereHas('dosen.fakultas', function($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas); // Adjust based on your column name
                })->get();
                $totalSuratPT = $suratPT->count();
                $PTdiproses = $suratPT->where("status", 'Sedang Diproses')->count();
                $PTditerima = $suratPT->where("status", 'Diterima')->count();
                $PTditolak = $suratPT->where("status", 'Ditolak')->count();

                return view('home', compact('userRole', 'userPivot', 'totalSuratTTD', 'belumTTD', 'sudahTTD', 'ditolakTTD', 'totalSuratPT', 'PTdiproses', 'PTditerima', 'PTditolak'));
            } else if (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
                $suratTTD = Letter::whereHas('mahasiswa.programStudi', function($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi); // Adjust based on your column name
                })->get();
                $totalSuratTTD = $suratTTD->count();
                $belumTTD = $suratTTD->where("status", 'Belum di TTD')->count();
                $sudahTTD = $suratTTD->where("status", 'Sudah di TTD')->count();
                $ditolakTTD = $suratTTD->where("status", 'Ditolak')->count();

                $SuratPT = PermohonanTugas::whereHas('dosen.programStudi', function($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi); // Adjust based on your column name
                })->get();
                $totalSuratPT = $SuratPT->count();
                $PTdiproses = $SuratPT->where("status", 'Sedang Diproses')->count();
                $PTditerima = $SuratPT->where("status", 'Diterima')->count();
                $PTditolak = $SuratPT->where("status", 'Ditolak')->count();

                return view('home', compact('userRole', 'userPivot', 'totalSuratTTD', 'belumTTD', 'sudahTTD', 'ditolakTTD', 'totalSuratPT', 'PTdiproses', 'PTditerima', 'PTditolak'));
            }
            else if (in_array('dosen', $userRole)) {
                $suratPT = PermohonanTugas::where("id_user", $user->id)->get();
                $totalSuratPT = $suratPT->count();
                $PTdiproses = $suratPT->where("status", "Sedang Diproses")->count();
                $PTditerima = $suratPT->where("status", "Diterima")->count();
                $PTditolak = $suratPT->where("status", "Ditolak")->count();

                return view('home', compact('userRole', 'userPivot', 'totalSuratPT', 'PTdiproses', 'PTditerima', 'PTditolak'));
            }
            else if (in_array('mahasiswa', $userRole)) {
                $suratTTD = Letter::where("id_mahasiswa", $user->id)->get();
                $totalSuratTTD = $suratTTD->count();
                $belumTTD = $suratTTD->where("status", 'Belum di TTD')->count();
                $sudahTTD = $suratTTD->where("status", 'Sudah di TTD')->count();
                $ditolakTTD = $suratTTD->where("status", 'Ditolak')->count();

                return view('home', compact('userRole', 'userPivot', 'totalSuratTTD', 'belumTTD', 'sudahTTD', 'ditolakTTD'));
            }
        }

        return view('home', compact('userRole', 'userPivot'));
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

        // Only include 'ttd' if the authenticated user is dekanat or prodi
        if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
            $response['role'] = $user->role;
            $response['ttd'] = $user->ttd;
        }

        return response()->json($response);
    }
}
