<?php

namespace App\Http\Controllers;

use App\Models\NilaiSemhas;
use App\Models\PendaftaranSemhas;
use App\Models\PendaftaranSkripsi;
use App\Models\User;
use App\Models\UsersPivot;

use App\Notifications\DaftarSkripsiAcceptNotification;
use App\Notifications\DaftarSkripsiRejectNotification;
use App\Notifications\DaftarSkripsiReviseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DaftarSkripsiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $data = collect();
        $semhasCount = 0;
        $namaDosen = collect();
        $hasRevise = '';
        $reviseCount = 0;

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $daftarSkripsi = PendaftaranSkripsi::whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $daftarSkripsi->each(function ($item) {
                    $item->role = 'admin';
                });

                // Calculate revisi count
                $reviseCount += $data->filter(function ($row) {
                    return $row->status === 'Revisi Diajukan';
                })->count();
                $hasRevise = $reviseCount > 0 ? "Revisi Diajukan" : null;

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $pivot->fakultas->programStudi;
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $daftarSkripsi = PendaftaranSkripsi::whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $daftarSkripsi->each(function ($item) {
                    $item->role = 'admin';
                });

                // Calculate revisi count
                $reviseCount += $data->filter(function ($row) {
                    return $row->status === 'Revisi Diajukan';
                })->count();
                $hasRevise = $reviseCount > 0 ? "Revisi Diajukan" : null;

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $daftarSkripsi = PendaftaranSkripsi::where('id_dosen_pembimbing_akademik', $user->id)
                    ->orWhere('id_dosen_pembimbing_1', $user->id)
                    ->orWhere('id_dosen_pembimbing_2', $user->id)
                    ->orWhere('id_calon_penguji_1', $user->id)
                    ->orWhere('id_calon_penguji_2', $user->id)
                    ->get()->reject(function ($item) {
                        return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                    });
                $daftarSkripsi->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Calculate revisi count
                $reviseCount += $data->filter(function ($row) {
                    return $row->status === 'Revisi';
                })->count();
                $hasRevise = $reviseCount > 0 ? "Revisi" : null;

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $daftarSkripsi = PendaftaranSkripsi::where('id_mahasiswa', $user->id)->get();
                $daftarSkripsi->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                $semhasCount = NilaiSemhas::where('id_mahasiswa', $user->id)
                    ->where('nilai_pembimbing_1', '!=', null)
                    ->where('nilai_pembimbing_2', '!=', null)
                    ->where('nilai_penguji_1', '!=', null)
                    ->where('nilai_penguji_2', '!=', null)
                    ->count();

                // Calculate revisi count
                $reviseCount += $data->filter(function ($row) {
                    return $row->status === 'Revisi';
                })->count();
                $hasRevise = $reviseCount > 0 ? "Revisi" : null;

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }

            // Merge the fetched daftarSemhas with the existing data collection
            $data = $data->merge($daftarSkripsi);
            $namaDosen = $namaDosen->merge($dosen);
        }

        // Sort data by 'role' to prioritize higher role and make it unique by 'id'
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');
        $namaDosen = $namaDosen->sortBy('name')->unique('id');


        return view('pages/skripsi/daftar_sidang_skripsi',  compact('user', 'userRole', 'userPivot', 'data', 'semhasCount', 'hasRevise', 'reviseCount'));
    }

    public function show($id)
    {
        $daftarSkripsi = PendaftaranSkripsi::where('id', $id)
            ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
            ->first();

        return response()->json([
            'pendaftaranSkripsi' => $daftarSkripsi,
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();
        $namaDosenAkademik = collect();


        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->with(['programStudi'  => function ($q) {
                    $q->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();
            }

            $namaDosenAkademik = $namaDosenAkademik->merge($dosenAkademik);
            $namaDosen = $namaDosen->merge($dosen);
        }

        // Pengaturan nama dosen akademik
        $namaDosenAkademik = $namaDosenAkademik->unique('id')->map(function ($item) {
            $item->display_name = $item->name;
            return $item;
        })->sortBy([
            ['name', 'asc']
        ]);

        // $namaDosen = $namaDosen->sortBy('name')->unique('id');
        $namaDosen = $namaDosen->unique('id')->map(function ($item,) {
            $prodi = $item->programStudi->first()->nama ?? 'No Prodi';
            $item->nama_prodi_sort = $prodi;
            $item->display_name = $item->name . " - " . $prodi;
            return $item;
        })->sortBy([
            ['nama_prodi_sort', 'asc'],
            ['name', 'asc']
        ]);

        return view('pages/skripsi/daftar_sidang_skripsi_add', compact('user', 'userPivot', 'namaDosen', 'namaDosenAkademik'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $semhasCount = NilaiSemhas::where('id_mahasiswa', $userId)
            ->where('nilai_pembimbing_1', '!=', null)
            ->where('nilai_pembimbing_2', '!=', null)
            ->where('nilai_penguji_1', '!=', null)
            ->where('nilai_penguji_2', '!=', null)
            ->count();

        if ($semhasCount < 1) {
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Anda belum memiliki nilai seminar hasil.');
        }

        $pendaftaranSemhas = PendaftaranSemhas::where('id_mahasiswa', $userId)->orderBy('created_at', 'desc')->first();
        if (!$pendaftaranSemhas) {
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Silahkan melakukan pendaftaran sempro terlebih dahulu');
        }

        $rules = [
            'judulSkripsi' => 'required|string|max:191',
            'waktuUjian' => 'required|date',
            'dosenPembimbingAkademik' => 'required|exists:users,id',
            'pembimbing1' => 'required|exists:users,id',
            'pembimbing2' => 'required|exists:users,id',
            'fileTranskripNilai' => 'required|file|mimes:pdf|max:15360',
            'filePersetujuanPengujiSemhas' => 'required|file|mimes:pdf|max:15360',
            'fileNaskahSkripsi' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'judulSkripsi.required' => 'Judul Skripsi wajib diisi',
            'judulSkripsi.string' => 'Judul Skripsi harus berupa string',
            'judulSkripsi.max' => 'Judul Skripsi maksimal 191 karakter',

            'waktuUjian.required' => 'Waktu Ujian wajib diisi',
            'waktuUjian.date' => 'Waktu Ujian harus dalam format tanggal',

            'dosenPembimbingAkademik.required' => 'Dosen Pembimbing Akademik wajib diisi',
            'dosenPembimbingAkademik.exists' => 'Dosen Pembimbing Akademik tidak valid',
            'pembimbing1.required' => 'Pembimbing 1 wajib diisi',
            'pembimbing1.exists' => 'Pembimbing 1 tidak valid',
            'pembimbing2.required' => 'Pembimbing 2 wajib diisi',
            'pembimbing2.exists' => 'Pembimbing 2 tidak valid',

            'fileTranskripNilai.required' => 'File Transkrip wajib diisi',
            'fileTranskripNilai.file' => 'File Transkrip yang dipilih tidak valid',
            'fileTranskripNilai.mimes' => 'File Transkrip harus dalam format PDF',
            'fileTranskripNilai.max' => 'Ukuran File Transkrip tidak boleh melebihi 15MB',

            'filePersetujuanPengujiSemhas.required' => 'File Pernyataan Karya Sendiri wajib diisi',
            'filePersetujuanPengujiSemhas.file' => 'File Pernyataan Karya Sendiri yang dipilih tidak valid',
            'filePersetujuanPengujiSemhas.mimes' => 'File Pernyataan Karya Sendiri harus dalam format PDF',
            'filePersetujuanPengujiSemhas.max' => 'Ukuran File Pernyataan Karya Sendiri tidak boleh melebihi 15MB',

            'fileNaskahSkripsi.required' => 'File Naskah Skripsi wajib diisi',
            'fileNaskahSkripsi.file' => 'File Naskah Skripsi yang dipilih tidak valid',
            'fileNaskahSkripsi.mimes' => 'File Naskah Skripsi harus dalam format PDF',
            'fileNaskahSkripsi.max' => 'Ukuran File Naskah Skripsi tidak boleh melebihi 15MB',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        $fileDaftarSkripsi = [];
        $fileNameArray = [];

        DB::beginTransaction();

        try {
            $files = [
                'fileTranskripNilai',
                'filePersetujuanPengujiSemhas',
                'fileNaskahSkripsi'
            ];

            foreach ($files as $file) {
                $fileName = time() . '_' . random_int(1, 300) . '_' . $request->file($file)->getClientOriginalName();
                $filePath = 'public/files/daftar_skripsi/';
                $fileDaftarSkripsi[$file] = $fileName;
                Storage::putFileAs($filePath, $request->file($file), $fileName);
                array_push($fileNameArray, $fileName);
            }

            PendaftaranSkripsi::create([
                'id_mahasiswa' => $userId,
                'judul_skripsi' => $request->judulSkripsi,
                'waktu_ujian' => $request->waktuUjian,
                'id_dosen_pembimbing_akademik' => $request->dosenPembimbingAkademik,
                'id_dosen_pembimbing_1' => $request->pembimbing1,
                'id_dosen_pembimbing_2' => $request->pembimbing2,
                'id_calon_penguji_1' => $request->calonPenguji1,
                'id_calon_penguji_2' => $request->calonPenguji2,
                'calon_penguji_3_name' => $request->calonPenguji3,
                'file_transkrip_nilai' => $fileDaftarSkripsi['fileTranskripNilai'],
                'file_persetujuan_penguji_semhas' => $fileDaftarSkripsi['filePersetujuanPengujiSemhas'],
                'file_naskah_skripsi' => $fileDaftarSkripsi['fileNaskahSkripsi'],
                'id_kategori_ta' => isset($pendaftaranSemhas) ? $pendaftaranSemhas->id_kategori_ta : null,
            ]);

            $pendaftaranDiproses = PendaftaranSkripsi::where('id_mahasiswa', $userId)
                ->where('judul_skripsi', $request->judulSkripsi)
                ->where('waktu_ujian', $request->waktuUjian)
                ->where('id_dosen_pembimbing_akademik', $request->dosenPembimbingAkademik)
                ->where('id_dosen_pembimbing_1', $request->pembimbing1)
                ->where('id_dosen_pembimbing_2', $request->pembimbing2)
                ->where('id_calon_penguji_1', $request->calonPenguji1)
                ->where('id_calon_penguji_2', $request->calonPenguji2)
                ->where('calon_penguji_3_name', $request->calonPenguji3)
                ->count();

            if ($pendaftaranDiproses > 1) {
                DB::rollback();
                return redirect()->route('daftar.sidang.skripsi')->with('error', 'Gagal mendaftar.');
            }

            DB::commit();

            return redirect()->route('daftar.sidang.skripsi')->with('message', 'Berhasil mendaftar.');
        } catch (\Exception $e) {
            DB::rollback();

            foreach ($fileNameArray as $fileName) {
                if (Storage::exists("public/files/daftar_skripsi/$fileName")) {
                    Storage::disk('local')->delete("public/files/daftar_skripsi/$fileName");
                }
            }

            Log::error($e->getMessage());
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Gagal mendaftar. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $daftarSkripsi = PendaftaranSkripsi::where('id', $id)
            ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
            ->first();

        $namaDosen = collect();
        $namaDosenAkademik = collect();


        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->with(['programStudi'  => function ($q) {
                    $q->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $namaDosenAkademik = $namaDosenAkademik->merge($dosenAkademik);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $namaDosenAkademik = $namaDosenAkademik->merge($dosenAkademik);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                // CONTOH PERBAIKAN PADA SEMUA BLOK ROLE
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('roles.nama', 'dosen'); // Gunakan roles.nama
                })
                    ->whereHas('programStudi', function ($query) use ($programStudiId) {
                        // INI BAGIAN KRUSIAL: Tambahkan 'program_studi.' sebelum 'id'
                        $query->where('program_studi.id', $programStudiId);
                    })
                    ->select('users.id', 'users.name') // Tambahkan prefix users.id
                    ->without(['pivot', 'roles'])
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $namaDosenAkademik = $namaDosenAkademik->merge($dosenAkademik);
            }
        }

        // Pengaturan nama dosen akademik
        $namaDosenAkademik = $namaDosenAkademik->unique('id')->map(function ($item) {
            $item->display_name = $item->name;
            return $item;
        })->sortBy([
            ['name', 'asc']
        ]);

        // $namaDosen = $namaDosen->sortBy('name')->unique('id');
        $namaDosen = $namaDosen->unique('id')->map(function ($item,) {
            $prodi = $item->programStudi->first()->nama ?? 'No Prodi';
            $item->nama_prodi_sort = $prodi;
            $item->display_name = $item->name . " - " . $prodi;
            return $item;
        })->sortBy([
            ['nama_prodi_sort', 'asc'],
            ['name', 'asc']
        ]);

        return view('pages/skripsi/daftar_sidang_skripsi_edit', compact('user', 'userPivot', 'daftarSkripsi', 'namaDosen', 'namaDosenAkademik'));
    }

    public function update(Request $request, $id)
    {
        // return response()->json($request->all());
        $daftarSkripsi = PendaftaranSkripsi::find($id);
        if (!$daftarSkripsi) {
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Data tidak ditemukan.');
        }

        if ($daftarSkripsi->status !== 'Revisi') {
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Data tidak dapat diubah karena status bukan "Revisi".');
        }

        // Mendeclare nama file yang akan diupload
        $files = [
            'fileTranskripNilai' => 'file_transkrip_nilai',
            'filePersetujuanPengujiSemhas' => 'file_persetujuan_penguji_semhas',
            'fileNaskahSkripsi' => 'file_naskah_skripsi',
        ];

        $fileDaftarSkripsi = [];
        $fileNameArray = [];

        DB::beginTransaction();

        try {
            // Update data pendaftaran skripsi sebagian
            $daftarSkripsi->update([
                'judul_skripsi' => $request->judulSkripsi ?? $daftarSkripsi->judul_skripsi,
                'waktu_ujian' => $request->waktuUjian ?? $daftarSkripsi->waktu_ujian,
                'id_dosen_pembimbing_akademik' => $request->dosenPembimbingAkademik ?? $daftarSkripsi->id_dosen_pembimbing_akademik,
                'id_dosen_pembimbing_1' => $request->pembimbing1 ?? $daftarSkripsi->id_dosen_pembimbing_1,
                'id_dosen_pembimbing_2' => $request->pembimbing2 ?? $daftarSkripsi->id_dosen_pembimbing_2,
                'id_calon_penguji_1' => $request->calonPenguji1 ?? $daftarSkripsi->id_calon_penguji_1,
                'id_calon_penguji_2' => $request->calonPenguji2 ?? $daftarSkripsi->id_calon_penguji_2,
                'calon_penguji_3_name' => $request->calonPenguji3 ?? $daftarSkripsi->calon_penguji_3_name,
            ]);

            // Handle tiap file
            foreach ($files as $inputName => $dbField) {
                if ($request->hasFile($inputName)) {
                    // Menghapus file yang lama
                    if (Storage::exists("public/files/daftar_skripsi/{$daftarSkripsi->$dbField}")) {
                        Storage::delete("public/files/daftar_skripsi/{$daftarSkripsi->$dbField}");
                    }

                    // Simpan file baru
                    $uploadedFile = $request->file($inputName);
                    $fileName = time() . '_' . random_int(1, 300) . '_' . $uploadedFile->getClientOriginalName();
                    $filePath = 'public/files/daftar_skripsi/';
                    $fileDaftarSkripsi[$dbField] = $fileName;
                    Storage::putFileAs($filePath, $uploadedFile, $fileName);
                    array_push($fileNameArray, $fileName);

                    // Update database dengan nama file yang baru
                    $daftarSkripsi->update([
                        $dbField => $fileName
                    ]);
                }
            }

            if (Auth::user()->role == 'mahasiswa') {
                $daftarSkripsi->update([
                    'status' => 'Revisi Diajukan',
                ]);
            }

            DB::commit();

            return redirect()->route('daftar.sidang.skripsi')->with('message', 'Data berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            // Menghapus file yang telah diupload tadi
            foreach ($fileNameArray as $fileName) {
                Storage::delete('public/files/daftar_skripsi/' . $fileName);
            }

            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Data gagal diubah.');
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranSkripsi = PendaftaranSkripsi::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

            $pendaftaranSkripsi->status = 'Diterima';
            $pendaftaranSkripsi->alasan = '';
            $pendaftaranSkripsi->save();

            DB::commit();

            Mail::to($pendaftaranSkripsi->mahasiswa->email)
                ->send(new DaftarSkripsiAcceptNotification($pendaftaranSkripsi));

            return redirect()->route('daftar.sidang.skripsi')->with('message', 'Status data diterima.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Status data gagal diubah menjadi diterima.');
        }
    }

    public function reject(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranSkripsi = PendaftaranSkripsi::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

            $pendaftaranSkripsi->status = 'Ditolak';
            $pendaftaranSkripsi->alasan = $request->alasan ?? 'Tidak ada alasan';
            $pendaftaranSkripsi->save();

            DB::commit();

            Mail::to($pendaftaranSkripsi->mahasiswa->email)
                ->send(new DaftarSkripsiRejectNotification($pendaftaranSkripsi));

            return redirect()->route('daftar.sidang.skripsi')->with('message', 'Status data ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Status data gagal diubah menjadi ditolak.');
        }
    }

    public function revise(Request $request, $id)
    {
        $pendaftaranSkripsi = PendaftaranSkripsi::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

        DB::beginTransaction();

        try {
            $pendaftaranSkripsi->status = 'Revisi';
            $pendaftaranSkripsi->alasan = $request->alasan ?? 'Tidak ada alasan';
            $pendaftaranSkripsi->save();

            DB::commit();

            Mail::to($pendaftaranSkripsi->mahasiswa->email)
                ->send(new DaftarSkripsiReviseNotification($pendaftaranSkripsi));

            return redirect()->back()->with('message', 'Status data revisi');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->back()->with('error', 'Status data gagal diubah menjadi revisi.');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranSkripsi = PendaftaranSkripsi::findOrFail($id);

            $pendaftaranSkripsi->delete();

            DB::commit();

            $listFile = [
                $pendaftaranSkripsi->file_transkrip_nilai,
                $pendaftaranSkripsi->file_naskah_skripsi,
                $pendaftaranSkripsi->file_persetujuan_penguji_semhas,
            ];
            foreach ($listFile as $file) {
                if (Storage::exists("public/files/daftar_skripsi/{$file}")) {
                    Storage::disk('local')->delete('public/files/daftar_skripsi/' . $file);
                }
            }

            return redirect()->route('daftar.sidang.skripsi')->with('message', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.sidang.skripsi')->with('error', 'Data gagal dihapus.');
        }
    }

    public function viewPdf($id)
    {
        $pathDaftarSkripsi = storage_path('app/public/files/daftar_skripsi/' . $id);

        if (file_exists($pathDaftarSkripsi)) {
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return response()->file($pathDaftarSkripsi, $headers);
        } else {
            abort(404);
        };
    }
}
