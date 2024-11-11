<?php

namespace App\Http\Controllers;

use App\Models\BimbinganSkripsi;
use App\Models\NilaiSempro;
use App\Models\User;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BimbinganSkripsiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();
        $data = collect(); // Initialize the main collection

        $dataCount = 0;

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $bimbinganSkripsi = BimbinganSkripsi::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with('mahasiswa', 'pembimbing', 'nilaiSempro')->get();
                $bimbinganSkripsi->each(function ($item) {
                    $item->role = 'admin';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $pivot->fakultas->programStudi;
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $bimbinganSkripsi = BimbinganSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with('mahasiswa', 'pembimbing', 'nilaiSempro')->get();
                $bimbinganSkripsi->each(function ($item) {
                    $item->role = 'admin';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $bimbinganSkripsi = BimbinganSkripsi::where('id_pembimbing', $user->id)
                                    ->with('mahasiswa', 'pembimbing', 'nilaiSempro')
                                    ->get();
                $bimbinganSkripsi->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $bimbinganSkripsi = BimbinganSkripsi::where('id_mahasiswa', $user->id)
                                    ->with('mahasiswa', 'pembimbing', 'nilaiSempro')
                                    ->get();
                $bimbinganSkripsi->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $dataCount = NilaiSempro::where('id_mahasiswa', $user->id)
                                    ->where('status', 'Diterima')
                                    ->count();
            }

            // Merge the fetched daftarSempro with the existing data collection
            $namaDosen = $namaDosen->merge($dosen);
            $data = $data->merge($bimbinganSkripsi);
        }
        $namaDosen = $namaDosen->sortBy('name')->unique('id');
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');

        return view('pages/monitoring/bimbingan_skripsi', compact('user', 'userRole', 'userPivot', 'data', 'dataCount', 'namaDosen'));
    }

    public function show($id)
    {
        $bimbinganSkripsi = BimbinganSkripsi::with('mahasiswa', 'pembimbing', 'nilaiSempro')->find($id);

        return response()->json([
            'bimbinganSkripsi' => $bimbinganSkripsi
        ]);
    }

    public function listMahasiswaBimbingan($id)
    {
        $nilaiSempro = NilaiSempro::with('mahasiswa', 'periodeSempro')->find($id);
        $namaDosen = User::where('id', $nilaiSempro->id_pembimbing_1)
                            ->orWhere('id', $nilaiSempro->id_pembimbing_2)
                            ->pluck('name', 'id');

        return response()->json([
            'nilaiSempro' => $nilaiSempro,
            'namaDosen' => $namaDosen
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = collect();
        $namaDosen = collect();
        $bimbinganTerakhir = null;

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $nilaiSempro = NilaiSempro::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with('mahasiswa', 'periodeSempro')
                    ->where('status', 'Diterima')
                    ->get();

                // Get the name of the dosen in the same fakultas as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $user->listMahasiswa = User::whereHas('roles', function($query) {
                    $query->where('nama', 'mahasiswa'); // Checking for 'mahasiswa' role
                })->whereHas('fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'nim_nip_nidn', 'name')
                    ->without(['pivot', 'roles'])
                    ->orderBy('nim_nip_nidn', 'asc') // Sort by nim_nip_nidn in ascending order
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $nilaiSempro = NilaiSempro::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with('mahasiswa', 'periodeSempro')
                    ->where('status', 'Diterima')
                    ->get();

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $user->listMahasiswa = User::whereHas('roles', function($query) {
                    $query->where('nama', 'mahasiswa'); // Checking for 'mahasiswa' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'nim_nip_nidn', 'name')
                    ->without(['pivot', 'roles'])
                    ->orderBy('nim_nip_nidn', 'asc') // Sort by nim_nip_nidn in ascending order
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $nilaiSempro = NilaiSempro::with('mahasiswa', 'periodeSempro')
                                ->where('status', 'Diterima')
                                // ->where('id_pembimbing_2', $user->id)
                                ->where(function($query) use ($userId) {
                                    $query->where('id_pembimbing_1', $userId)
                                        ->orWhere('id_pembimbing_2', $userId);
                                })
                                ->get();

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $nilaiSempro = NilaiSempro::where('id_mahasiswa', $user->id)
                                    ->where('status', 'Diterima')
                                    ->latest('updated_at')
                                    ->get();

                $dosenPembimbingId = NilaiSempro::where('id_mahasiswa', $user->id)
                                    ->where('status', 'Diterima')
                                    ->get(['id_pembimbing_1', 'id_pembimbing_2']) // Get both columns
                                    ->flatMap(function ($item) {
                                        return [$item->id_pembimbing_1, $item->id_pembimbing_2]; // Combine both into a flat array
                                    })
                                    ->filter() // Remove null values if necessary
                                    ->toArray();

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereIn('id', $dosenPembimbingId)
                                ->select('id', 'name')
                                ->without(['pivot', 'roles'])
                                ->get();

                $bimbinganTerakhir = BimbinganSkripsi::where('id_mahasiswa', $user->id)
                                ->latest('updated_at')
                                ->first();

                $namaDosen = $namaDosen->merge($dosen);
            }
            $data = $data->merge($nilaiSempro);
        }

        // $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at')->unique('id');
        // $data = $data->unique('id')->values();
        $namaDosen = $namaDosen->sortBy('name')->unique('id');


        return view('pages/monitoring/bimbingan_skripsi_add', compact('user', 'userRole', 'userPivot', 'namaDosen', 'data', 'bimbinganTerakhir'));
    }

    public function store(Request $request)
    {
        $rules = [
            'mahasiswaId' => 'required|exists:users,id',
            'dosenPembimbing' => 'required|exists:users,id',
            'judulSkripsi' => 'required|string|max:191',
            'sesiBimbingan' => 'required|string|max:191',
            'tanggalBimbingan' => 'required|date',
            'jenisBimbingan' => 'required|string|max:191',
        ];

        $customMessages = [
            'mahasiswaId.required' => 'Mahasiswa wajib diisi',
            'mahasiswaId.exists' => 'Mahasiswa tidak valid',

            'dosenPembimbing.required' => 'Pembimbing wajib diisi',
            'dosenPembimbing.exists' => 'Pembimbing tidak valid',

            'judulSkripsi.required' => 'Judul Skripsi wajib diisi',
            'judulSkripsi.string' => 'Judul Skripsi harus berupa string',
            'judulSkripsi.max' => 'Judul Skripsi tidak boleh melebihi 191 karakter',

            'sesiBimbingan.required' => 'Sesi Bimbingan wajib diisi',
            'sesiBimbingan.string' => 'Sesi Bimbingan harus berupa string',
            'sesiBimbingan.max' => 'Sesi Bimbingan tidak boleh melebihi 191 karakter',

            'tanggalBimbingan.required' => 'Tanggal Bimbingan wajib diisi',
            'tanggalBimbingan.date' => 'Tanggal Bimbingan harus berupa tanggal',

            'jenisBimbingan.required' => 'Jenis Bimbingan wajib diisi',
            'jenisBimbingan.string' => 'Jenis Bimbingan harus berupa string',
            'jenisBimbingan.max' => 'Jenis Bimbingan tidak boleh melebihi 191 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        DB::beginTransaction();

        try {
            BimbinganSkripsi::create([
                'id_mahasiswa' => $request->mahasiswaId,
                'id_pembimbing' => $request->dosenPembimbing,
                'id_nilai_sempro' => $request->nilaiSemproSelect,
                'judul_skripsi' => $request->judulSkripsi,
                'sesi' => $request->sesiBimbingan,
                'tanggal' => $request->tanggalBimbingan,
                'jenis' => $request->jenisBimbingan,
                'catatan' => $request->catatanBimbingan
            ]);

            $bimbinganSkripsiTotal = BimbinganSkripsi::where('id_mahasiswa', $request->mahasiswaId)
                                                ->where('id_pembimbing', $request->dosenPembimbing)
                                                ->where('sesi', $request->sesiBimbingan)
                                                ->count();

            if ($bimbinganSkripsiTotal > 1) {
                // return redirect()->route('daftar_seminar_proposal')->with('error', 'Anda masih memiliki pendaftaran yang sedang diproses pada periode yang sama. Silahkan tunggu konfirmasi dari Kaprodi.');
                DB::rollBack();

                return redirect()->route('monitoring.bimbingan.skripsi')->with('error', 'Anda sudah memiliki bimbingan pada dosen dan sesi yang sama.');
            }

            DB::commit();

            return redirect()->route('monitoring.bimbingan.skripsi')->with('message', 'Berhasil diinput.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('monitoring.bimbingan.skripsi')->with('error', 'Gagal diinput.');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $nilaiSempro = BimbinganSkripsi::findOrFail($id);

            if ($request->filled('judulSkripsi') && $request->judulSkripsi != $nilaiSempro->judul_skripsi) {
                $nilaiSempro->judul_skripsi = $request->judulSkripsi;
            }

            if ($request->filled('dosenPembimbing') && $request->dosenPembimbing != $nilaiSempro->id_pembimbing) {
                $nilaiSempro->id_pembimbing = $request->dosenPembimbing;
            }

            if ($request->filled('catatanBimbingan') && $request->catatanBimbingan != $nilaiSempro->catatan) {
                $nilaiSempro->catatan = $request->catatanBimbingan;
            }

            if ($request->filled('tanggalBimbingan') && $request->tanggalBimbingan != $nilaiSempro->tanggal) {
                $nilaiSempro->tanggal = $request->tanggalBimbingan;
            }

            if ($request->filled('sesiBimbingan') && $request->sesiBimbingan != $nilaiSempro->sesi) {
                $nilaiSempro->sesi = $request->sesiBimbingan;
            }

            if ($request->filled('jenisBimbingan') && $request->jenisBimbingan != $nilaiSempro->jenis) {
                $nilaiSempro->jenis = $request->jenisBimbingan;
            }

            $nilaiSempro->save();
            DB::commit();

            return redirect()->route('monitoring.bimbingan.skripsi')->with('message', 'Detail bimbingan skripsi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('monitoring.bimbingan.skripsi')->with('error', 'Gagal diperbarui.');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $bimbinganSkripsi = BimbinganSkripsi::findOrFail($id);
            $bimbinganSkripsi->delete();
            DB::commit();

            return redirect()->route('monitoring.bimbingan.skripsi')->with('message', 'Bimbingan skripsi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('monitoring.bimbingan.skripsi')->with('error', 'Gagal menghapus bimbingan skripsi.');
        }
    }
}
