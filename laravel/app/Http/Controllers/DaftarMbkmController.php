<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PendaftaranMbkm;
use App\Models\User;
use App\Models\UsersPivot;

use App\Notifications\DaftarMbkmAcceptNotification;
use App\Notifications\DaftarMbkmRejectNotification;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class DaftarMbkmController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = collect(); // Main collection for NilaiSempro

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudi = $pivot->id_program_studi;
            $fakultas = $pivot->id_fakultas;

            $daftarMbkm = collect(); // Initialize inside the loop, but will merge into $data

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $daftarMbkm = PendaftaranMbkm::whereHas('mahasiswa.fakultas', function ($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas);
                })->get();
                $daftarMbkm->each(function ($item) {
                    $item->role = 'admin';
                });

                // Fetch dosen's name from the same program studi as the current user
                $namaDosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen');
                })->whereHas('fakultas', function ($query) use ($fakultas) {
                    $query->where('fakultas.id', $fakultas);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $pivot->fakultas->programStudi;
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $daftarMbkm = PendaftaranMbkm::whereHas('mahasiswa.programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->get();
                $daftarMbkm->each(function ($item) {
                    $item->role = 'admin';
                });

                // Fetch dosen's name from the same program studi as the current user
                $namaDosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen');
                })->whereHas('programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $daftarMbkm = PendaftaranMbkm::whereHas('mahasiswa.programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->where('id_dosen_pembimbing', $user->id)->get();
                $daftarMbkm->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Fetch dosen's name from the same program studi as the current user
                $namaDosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen');
                })->whereHas('programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $daftarMbkm = PendaftaranMbkm::where('id_mahasiswa', $user->id)->get();
                $daftarMbkm->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Fetch dosen's name from the same program studi as the current user
                $namaDosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen');
                })->whereHas('programStudi', function ($query) use ($programStudi) {
                    $query->where('program_studi.id', $programStudi);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }

            // Merge current $daftarMbkm into $data
            $data = $data->merge($daftarMbkm);
        }

        // Sort data by 'created_at' and make it unique by 'id'
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');

        return view('pages/mbkm/daftar_mbkm',  compact('user', 'userRole', 'userPivot', 'data', 'namaDosen'));
    }

    public function show($id)
    {
        $pendaftaranMbkm = PendaftaranMbkm::where('id', $id)
            ->with('mahasiswa', 'pembimbing')
            ->first();

        return response()->json([
            "pendaftaranMbkm" => $pendaftaranMbkm,
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();

        foreach ($user->pivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                // Fetch dosen's name from the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $pivot->fakultas->programStudi;
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);
            }
        }

        return view('pages/mbkm/daftar_mbkm_add', compact('user', 'userRole', 'userPivot', 'namaDosen'));
    }

    public function store(Request $request)
    {
        $rules = [
            'jenisMbkm' => 'required|string|max:191',
            'dosenPembimbing' => 'required|exists:users,id',
            'mitra' => 'required|string|max:191',
            'learningPath' => 'nullable|string|max:191',
            'jumlahSks' => 'required|numeric|min:1|max:30',
            'mkKonversi' => 'required|string|max:191',
            'fileKomitmen' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'jenisMbkm.required' => 'Jenis MBKM wajib diisi',
            'jenisMbkm.string' => 'Jenis MBKM harus berupa string',
            'jenisMbkm.max' => 'Jenis MBKM tidak boleh melebihi 191 karakter',

            'dosenPembimbing.required' => 'Dosen Pembimbing wajib diisi',
            'dosenPembimbing.exists' => 'Dosen Pembimbing tidak valid',

            'mitra.required' => 'Mitra wajib diisi',
            'mitra.string' => 'Mitra harus berupa string',
            'mitra.max' => 'Mitra tidak boleh melebihi 191 karakter',

            'learningPath.string' => 'Learning Path harus berupa string',
            'learningPath.max' => 'Learning Path tidak boleh melebihi 191 karakter',

            'jumlahSks.required' => 'Total SKS harus diisi',
            'jumlahSks.numeric' => 'Total SKS harus berupa angka',
            'jumlahSks.min' => 'Total SKS tidak boleh kurang dari 1',
            'jumlahSks.max' => 'Total SKS tidak boleh lebih dari 30',

            'mkKonversi.required' => 'Mata Kuliah Konversi harus diisi',
            'mkKonversi.string' => 'Mata Kuliah Konversi harus berupa string',
            'mkKonversi.max' => 'Mata Kuliah Konversi tidak boleh melebihi 191 karakter',

            'fileKomitmen.required' => 'File Surat Pernyataan Kesanggupan/Komitmen harus diisi',
            'fileKomitmen.file' => 'File Surat Pernyataan Kesanggupan/Komitmen yang dipilih tidak valid',
            'fileKomitmen.mimes' => 'File Surat Pernyataan Kesanggupan/Komitmen harus dalam format PDF',
            'fileKomitmen.max' => 'Ukuran File Surat Pernyataan Kesanggupan/Komitmen tidak boleh melebihi 15MB',
            'fileKomitmen.uploaded' => 'Ukuran File Surat Pernyataan Kesanggupan/Komitmen tidak boleh melebihi 15MB',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        // if ($request->jenisMbkm === 'bangkit' && $request->learningPath == null) {
        //     return back()->
        // }

        $userId = Auth::user()->id;

        $fileKomitmen = $request->file('fileKomitmen');
        $komitmenName = time() . '_' . $fileKomitmen->getClientOriginalName();
        $komitmenPath = 'public/files/daftar_mbkm/';

        DB::beginTransaction();

        try {
            PendaftaranMbkm::create([
                'id_mahasiswa' => $userId,
                'id_dosen_pembimbing' => $request->dosenPembimbing,
                'jenis_mbkm' => $request->jenisMbkm,
                'mitra' => $request->mitra,
                'learning_path' => $request->learningPath,
                'jumlah_sks' => $request->jumlahSks,
                'mk_konversi' => $request->mkKonversi,
                'file_pernyataan_komitmen' => $komitmenName,
            ]);

            DB::commit();

            if ($fileKomitmen) {
                Storage::putFileAs($komitmenPath, $fileKomitmen, $komitmenName);
            }

            return redirect()->route('daftar.mbkm')->with('message', 'Berhasil mendaftar.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.mbkm')->with('error', 'Gagal diinput.');
        }
    }

    public function approve(Request $request, $id)
    {
        $validatedData = [
            'fileRekomendasi' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'fileRekomendasi.required' => 'File perlu diisi.',
            'fileRekomendasi.file' => 'File yang dipilih tidak valid.',
            'fileRekomendasi.max' => 'Ukuran file tidak boleh melebihi 15MB.',
            'fileRekomendasi.mimes' => 'File harus berupa pdf.',
        ];
        $validator = Validator::make($request->all(), $validatedData, $customMessages);
        $validator->validate();

        DB::beginTransaction();

        try {
            $pendaftaranMbkm = PendaftaranMbkm::findOrFail($id);

            $pendaftaranMbkm->status = 'Diterima';
            $pendaftaranMbkm->alasan = '';

            $fileRekomendasi = $request->file('fileRekomendasi');
            $rekomendasiName = time() . '_' . $fileRekomendasi->getClientOriginalName();
            $rekomendasiPath = 'public/files/daftar_mbkm/';
            $pendaftaranMbkm->file_surat_rekomendasi = $rekomendasiName;

            $pendaftaranMbkm->save();

            DB::commit();

            if ($fileRekomendasi) {
                Storage::putFileAs($rekomendasiPath, $fileRekomendasi, $rekomendasiName);
            }

            // Mail::to($pendaftaranMbkm->mahasiswa->email)
            //     ->send(new DaftarMbkmAcceptNotification($pendaftaranMbkm));

            return redirect()->route('daftar.mbkm')->with('message', 'Status data diterima.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.mbkm')->with('error', 'Status data gagal diubah menjadi diterima.');
        }
    }

    public function reject(Request $request, $id)
    {
        $pendaftaranMbkm = PendaftaranMbkm::findOrFail($id);

        DB::beginTransaction();

        try {
            $pendaftaranMbkm->status = 'Ditolak';
            $pendaftaranMbkm->alasan = $request->alasan ?? 'Tidak ada alasan';
            $pendaftaranMbkm->save();

            DB::commit();

            // Mail::to($pendaftaranMbkm->mahasiswa->email)
            //     ->send(new DaftarMbkmRejectNotification($pendaftaranMbkm));

            return redirect()->route('daftar.mbkm')->with('message', 'Status data ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.mbkm')->with('error', 'Status data gagal diubah menjadi ditolak.');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranMbkm = PendaftaranMbkm::findOrFail($id);

            $fileKomitmen = $pendaftaranMbkm->file_pernyataan_komitmen;
            $fileRekomendasi = $pendaftaranMbkm->file_surat_rekomendasi;

            $pendaftaranMbkm->delete();

            DB::commit();

            if (Storage::exists("public/files/daftar_mbkm/{$fileKomitmen}")) {
                Storage::disk('local')->delete('public/files/daftar_mbkm/' . $fileKomitmen);
            }

            if (Storage::exists("public/files/daftar_mbkm/{$fileRekomendasi}")) {
                Storage::disk('local')->delete('public/files/daftar_mbkm/' . $fileRekomendasi);
            }

            return redirect()->route('daftar.mbkm')->with('message', 'Data berhasil dihapus.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('daftar.mbkm')->with('error', 'Data gagal dihapus.');
        }
    }

    public function viewPdf($id)
    {
        $pathDaftarMbkm = storage_path('app/public/files/daftar_mbkm/' . $id);

        if (file_exists($pathDaftarMbkm)) {
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return response()->file($pathDaftarMbkm, $headers);
        } else {
            abort(404); // File not found
        };
    }

    public function viewRekomendasi($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $pendaftaranMbkm = PendaftaranMbkm::findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pendaftaranMbkm->file_surat_rekomendasi . '"',
        ];

        if (Storage::exists("public/files/daftar_mbkm/{$pendaftaranMbkm->file_surat_rekomendasi}")) {
            $filePath = storage_path("app/public/files/daftar_mbkm/{$pendaftaranMbkm->file_surat_rekomendasi}");

            return response()->file($filePath, $headers);
        } else {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }
    }
}
