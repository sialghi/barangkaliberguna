<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;

use App\Models\User;
use App\Models\NilaiSemhas;
use App\Models\PendaftaranSemhas;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use App\Notifications\NilaiSemhasNotification;
use App\Notifications\JadwalSemhasNotification;

class NilaiSemhasController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $data = collect();
        $namaDosen = collect();

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $nilaiSemhas->each(function ($item) {
                    $item->role = 'admin';
                });

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
                $nilaiSemhas = NilaiSemhas::whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $nilaiSemhas->each(function ($item) {
                    $item->role = 'admin';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $nilaiSemhas = NilaiSemhas::where(function ($query) use ($userId) {
                    $query->Where("id_pembimbing_1", $userId)
                        ->orWhere("id_pembimbing_2", $userId)
                        ->orWhere("id_penguji_1", $userId)
                        ->orWhere("id_penguji_2", $userId);
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $nilaiSemhas->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $nilaiSemhas = NilaiSemhas::where('id_mahasiswa', $userId)->get();
                $nilaiSemhas->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }

            // Merge the fetched daftarSemhas with the existing data collection
            $data = $data->merge($nilaiSemhas);
            $namaDosen = $namaDosen->merge($dosen);
        }

        // Sort data by 'role' to prioritize higher role and make it unique by 'id'
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');
        $namaDosen = $namaDosen->sortBy('name')->unique('id');

        return view('pages/semhas/nilai_seminar_hasil', compact('data', 'user', 'userRole', 'namaDosen', 'userPivot'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $nilaiSemhas = NilaiSemhas::where('id', $id)
            ->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')
            ->first();


        $userRole = $user->roles->pluck('nama')->toArray();

        // $namaDosen = User::whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])->pluck('name', 'id');

        return response()->json([
            'penilaianSemhas' => $nilaiSemhas,
            'userRole' => $userRole
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();

        foreach ($userPivot as $pivot) {
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

                $nilaiSemhas = NilaiSemhas::with('pendaftaranSemhas')->whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->pluck('id_pendaftaran_semhas')->toArray();
                $pendaftarSemhasAll = PendaftaranSemhas::whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->where('status', 'Diterima')
                    ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
                    ->get();

                $pendaftarSemhas = $pendaftarSemhasAll->reject(function ($item) use ($nilaiSemhas) {
                    return in_array($item->id, $nilaiSemhas);
                });
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                // Fetch dosen's name from the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $nilaiSemhas = NilaiSemhas::with('pendaftaranSemhas')->whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->pluck('id_pendaftaran_semhas')->toArray();
                $pendaftarSemhasAll = PendaftaranSemhas::whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->where('status', 'Diterima')
                    ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
                    ->get();

                $pendaftarSemhas = $pendaftarSemhasAll->reject(function ($item) use ($nilaiSemhas) {
                    return in_array($item->id, $nilaiSemhas);
                });
            }
            // If user has the role of Dosen
            // else if (in_array($role, ['dosen'])) {
            //     // Fetch dosen's name from the same program studi as the current user
            //     $dosen = User::whereHas('roles', function($query) {
            //         $query->where('nama', 'dosen'); // Checking for 'dosen' role
            //     })->whereHas('programStudi', function($query) use ($programStudiId) {
            //         $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
            //     })->select('id', 'name')->without(['pivot', 'roles'])->get();

            //     $namaDosen = $namaDosen->merge($dosen);

            //     $nilaiSemhasData = NilaiSemhas::where(function ($query) use ($user) {
            //         $query->Where("id_pembimbing_1", $user->id)
            //             ->orWhere("id_pembimbing_2", $user->id)
            //             ->orWhere("id_penguji_1", $user->id)
            //             ->orWhere("id_penguji_2", $user->id);
            //         })->with('pendaftaranSemhas')->select('id_pendaftaran_semhas')->get();

            //     $nilaiSemhas = $nilaiSemhas->merge($nilaiSemhasData);
            // }
        }

        $namaDosen = $namaDosen->sortBy('name')->unique('id');

        return view('pages/semhas/nilai_seminar_hasil_add', compact('user', 'userRole', 'userPivot', 'namaDosen', 'pendaftarSemhas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'judulSkripsi' => ['required', 'string', 'max:191'],
            'tanggalSeminar' => ['required', 'date'],
            'pembimbing1' => ['required', 'string', 'max:191'],
            'nilaiPembimbing1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pembimbing2' => ['required', 'string', 'max:191'],
            'nilaiPembimbing2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'penguji1' => ['required', 'string', 'max:191'],
            'nilaiPenguji1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'penguji2' => ['required', 'string', 'max:191'],
            'nilaiPenguji2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'jamSeminar' => ['nullable', 'string', 'max:191', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'ruangSeminar' => ['nullable', 'string', 'max:191'],
            'linkSeminar' => ['nullable', 'string', 'max:191'],
            'tipeUjian' => ['required', 'string', 'max:191'],
        ];

        $customMessages = [
            'judulSkripsi.required' => 'Judul skripsi wajib diisi.',
            'judulSkripsi.string' => 'Judul skripsi harus berupa string',
            'judulSkripsi.max' => 'Judul skripsi maksimal 191 karakter',

            'tanggalSeminar.required' => 'Tanggal seminar wajib diisi.',
            'tanggalSeminar.date' => 'Tanggal seminar harus berupa tanggal',

            'pembimbing1.required' => 'Pembimbing 1 wajib diisi.',
            'pembimbing1.string' => 'Pembimbing 1 harus berupa string',
            'pembimbing1.max' => 'Pembimbing 1 maksimal 191 karakter',

            'nilaiPembimbing1.required' => 'Nilai pembimbing 1 wajib diisi.',
            'nilaiPembimbing1.numeric' => 'Nilai pembimbing 1 harus berupa angka',
            'nilaiPembimbing1.min' => 'Minimal nilai adalah 0',
            'nilaiPembimbing1.max' => 'Maksimal nilai adalah 100',

            'pembimbing2.required' => 'Pembimbing 2 wajib diisi.',
            'pembimbing2.string' => 'Pembimbing 2 harus berupa string',
            'pembimbing2.max' => 'Pembimbing 2 maksimal 191 karakter',

            'nilaiPembimbing2.required' => 'Nilai pembimbing 2 wajib diisi.',
            'nilaiPembimbing2.numeric' => 'Nilai pembimbing 2 harus berupa angka',
            'nilaiPembimbing2.min' => 'Minimal nilai adalah 0',
            'nilaiPembimbing2.max' => 'Maksimal nilai adalah 100',

            'penguji1.required' => 'Penguji 1 wajib diisi.',
            'penguji1.string' => 'Penguji 1 harus berupa string',
            'penguji1.max' => 'Penguji 1 maksimal 191 karakter',

            'nilaiPenguji1.required' => 'Nilai penguji 1 wajib diisi.',
            'nilaiPenguji1.numeric' => 'Nilai penguji 1 harus berupa angka',
            'nilaiPenguji1.min' => 'Minimal nilai adalah 0',
            'nilaiPenguji1.max' => 'Maksimal nilai adalah 100',

            'penguji2.required' => 'Penguji 2 wajib diisi.',
            'penguji2.string' => 'Penguji 2 harus berupa string',
            'penguji2.max' => 'Penguji 2 maksimal 191 karakter',

            'nilaiPenguji2.required' => 'Nilai penguji 2 wajib diisi.',
            'nilaiPenguji2.numeric' => 'Nilai penguji 2 harus berupa angka',
            'nilaiPenguji2.min' => 'Minimal nilai adalah 0',
            'nilaiPenguji2.max' => 'Maksimal nilai adalah 100',

            'jamSeminar.string' => 'Jam seminar harus berupa string',
            'jamSeminar.max' => 'Jam seminar maksimal 191 karakter',
            'jamSeminar.regex' => 'Format jam seminar harus HH:mm, contoh: 01:04, 18:59',

            'ruangSeminar.string' => 'Ruang seminar harus berupa string',
            'ruangSeminar.max' => 'Ruang seminar maksimal 191 karakter',

            'linkSeminar.string' => 'Link seminar harus berupa string',
            'linkSeminar.max' => 'Link seminar maksimal 191 karakter',

            'tipeUjian.required' => 'Tipe ujian wajib diisi',
            'tipeUjian.string' => 'Tipe ujian harus berupa string',
            'tipeUjian.max' => 'Tipe ujian maksimal 191 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        // Get the authenticated user's ID
        $user = Auth::user();

        // Retrieve the form input values
        // $pembimbing1 = User::where('id', $request->input('pembimbing1'))
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$pembimbing1) {
        //     return redirect()->back()->withErrors(['pembimbing1_not_found' => 'Pembimbing 1 tidak ditemukan']);
        // }

        // $pembimbing2 = User::where('id', $request->input('pembimbing2'))
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$pembimbing2) {
        //     return redirect()->back()->withErrors(['pembimbing2_not_found' => 'Pembimbing 2 tidak ditemukan']);
        // }

        // $penguji1 = User::where('id', $request->input('penguji1'))
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$penguji1) {
        //     return redirect()->back()->withErrors(['penguji1_not_found' => 'Penguji 1 tidak ditemukan']);
        // }

        // $penguji2 = User::where('id', $request->input('penguji2'))
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$penguji2) {
        //     return redirect()->back()->withErrors(['penguji2_not_found' => 'Penguji 2 tidak ditemukan']);
        // }

        DB::beginTransaction();

        try {
            // Proses penyimpanan data jika validasi berhasil
            $nilaiSemhas = NilaiSemhas::create([
                'id_mahasiswa' => $request->pendaftarSemhasId,
                'id_pembimbing_1' => $request->pembimbing1,
                'id_pembimbing_2' => $request->pembimbing2,
                'id_penguji_1' => $request->penguji1,
                'id_penguji_2' => $request->penguji2,

                'judul_skripsi' => $request->judulSkripsi,
                'nilai_pembimbing_1' => $request->nilaiPembimbing1,
                'nilai_pembimbing_2' => $request->nilaiPembimbing2,
                'nilai_penguji_1' => $request->nilaiPenguji1,
                'nilai_penguji_2' => $request->nilaiPenguji2,

                'tanggal_seminar' => $request->tanggalSeminar,
                'jam_seminar' => $request->jamSeminar,
                'ruangan_seminar' => $request->ruanganSeminar,
                'link_seminar' => $request->linkWebinar,
                'id_pendaftaran_semhas' => $request->pendaftarSemhasSelect,
            ]);

            DB::commit();
            // $dosen = User::whereHas('roles', function($query) {
            //     $query->where('nama', 'dosen'); // Checking for 'dosen' role
            // })->whereHas('fakultas', function($query) use ($fakultasId) {
            //     $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
            // })->select('id', 'name')->without(['pivot', 'roles'])->get();

            $userPivot = UsersPivot::where('id_user', $nilaiSemhas->id_mahasiswa)->first();
            $programStudiId = $userPivot->id_program_studi;

            $toKaprodiPivot = UsersPivot::where('id_program_studi', $programStudiId)
                ->where('id_role', 6)
                ->with('user')
                ->first();
            $kaprodiEmail = $toKaprodiPivot->user->email;

            // $nilaiSemhas = NilaiSemhas::where('id', $nilaiSemhas->id)
            //     ->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')
            //     ->first();

            $toSekprodiPivot = UsersPivot::where('id_program_studi', $programStudiId)
                ->where('id_role', 7)
                ->with('user')
                ->first();
            $sekprodiEmail = '';
            if ($toSekprodiPivot) {
                $sekprodiEmail = $toSekprodiPivot->user->email;
            }

            $ccEmails = [
                $nilaiSemhas->pembimbing1->email,
                $nilaiSemhas->pembimbing2->email,
                $nilaiSemhas->penguji1->email,
                $nilaiSemhas->penguji2->email
            ];

            if ($sekprodiEmail != null || $sekprodiEmail != '') {
                array_push($ccEmails, $sekprodiEmail);
            }
            Mail::to($kaprodiEmail)
                ->cc($ccEmails)
                ->send(new JadwalSemhasNotification($nilaiSemhas));

            return redirect()->route('nilai.seminar.hasil')->with('message', 'Berhasil Diinput.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.hasil')->with('error', 'Gagal Diinput.');
        }
    }

    public function simpanNilai(Request $request, $id)
    {

        // Cari nilai semhas berdasarkan ID
        $nilaiSemhas = NilaiSemhas::findOrFail($id);
        $user = Auth::user();


        $userRoles = $user->roles->pluck('nama')->toArray(); // Collection berisi nama-nama

        // 1. Definisikan peran yang memiliki hak akses penuh (admin-level)
        $adminRoles = [
            'dekan',
            'wadek_satu',
            'wadek_dua',
            'wadek_tiga',
            'admin_dekanat',
            'kaprodi',
            'sekprodi',
            'admin_prodi'
        ];

        // 2. Cek apakah ada irisan antara peran pengguna dan peran admin
        // Jika hasil irisan > 0, berarti pengguna adalah seorang admin.
        $isAdmin = count(array_intersect($userRoles, $adminRoles)) > 0;

        if (!$isAdmin) {
            // Jika BUKAN admin, cek apakah dia mencoba mengisi nilai yang bukan haknya.
            // Cek hanya untuk field yang dikirimkan oleh form.
            if ($request->has('nilai_pembimbing_1') && $user->id != $nilaiSemhas->id_pembimbing_1) {
                return redirect()->route('nilai.seminar.hasil')
                    ->with('error', 'AKSES DITOLAK: Anda tidak berhak mengisi nilai pembimbing 1.');
            }
            if ($request->has('nilai_pembimbing_2') && $user->id != $nilaiSemhas->id_pembimbing_2) {
                return redirect()->route('nilai.seminar.hasil')
                    ->with('error', 'AKSES DITOLAK: Anda tidak berhak mengisi nilai pembimbing 2.');
            }
            if ($request->has('nilai_penguji_1') && $user->id != $nilaiSemhas->id_penguji_1) {
                return redirect()->route('nilai.seminar.hasil')
                    ->with('error', 'AKSES DITOLAK: Anda tidak berhak mengisi nilai Penguji 1.');
            }
            if ($request->has('nilai_penguji_2') && $user->id != $nilaiSemhas->id_penguji_2) {
                return redirect()->route('nilai.seminar.hasil')
                    ->with('error', 'AKSES DITOLAK: Anda tidak berhak mengisi nilai Penguji 2.');
            }
        }
        DB::beginTransaction();
        try {
            $rules = [
                'nilai_pembimbing_1' => 'nullable|numeric|between:0,100',
                'nilai_pembimbing_2' => 'nullable|numeric|between:0,100',
                'nilai_penguji_1' => 'nullable|numeric|between:0,100',
                'nilai_penguji_2' => 'nullable|numeric|between:0,100',
            ];

            $messages = [
                'nilai_pembimbing_1.numeric' => 'Nilai harus berupa angka dan tidak boleh kosong',
                'nilai_pembimbing_2.numeric' => 'Nilai harus berupa angka dan tidak boleh kosong',
                'nilai_penguji_1.numeric' => 'Nilai harus berupa angka dan tidak boleh kosong',
                'nilai_penguji_2.numeric' => 'Nilai harus berupa angka dan tidak boleh kosong',

                'nilai_pembimbing_1.between' => 'Nilai harus berada diantara 0 dan 100',
                'nilai_pembimbing_2.between' => 'Nilai harus berada diantara 0 dan 100',
                'nilai_penguji_1.between' => 'Nilai harus berada diantara 0 dan 100',
                'nilai_penguji_2.between' => 'Nilai harus berada diantara 0 dan 100',
            ];

            // Validasi input
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return redirect()->route('nilai.seminar.hasil')->with('error', 'Nilai Seminar Hasil Gagal Disimpan')->withErrors($validator)->withInput();
            }



            // Update nilai semhas
            $nilaiSemhas->nilai_pembimbing_1 = $request->has('nilai_pembimbing_1') ? $request->input('nilai_pembimbing_1') : $nilaiSemhas->nilai_pembimbing_1;
            $nilaiSemhas->nilai_pembimbing_2 = $request->has('nilai_pembimbing_2') ? $request->input('nilai_pembimbing_2') : $nilaiSemhas->nilai_pembimbing_2;
            $nilaiSemhas->nilai_penguji_1 = $request->has('nilai_penguji_1') ? $request->input('nilai_penguji_1') : $nilaiSemhas->nilai_penguji_1;
            $nilaiSemhas->nilai_penguji_2 = $request->has('nilai_penguji_2') ? $request->input('nilai_penguji_2') : $nilaiSemhas->nilai_penguji_2;

            // simpan nilai semhas
            $nilaiSemhas->save();

            DB::commit();

            // Redirect ke halaman nilai semhas
            return redirect()->route('nilai.seminar.hasil')->with('message', 'Nilai Seminar Hasil Berhasil Disimpan');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.hasil')->with('error', 'Gagal menyimpan nilai seminar hasil');
        }
    }

    public function generateDocx($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $semhas = NilaiSemhas::findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        // Enable output escaping
        Settings::setOutputEscapingEnabled(true);

        // Load the .docx template
        $template = new TemplateProcessor(storage_path("app/public/files/nilai_semhas/template/Nilai_Seminar_Hasil_Template_Modified.docx"));

        $template->setValue('nama_mahasiswa', $semhas->mahasiswa->name);
        $template->setValue('nim', $semhas->nim);
        $template->setValue('judul', $semhas->judul_skripsi);

        // Pembimbing 1
        $pembimbing_1 = User::findOrFail($semhas->id_pembimbing_1);

        $template->setValue('pembimbing_1', $pembimbing_1->name);
        $template->setValue('nilai_pembimbing_1', $semhas->nilai_pembimbing_1);
        $template->setValue('jumlah_nilai_pembimbing_1', $semhas->nilai_pembimbing_1 * 5);
        $template->setValue('ratarata_nilai_pembimbing_1', $semhas->nilai_pembimbing_1);
        $template->setValue('nip_nidn_pembimbing_1', $pembimbing_1->nim_nip_nidn);

        $path_ttd_pembimbing_1 = $pembimbing_1->ttd;
        if ($pembimbing_1->ttd && Storage::exists($path_ttd_pembimbing_1)) {
            $ttd_pembimbing_1 = Storage::path($path_ttd_pembimbing_1);
            $template->setImageValue('ttd_pembimbing_1', array('path' => $ttd_pembimbing_1, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Pembimbing 2
        $pembimbing_2 = User::findOrFail($semhas->id_pembimbing_2);

        $template->setValue('pembimbing_2', $pembimbing_2->name);
        $template->setValue('nilai_pembimbing_2', $semhas->nilai_pembimbing_2);
        $template->setValue('jumlah_nilai_pembimbing_2', $semhas->nilai_pembimbing_2 * 5);
        $template->setValue('ratarata_nilai_pembimbing_2', $semhas->nilai_pembimbing_2);
        $template->setValue('nip_nidn_pembimbing_2', $pembimbing_2->nim_nip_nidn);

        $path_ttd_pembimbing_2 = $pembimbing_2->ttd;
        if ($pembimbing_2->ttd && Storage::exists($path_ttd_pembimbing_2)) {
            $ttd_pembimbing_2 = Storage::path($path_ttd_pembimbing_2);
            $template->setImageValue('ttd_pembimbing_2', array('path' => $ttd_pembimbing_2, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Penguji 1
        $penguji_1 = User::findOrFail($semhas->id_penguji_1);

        $template->setValue('penguji_1', $penguji_1->name);
        $template->setValue('nilai_penguji_1', $semhas->nilai_penguji_1);
        $template->setValue('jumlah_nilai_penguji_1', $semhas->nilai_penguji_1 * 5);
        $template->setValue('ratarata_nilai_penguji_1', $semhas->nilai_penguji_1);
        $template->setValue('nip_nidn_penguji_1', $penguji_1->nim_nip_nidn);

        $path_ttd_penguji_1 = $penguji_1->ttd;
        if ($penguji_1->ttd && Storage::exists($path_ttd_penguji_1)) {
            $ttd_penguji_1 = Storage::path($path_ttd_penguji_1);
            $template->setImageValue('ttd_penguji_1', array('path' => $ttd_penguji_1, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Penguji 2
        $penguji_2 = User::findOrFail($semhas->id_penguji_2);

        $template->setValue('penguji_2', $penguji_2->name);
        $template->setValue('nilai_penguji_2', $semhas->nilai_penguji_2);
        $template->setValue('jumlah_nilai_penguji_2', $semhas->nilai_penguji_2 * 5);
        $template->setValue('ratarata_nilai_penguji_2', $semhas->nilai_penguji_2);
        $template->setValue('nip_nidn_penguji_2', $penguji_2->nim_nip_nidn);

        $path_ttd_penguji_2 = $penguji_2->ttd;
        if ($penguji_2->ttd && Storage::exists($path_ttd_penguji_2)) {
            $ttd_penguji_2 = Storage::path($path_ttd_penguji_2);
            $template->setImageValue('ttd_penguji_2', array('path' => $ttd_penguji_2, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Atur Tanggal
        $date = new DateTime($semhas->tanggal_seminar, new DateTimeZone('Asia/Jakarta'));
        $id_nama_bulan = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
        $dateFormatted = $date->format('d n Y');
        $dateFormatted = explode(" ", $dateFormatted);
        $tanggal_seminar = $dateFormatted[0] . " " . $id_nama_bulan[$dateFormatted[1]] . " " . $dateFormatted[2];

        $template->setValue('tanggal', $tanggal_seminar);

        // Atur tanda tangan dosen tabel
        if ($pembimbing_1->ttd && Storage::exists($path_ttd_pembimbing_1)) {
            $ttd_pembimbing_1 = Storage::path($path_ttd_pembimbing_1);
            $template->setImageValue('ttd_pembimbing_1_tabel', array('path' => $ttd_pembimbing_1, 'width' => 80, 'height' => 80, 'ratio' => true));
        }

        if ($pembimbing_2->ttd && Storage::exists($path_ttd_pembimbing_2)) {
            $ttd_pembimbing_2 = Storage::path($path_ttd_pembimbing_2);
            $template->setImageValue('ttd_pembimbing_2_tabel', array('path' => $ttd_pembimbing_2, 'width' => 80, 'height' => 80, 'ratio' => true));
        }

        if ($penguji_1->ttd && Storage::exists($path_ttd_penguji_1)) {
            $ttd_penguji_1 = Storage::path($path_ttd_penguji_1);
            $template->setImageValue('ttd_penguji_1_tabel', array('path' => $ttd_penguji_1, 'width' => 80, 'height' => 80, 'ratio' => true));
        }

        if ($penguji_2->ttd && Storage::exists($path_ttd_penguji_2)) {
            $ttd_penguji_2 = Storage::path($path_ttd_penguji_2);
            $template->setImageValue('ttd_penguji_2_tabel', array('path' => $ttd_penguji_2, 'width' => 80, 'height' => 80, 'ratio' => true));
        }

        // Rata-rata nilai Akhir
        $rata_rata_nilai_akhir = ($semhas->nilai_penguji_1 + $semhas->nilai_penguji_2 + $semhas->nilai_pembimbing_1 + $semhas->nilai_pembimbing_2) / 4;

        $tanggal_awal = '2024-08-31';
        $tanggal_akhir = '2024-12-19';

        $tanggal_seminarFinal = $semhas->tanggal_seminar;

        if ($tanggal_seminarFinal >= $tanggal_awal && $tanggal_seminarFinal <= $tanggal_akhir) {
            $rata_rata_nilai_akhir = round($rata_rata_nilai_akhir);
        }

        if ($rata_rata_nilai_akhir >= 80) {
            $predikat_nilai_akhir = 'A';
        } else if ($rata_rata_nilai_akhir >= 75) {
            $predikat_nilai_akhir = 'A-';
        } else if ($rata_rata_nilai_akhir >= 70) {
            $predikat_nilai_akhir = 'B';
        } else if ($rata_rata_nilai_akhir >= 65) {
            $predikat_nilai_akhir = 'B-';
        } else if ($rata_rata_nilai_akhir >= 60) {
            $predikat_nilai_akhir = 'C';
        } else if ($rata_rata_nilai_akhir >= 50) {
            $predikat_nilai_akhir = 'D';
        } else {
            $predikat_nilai_akhir = 'E';
        }

        $template->setValue('ratarata_nilai_akhir', $rata_rata_nilai_akhir);
        $template->setValue('nilai_huruf', $predikat_nilai_akhir);

        $directory = 'app/public/files/nilai_semhas/';
        $outputFileNameDocx = 'NilaiSeminarHasil_' . preg_replace('/\s+/', '', $semhas->mahasiswa->name) . '_' . $semhas->mahasiswa->nim_nip_nidn . '.docx';
        $docxFilePath = storage_path($directory . $outputFileNameDocx);
        $template->saveAs($docxFilePath);

        return response()->download($docxFilePath)->deleteFileAfterSend();
    }

    public function kirimEmail($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $nilaiSemhas = NilaiSemhas::findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $programStudiId = $nilaiSemhas->mahasiswa->programStudi->id;

        $toKaprodiPivot = UsersPivot::where('id_program_studi', $programStudiId)
            ->where('id_role', 6)
            ->with('user')
            ->first();
        $kaprodiEmail = $toKaprodiPivot->user->email;

        $toSekprodiPivot = UsersPivot::where('id_program_studi', $programStudiId)
            ->where('id_role', 7)
            ->with('user')
            ->first();
        $sekprodiEmail = '';
        if ($toSekprodiPivot) {
            $sekprodiEmail = $toSekprodiPivot->user->email;
        }

        $ccEmails = [
            $nilaiSemhas->pembimbing1->email,
            $nilaiSemhas->pembimbing2->email,
            $nilaiSemhas->penguji1->email,
            $nilaiSemhas->penguji2->email
        ];

        if ($sekprodiEmail != null || $sekprodiEmail != '') {
            array_push($ccEmails, $sekprodiEmail);
        }
        Mail::to($kaprodiEmail)
            ->cc($ccEmails)
            ->send(new JadwalSemhasNotification($nilaiSemhas));

        // Redirect to a success page or perform any additional logic
        return Redirect::back()->with('message', 'Email berhasil dikirim.');
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $nilaiSemhas = NilaiSemhas::findOrFail($id);
            $nilaiSemhas->delete();
            DB::commit();
            return redirect()->back()->with('message', 'Data nilai seminar hasil berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data nilai seminar hasil.');
        }
    }
}
