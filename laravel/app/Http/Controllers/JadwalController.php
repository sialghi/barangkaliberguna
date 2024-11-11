<?php

namespace App\Http\Controllers;

use App\Models\NilaiSemhas;
use App\Models\NilaiSkripsi;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function indexSemhas()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = collect(); // Initialize the data collection to store merged data

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If the user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                $pivot->fakultas->programStudi;

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSemhas);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSemhas);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSemhas);
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSemhas);
            }
        }

        // Sort the data by 'created_at' ascending and make it unique by 'id'
        $data = $data->sortBy('created_at')->unique('id');

        // return response()->json($data);
        return view('pages.jadwal.semhas', compact('user', 'userRole', 'userPivot', 'data'));
    }

    public function indexSkripsi()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = collect(); // Initialize the data collection to store merged data

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If the user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                $pivot->fakultas->programStudi;

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSkripsi);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSkripsi);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSkripsi);
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['mahasiswa' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')->get();

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($nilaiSkripsi);
            }
        }

        // Sort the data by 'created_at' ascending and make it unique by 'id'
        $data = $data->sortBy('created_at')->unique('id');

        return view('pages.jadwal.skripsi', compact('user', 'userRole', 'userPivot', 'data'));
    }
}
