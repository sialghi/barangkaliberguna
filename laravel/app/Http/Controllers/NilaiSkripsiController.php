<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use App\Models\User;
use App\Models\NilaiSkripsi;
use App\Models\PendaftaranSkripsi;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use App\Notifications\NilaiSkripsiNotification;
use App\Notifications\JadwalSkripsiNotification;

class NilaiSkripsiController extends Controller
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
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $nilaiSkripsi->each(function ($item) {
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
                $nilaiSkripsi = NilaiSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $nilaiSkripsi->each(function ($item) {
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
                $nilaiSkripsi = NilaiSkripsi::where(function ($query) use ($userId) {
                    $query->Where("id_pembimbing_1", $userId)
                        ->orWhere("id_pembimbing_2", $userId)
                        ->orWhere("id_penguji_1", $userId)
                        ->orWhere("id_penguji_2", $userId);
                    })->get()->reject(function ($item) {
                        return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                    });
                $nilaiSkripsi->each(function ($item) {
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
                $nilaiSkripsi = NilaiSkripsi::where('id_mahasiswa', $userId)->get();
                $nilaiSkripsi->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }

            // Merge the fetched nilaiSkripsi with the existing data collection
            $data = $data->merge($nilaiSkripsi);
            $namaDosen = $namaDosen->merge($dosen);
        }

        // Sort data by 'role' to prioritize higher role and make it unique by 'id'
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');
        $namaDosen = $namaDosen->sortBy('name')->unique('id');

        return view('pages/skripsi/nilai_skripsi', compact('data', 'user', 'userRole', 'userPivot', 'namaDosen'));
    }

    public function show($id)
    {
        $nilaiSkripsi = NilaiSkripsi::where('id', $id)
                                    ->with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2')
                                    ->first();
        // $userRole = Auth::user()->role;

        return response()->json([
            'penilaianSkripsi' => $nilaiSkripsi,
            // 'userRole' => $userRole
        ]);
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();
        $nilaiSkripsi = collect();

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                // Fetch dosen's name from the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $nilaiSkripsi = NilaiSkripsi::with('pendaftaranSemhas')->whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('program_studi.id', $fakultasId); // Adjust based on your column name
                })->pluck('id_pendaftaran_skripsi')->toArray();
                $pendaftarSkripsi = PendaftaranSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($fakultasId) {
                    $query->where('program_studi.id', $fakultasId); // Adjust based on your column name
                })->where('status', 'Diterima')
                ->whereNotIn('id', $nilaiSkripsi)
                ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
                ->get();
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                // Fetch dosen's name from the same program studi as the current user
                $dosen = User::whereHas('roles', function($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $nilaiSkripsi = NilaiSkripsi::with('pendaftaranSemhas')->whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->pluck('id_pendaftaran_skripsi')->toArray();
                $pendaftarSkripsi = PendaftaranSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->where('status', 'Diterima')
                ->whereNotIn('id', $nilaiSkripsi)
                ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
                ->get();
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

            //     $nilaiSkripsi = NilaiSkripsi::with('pendaftaranSemhas')->whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
            //         $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
            //     })->pluck('id_pendaftaran_skripsi')->toArray();
            //     $pendaftarSkripsi = PendaftaranSkripsi::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
            //         $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
            //     })->where('status', 'Diterima')
            //     ->whereNotIn('id', $nilaiSkripsi)
            //     ->with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')
            //     ->get();
            // }
        }

        $namaDosen = $namaDosen->sortBy('name')->unique('id');

        return view('pages/skripsi/nilai_skripsi_add', compact('user', 'userRole', 'userPivot', 'namaDosen', 'pendaftarSkripsi'));
    }

    public function store(Request $request)
    {
        $rules = [
            'judulSkripsi' => ['required', 'string', 'max:191'],
            'tanggalUjian' => ['required', 'date'],
            'pembimbing1' => ['required', 'string', 'max:191'],
            'nilaiPembimbing1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pembimbing2' => ['required', 'string', 'max:191'],
            'nilaiPembimbing2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'penguji1' => ['required', 'string', 'max:191'],
            'nilaiPenguji1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'penguji2' => ['required', 'string', 'max:191'],
            'nilaiPenguji2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'jamUjian' => ['nullable', 'string', 'max:191', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'ruanganUjian' => ['nullable', 'string', 'max:191'],
            'linkUjian' => ['nullable', 'string', 'max:191'],
            'tipeUjian' => ['required', 'string', 'max:191'],
        ];

        $messages = [
            'judulSkripsi.required' => 'Judul skripsi wajib diisi',
            'judulSkripsi.string' => 'Judul skripsi harus berupa string',
            'judulSkripsi.max' => 'Judul skripsi maksimal 191 karakter',

            'tanggalUjian.required' => 'Tanggal sidang wajib diisi',
            'tanggalUjian.date' => 'Tanggal sidang harus berupa tanggal',

            'pembimbing1.required' => 'Pembimbing 1 wajib diisi',
            'pembimbing1.string' => 'Pembimbing 1 harus berupa string',
            'pembimbing1.max' => 'Pembimbing 1 maksimal 191 karakter',

            'nilaiPembimbing1.numeric' => 'Nilai pembimbing 1 harus berupa angka',
            'nilaiPembimbing1.min' => 'Minimal nilai adalah 0',
            'nilaiPembimbing1.max' => 'Maksimal nilai adalah 100',

            'pembimbing2.required' => 'Pembimbing 2 wajib diisi',
            'pembimbing2.string' => 'Pembimbing 2 harus berupa string',
            'pembimbing2.max' => 'Pembimbing 2 maksimal 191 karakter',

            'nilaiPembimbing2.numeric' => 'Nilai pembimbing 2 harus berupa angka',
            'nilaiPembimbing2.min' => 'Minimal nilai adalah 0',
            'nilaiPembimbing2.max' => 'Maksimal nilai adalah 100',

            'penguji1.required' => 'Penguji 1 wajib diisi',
            'penguji1.string' => 'Penguji 1 harus berupa string',
            'penguji1.max' => 'Penguji 1 maksimal 191 karakter',

            'nilaiPenguji1.numeric' => 'Nilai penguji 1 harus berupa angka',
            'nilaiPenguji1.min' => 'Minimal nilai adalah 0',
            'nilaiPenguji1.max' => 'Maksimal nilai adalah 100',

            'penguji2.required' => 'Penguji 2 wajib diisi',
            'penguji2.string' => 'Penguji 2 harus berupa string',
            'penguji2.max' => 'Penguji 2 maksimal 191 karakter',

            'nilaiPenguji2.numeric' => 'Nilai penguji 2 harus berupa angka',
            'nilaiPenguji2.min' => 'Minimal nilai adalah 0',
            'nilaiPenguji2.max' => 'Maksimal nilai adalah 100',

            'jamUjian.regex' => 'Format jam ujian harus HH:mm, contoh: 01:04, 18:59',
            'jamUjian.max' => 'Jam ujian maksimal 191 karakter',

            'ruanganUjian.max' => 'Ruangan ujian maksimal 191 karakter',

            'linkUjian.max' => 'Link ujian maksimal 191 karakter',

            'tipeUjian.required' => 'Tipe ujian wajib diisi',
            'tipeUjian.string' => 'Tipe ujian harus berupa string',
            'tipeUjian.max' => 'Tipe ujian maksimal 191 karakter',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->validate();

        // Get the authenticated user's ID
        $user = Auth::user();
        // $userId = $user->id;

        // Dapatkan ID pembimbing 1 dari opsi yang dipilih
        // $pembimbing1Id = $request->input('pembimbing1');

        // // Cari entitas dosen berdasarkan ID pembimbing 1
        // $pembimbing1 = User::where('id', $pembimbing1Id)
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$pembimbing1) {
        //     return redirect()->back()->withErrors(['pembimbing1_not_found' => 'Pembimbing 1 tidak ditemukan']);
        // }


        // // Dapatkan ID pembimbing 2 dari opsi yang dipilih
        // $pembimbing2Id = $request->input('pembimbing2');

        // // Cari entitas dosen berdasarkan ID pembimbing 1
        // $pembimbing2 = User::where('id', $pembimbing2Id)
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$pembimbing2) {
        //     return redirect()->back()->withErrors(['pembimbing2_not_found' => 'Pembimbing 2 tidak ditemukan']);
        // }


        // // Dapatkan ID penguji 1 dari opsi yang dipilih
        // $penguji1Id = $request->input('penguji1');

        // // Cari entitas dosen berdasarkan ID pembimbing 1
        // $penguji1 = User::where('id', $penguji1Id)
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$penguji1) {
        //     return redirect()->back()->withErrors(['penguji1_not_found' => 'Penguji 1 tidak ditemukan']);
        // }


        // // Dapatkan ID penguji 2 dari opsi yang dipilih
        // $penguji2Id = $request->input('penguji2');

        // // Cari entitas dosen berdasarkan ID pembimbing 1
        // $penguji2 = User::where('id', $penguji2Id)
        //     ->whereIn('role', ['dosen', 'kaprodi', 'sekprodi'])
        //     ->first();

        // if (!$penguji2) {
        //     return redirect()->back()->withErrors(['penguji2_not_found' => 'Penguji 2 tidak ditemukan']);
        // }

        DB::beginTransaction();

        try {
            // Proses penyimpanan data jika validasi berhasil
            $nilaiSkripsi = NilaiSkripsi::create([
                'id_mahasiswa' => $request->pendaftarSkripsiId,
                'id_pembimbing_1' => $request->pembimbing1,
                'id_pembimbing_2' => $request->pembimbing2,
                'id_penguji_1' => $request->penguji1,
                'id_penguji_2' => $request->penguji2,

                'judul_skripsi' => $request->judulSkripsi,
                'nilai_pembimbing_1' => $request->nilaiPembimbing1,
                'nilai_pembimbing_2' => $request->nilaiPembimbing2,
                'nilai_penguji_1' => $request->nilaiPenguji1,
                'nilai_penguji_2' => $request->nilaiPenguji2,

                'tanggal_ujian' => $request->tanggalUjian,
                'jam_ujian' => $request->jamUjian,
                'ruangan_ujian' => $request->ruanganUjian,
                'link_ujian' => $request->linkUjian,
                'id_pendaftaran_skripsi' => $request->pendaftarSkripsiSelect,
            ]);

            DB::commit();

            // Kirim email notifikasi ke dosen pembimbing dan penguji
            $userPivot = UsersPivot::where('id_user', $nilaiSkripsi->id_mahasiswa)->first();
            $programStudiId = $userPivot->id_program_studi;

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
                $nilaiSkripsi->pembimbing1->email,
                $nilaiSkripsi->pembimbing2->email,
                $nilaiSkripsi->penguji1->email,
                $nilaiSkripsi->penguji2->email
            ];

            if ($sekprodiEmail != null || $sekprodiEmail != '') {
                array_push($ccEmails, $sekprodiEmail);
            }
            Mail::to($kaprodiEmail)
                ->cc($ccEmails)
                ->send(new JadwalSkripsiNotification($nilaiSkripsi));

            return redirect()->route('nilai.sidang.skripsi')->with('message', 'Berhasil Diinput.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('nilai.sidang.skripsi')->with('error', 'Gagal Diinput.'. $e->getMessage());
        }
    }

    public function simpanNilai(Request $request, $id)
    {
        // Validasi input
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

            $validator = Validator::make($request->all(), $rules, $messages);
            $validator->validate();

            // Cari nilai skripsi berdasarkan ID
            $nilaiSkripsi = NilaiSkripsi::findOrFail($id);

            // Update nilai skripsi
            // $listDosen = ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2'];
            // foreach ($listDosen as $dosen) {
            //     if ($request->has('nilai_' . $dosen)) {
            //         $nilaiSkripsi->{'nilai_' . $dosen} = $request->input('nilai_' . $dosen);
            //     }
            $nilaiSkripsi->nilai_pembimbing_1 = $request->has('nilai_pembimbing_1') ? $request->input('nilai_pembimbing_1') : $nilaiSkripsi->nilai_pembimbing_1;
            $nilaiSkripsi->nilai_pembimbing_2 = $request->has('nilai_pembimbing_2') ? $request->input('nilai_pembimbing_2') : $nilaiSkripsi->nilai_pembimbing_2;
            $nilaiSkripsi->nilai_penguji_1 = $request->has('nilai_penguji_1') ? $request->input('nilai_penguji_1') : $nilaiSkripsi->nilai_penguji_1;
            $nilaiSkripsi->nilai_penguji_2 = $request->has('nilai_penguji_2') ? $request->input('nilai_penguji_2') : $nilaiSkripsi->nilai_penguji_2;

            // simpan nilai skripsi
            $nilaiSkripsi->save();

            DB::commit();

            // Redirect ke halaman nilai skripsi
            return redirect()->route('nilai.sidang.skripsi')->with('message', 'Nilai skripsi berhasil disimpan.');
        } catch (DecryptException $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.sidang.skripsi')->with('error', 'Nilai skripsi gagal disimpan.');
        }
    }

    public function generateDocx($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $skripsi = NilaiSkripsi::findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        // Enable output escaping
        Settings::setOutputEscapingEnabled(true);

        // Load the .docx template
        $template = new TemplateProcessor(storage_path("app/public/files/nilai_skripsi/template/Nilai_Sidang_Skripsi_Template_Modified.docx"));

        $template->setValue('nama_mahasiswa', $skripsi->mahasiswa->name);
        $template->setValue('nim', $skripsi->mahasiswa->nim_nip_nidn);
        $template->setValue('judul', $skripsi->judul_skripsi);

        // Pembimbing 1
        $pembimbing_1 = User::findOrFail($skripsi->id_pembimbing_1);

        $template->setValue('pembimbing_1', $pembimbing_1->name);
        $template->setValue('nilai_pembimbing_1', $skripsi->nilai_pembimbing_1);
        $template->setValue('jumlah_nilai_pembimbing_1', $skripsi->nilai_pembimbing_1 * 4);
        $template->setValue('ratarata_nilai_pembimbing_1', $skripsi->nilai_pembimbing_1);
        $template->setValue('nip_nidn_pembimbing_1', $pembimbing_1->nim_nip_nidn);

        $path_ttd_pembimbing_1 = $pembimbing_1->ttd;
        if ($pembimbing_1->ttd && Storage::exists($path_ttd_pembimbing_1)) {
            $ttd_pembimbing_1 = Storage::path($path_ttd_pembimbing_1);
            $template->setImageValue('ttd_pembimbing_1', array('path' => $ttd_pembimbing_1, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Pembimbing 2
        $pembimbing_2 = User::findOrFail($skripsi->id_pembimbing_2);

        $template->setValue('pembimbing_2', $pembimbing_2->name);
        $template->setValue('nilai_pembimbing_2', $skripsi->nilai_pembimbing_2);
        $template->setValue('jumlah_nilai_pembimbing_2', $skripsi->nilai_pembimbing_2 * 4);
        $template->setValue('ratarata_nilai_pembimbing_2', $skripsi->nilai_pembimbing_2);
        $template->setValue('nip_nidn_pembimbing_2', $pembimbing_2->nim_nip_nidn);

        $path_ttd_pembimbing_2 = $pembimbing_2->ttd;
        if ($pembimbing_1->ttd && Storage::exists($path_ttd_pembimbing_2)) {
            $ttd_pembimbing_2 = Storage::path($path_ttd_pembimbing_2);
            $template->setImageValue('ttd_pembimbing_2', array('path' => $ttd_pembimbing_2, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Penguji 1
        $penguji_1 = User::findOrFail($skripsi->id_penguji_1);

        $template->setValue('penguji_1', $penguji_1->name);
        $template->setValue('nilai_penguji_1', $skripsi->nilai_penguji_1);
        $template->setValue('jumlah_nilai_penguji_1', $skripsi->nilai_penguji_1 * 3);
        $template->setValue('ratarata_nilai_penguji_1', $skripsi->nilai_penguji_1);
        $template->setValue('nip_nidn_penguji_1', $penguji_1->nim_nip_nidn);

        $path_ttd_penguji_1 = $penguji_1->ttd;
        if ($penguji_1->ttd && Storage::exists($path_ttd_penguji_1)) {
            $ttd_penguji_1 = Storage::path($path_ttd_penguji_1);
            $template->setImageValue('ttd_penguji_1', array('path' => $ttd_penguji_1, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Penguji 2
        $penguji_2 = User::findOrFail($skripsi->id_penguji_2);

        $template->setValue('penguji_2', $penguji_2->name);
        $template->setValue('nilai_penguji_2', $skripsi->nilai_penguji_2);
        $template->setValue('jumlah_nilai_penguji_2', $skripsi->nilai_penguji_2 * 3);
        $template->setValue('ratarata_nilai_penguji_2', $skripsi->nilai_penguji_2);
        $template->setValue('nip_nidn_penguji_2', $penguji_2->nim_nip_nidn);

        $path_ttd_penguji_2 = $penguji_2->ttd;
        if ($penguji_2->ttd && Storage::exists($path_ttd_penguji_2)) {
            $ttd_penguji_2 = Storage::path($path_ttd_penguji_2);
            $template->setImageValue('ttd_penguji_2', array('path' => $ttd_penguji_2, 'width' => 120, 'height' => 120, 'ratio' => true));
        }

        // Atur Tanggal
        $date = new DateTime($skripsi->tanggal_ujian, new DateTimeZone('Asia/Jakarta'));
        $nama_bulan_id = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
        $dateFormatted = $date->format('d n Y');
        $dateFormatted = explode(" ", $dateFormatted);
        $tanggal_ujian = $dateFormatted[0] . " " . $nama_bulan_id[$dateFormatted[1]] . " " . $dateFormatted[2];

        $template->setValue('tanggal', $tanggal_ujian);

        // Atur Nilai Akhir
        $ratarata_nilai_akhir = ($skripsi->nilai_pembimbing_1 + $skripsi->nilai_pembimbing_2 + $skripsi->nilai_penguji_1 + $skripsi->nilai_penguji_2) / 4;
        $template->setValue('ratarata_nilai_akhir', $ratarata_nilai_akhir);

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

        $directory = 'app/public/files/nilai_skripsi/';
        $outputFileNameDocx = 'NilaiSidangSkripsi_' . preg_replace('/\s+/', '', $skripsi->mahasiswa->name) . '_' . $skripsi->mahasiswa->nim_nip_nidn . '.docx';
        $docxFilePath = storage_path($directory . $outputFileNameDocx);
        $template->saveAs($docxFilePath);

        return response()->download($docxFilePath)->deleteFileAfterSend();
    }

    public function kirimEmail($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $nilaiSkripsi = NilaiSkripsi::findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        // Kirim email notifikasi ke dosen pembimbing dan penguji
        $programStudiId = $nilaiSkripsi->mahasiswa->programStudi->id;

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
            $nilaiSkripsi->pembimbing1->email,
            $nilaiSkripsi->pembimbing2->email,
            $nilaiSkripsi->penguji1->email,
            $nilaiSkripsi->penguji2->email
        ];

        if ($sekprodiEmail != null || $sekprodiEmail != '') {
            array_push($ccEmails, $sekprodiEmail);
        }
        Mail::to($kaprodiEmail)
            ->cc($ccEmails)
            ->send(new JadwalSkripsiNotification($nilaiSkripsi));

        // Redirect to a success page or perform any additional logic
        return Redirect::back()->with('message', 'Email berhasil dikirim.');
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $nilaiSkripsi = NilaiSkripsi::findOrFail($id);
            $nilaiSkripsi->delete();

            DB::commit();

            return redirect()->route('nilai.sidang.skripsi')->with('message', 'Nilai skripsi berhasil dihapus.');
        } catch (DecryptException $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.sidang.skripsi')->with('error', 'Nilai skripsi gagal dihapus.');
        }
    }
}
