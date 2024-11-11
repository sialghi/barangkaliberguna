<?php

namespace App\Http\Controllers;

use App\Models\BimbinganSkripsi;
use App\Models\NilaiSemhas;
use App\Models\User;
use App\Models\NilaiSempro;
use App\Models\NilaiSkripsi;
use Illuminate\Http\Request;

class StatistikDosenController extends Controller
{
    public function index()
    {
        // Fetch all users with role 'dosen'
        $dosen = User::where('role', ['dosen', 'kaprodi', 'sekprodi'])->get();

        // Prepare an array to hold the leaderboard data
        $leaderboard = [];

        // Loop through each dosen to count their appearances in nilai_sempro
        foreach ($dosen as $user) {
            $bimbinganSkripsiCount = BimbinganSkripsi::where('pembimbing_id', $user->id)
                                                    ->distinct('mahasiswa_id')
                                                    ->count('mahasiswa_id');

            $pengujiSemproCount = NilaiSempro::where('penguji_1_id', $user->id)
                                            ->orWhere('penguji_2_id', $user->id)
                                            ->orWhere('penguji_3_id', $user->id)
                                            ->orWhere('penguji_4_id', $user->id)
                                            ->count();
            $pengujiSemhasCount = NilaiSemhas::where('penguji_1_id', $user->id)
                                            ->orWhere('penguji_2_id', $user->id)
                                            ->count();
            $pengujiSidangCount = NilaiSkripsi::where('penguji_1_id', $user->id)
                                            ->orWhere('penguji_2_id', $user->id)
                                            ->count();
            
            // Add the total count to the leaderboard array
            $leaderboard[] = [
                'user' => $user,
                'mahasiswaBimbingan' => $bimbinganSkripsiCount,
                'pengujiSempro' => $pengujiSemproCount,
                'pengujiSemhas' => $pengujiSemhasCount,
                'pengujiSidang' => $pengujiSidangCount,
            ];
        }

        // return response()->json($leaderboard);

        // Pass the leaderboard data to the view
        return view('pages.monitoring.leaderboard_dosen', compact('leaderboard'));
    }
}
