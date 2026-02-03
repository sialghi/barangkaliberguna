<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NilaiSemhas;
use App\Models\User;
use App\Models\UsersPivot;
use App\Models\PendaftaranSemhas;
use App\Models\BimbinganSkripsi;
use App\Models\PendaftaranSempro;
use App\Notifications\DaftarSemhasAcceptNotification;
use App\Notifications\DaftarSemhasRejectNotification;
use App\Notifications\DaftarSemhasReviseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DaftarSemhasController extends Controller
{
    public function index()
    {
        // 1. Ambil data user dan role pivot dengan Eager Loading untuk menghindari N+1 query
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with(['role', 'programStudi', 'fakultas.programStudi'])
            ->orderBy('id_role', 'desc')
            ->get();

        // 2. Inisialisasi koleksi dan variabel pendukung
        $data = collect();
        $namaDosenRaw = collect();
        $bimbinganCount = 0;
        $reviseCount = 0;

        // 3. Tentukan relasi pendaftaran yang akan di-eager load untuk tampilan tabel di View
        $semhasRelations = [
            'mahasiswa.programStudi',
            'mahasiswa.fakultas',
            // 'pembimbing1',
            // 'pembimbing2',
            // 'dosenPembimbingAkademik',
            // 'calonPenguji1',
            // 'calonPenguji2',
            'kategoriTa'
        ];

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // Inisialisasi base query pendaftaran
            $query = PendaftaranSemhas::with($semhasRelations);

            // --- LOGIKA FILTER BERDASARKAN ROLE ---

            // Role Dekanat: Melihat semua data dalam lingkup Fakultas
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $daftarSemhas = $query->whereHas('mahasiswa.fakultas', function ($q) use ($fakultasId) {
                    $q->where('fakultas.id', $fakultasId);
                })->get();

                $currentRole = 'admin';
                $targetStatus = 'Revisi Diajukan';

                // Ambil dosen satu fakultas (Lintas Prodi)
                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->whereHas('fakultas', fn($q) => $q->where('fakultas.id', $fakultasId))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();

                // $dosenAkademik = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                //     ->whereHas('fakultas', fn($q) => $q->where('id', $fakultasId))
                //     ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                //     ->select('id', 'name')->get();

                // Role Prodi: Melihat semua data dalam lingkup Program Studi
            } else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $daftarSemhas = $query->whereHas('mahasiswa.programStudi', function ($q) use ($programStudiId) {
                    $q->where('program_studi.id', $programStudiId);
                })->get();

                $currentRole = 'admin';
                $targetStatus = 'Revisi Diajukan';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();

                // Role Dosen: Melihat mahasiswa bimbingan atau ujian (Bisa Lintas Prodi)
            } else if ($role === 'dosen') {
                $daftarSemhas = $query->where(function ($q) use ($user) {
                    $q->where('id_dosen_pembimbing_akademik', $user->id)
                        ->orWhere('id_dosen_pembimbing_1', $user->id)
                        ->orWhere('id_dosen_pembimbing_2', $user->id)
                        ->orWhere('id_calon_penguji_1', $user->id)
                        ->orWhere('id_calon_penguji_2', $user->id);
                })->get();

                $currentRole = 'dosen';
                $targetStatus = 'Revisi';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->whereHas('programStudi', fn($q) => $q->where('program_studi.id', $programStudiId))
                    ->select('id', 'name')->get();

                // Role Mahasiswa: Hanya melihat data dirinya sendiri
            } else if ($role === 'mahasiswa') {
                $daftarSemhas = $query->where('id_mahasiswa', $user->id)->get();
                $currentRole = 'mahasiswa';
                $targetStatus = 'Revisi';

                $bimbinganCount = BimbinganSkripsi::where('id_mahasiswa', $user->id)->count();

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();
            }

            // 4. Transformasi Koleksi: Reject data sampah, set role atribut, dan hitung revisi
            $daftarSemhas = $daftarSemhas->reject(fn($item) => is_null($item->mahasiswa))
                ->each(function ($item) use ($currentRole) {
                    $item->role = $currentRole;
                });

            // Hitung Revisi secara efisien dari koleksi yang sudah ada
            $reviseCount += $daftarSemhas->where('status', $targetStatus)->count();

            // Merge data ke koleksi utama
            $data = $data->merge($daftarSemhas);
            $namaDosenRaw = $namaDosenRaw->merge($dosen);
        }

        // 5. Finalisasi Data (Unique, Map untuk Display, dan Sort)
        $hasRevise = $reviseCount > 0 ? (in_array('mahasiswa', $userRole) || in_array('dosen', $userRole) ? "Revisi" : "Revisi Diajukan") : null;

        // Memproses tampilan nama dosen "Nama - Prodi" dan Sorting
        $namaDosen = $namaDosenRaw->unique('id')->map(function ($item) {
            $prodi = $item->programStudi->first()->nama ?? 'No Prodi';
            $item->nama_prodi_sort = $prodi;
            $item->display_name = $item->name . " - " . $prodi;
            return $item;
        })->sortBy([
            ['nama_prodi_sort', 'asc'],
            ['name', 'asc']
        ]);

        // Sorting data pendaftaran
        $data = $data->sortBy('role')->unique('id')->sortBy('created_at');

        // 6. Return ke view
        return view('pages/semhas/daftar_seminar_hasil', compact(
            'data',
            'bimbinganCount',
            'userRole',
            'namaDosen',
            'userPivot',
            'hasRevise',
            'reviseCount'
        ));
    }

    public function show($id)
    {
        $pendaftaranSemhas = PendaftaranSemhas::where('id', $id)
            ->with('mahasiswa', 'dosenPembimbingAkademik', 'calonPenguji1', 'calonPenguji2', 'pembimbing1', 'pembimbing2')
            ->first();

        return response()->json([
            "pendaftaranSemhas" => $pendaftaranSemhas,
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

                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                // Dosen Lintas Prodi
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();
                // Dosen Akademik
                $dosenAkademik = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId);
                })->select('id', 'name')->without(['pivot', 'roles'])->get();
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

        return view('pages/semhas/daftar_seminar_hasil_add', compact('user', 'userPivot', 'namaDosen', 'namaDosenAkademik'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->roles->pluck('nama')->toArray();

        $pendaftaranSempro = PendaftaranSempro::where('id_mahasiswa', $userId)->orderBy('created_at', 'desc')->first();
        if (!$pendaftaranSempro) {
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Silahkan melakukan pendaftaran sempro terlebih dahulu');
        }

        if (array_intersect($userRole, ['mahasiswa'])) {
            $bimbinganDosen = BimbinganSkripsi::where('id_mahasiswa', $userId)
                ->pluck('id_pembimbing');
            $bimbinganDosenFiltered = array_unique($bimbinganDosen->toArray());

            $bimbinganDosenCountTotal = 0;
            foreach ($bimbinganDosenFiltered as $dosenId) {
                $bimbinganDosenCount = BimbinganSkripsi::where('id_mahasiswa', $userId)
                    ->where('id_pembimbing', $dosenId)
                    ->count();
                $bimbinganDosenCountTotal += $bimbinganDosenCount;
            }

            if ($bimbinganDosenCountTotal < 16) {
                return redirect()->route('daftar.seminar.hasil')->with('error', 'Harus telah melakukan minimal 16 kali bimbingan dengan dosen pembimbing');
            }
        }

        $rules = [
            'judulSkripsi' => 'required|string|max:191',
            'waktuSeminar' => 'required|date',
            'dosenPembimbingAkademik' => 'required|exists:users,id',
            'pembimbing1' => 'required|exists:users,id',
            'pembimbing2' => 'required|exists:users,id',
            'fileTranskripNilai' => 'required|file|mimes:pdf|max:15360',
            'filePernyataanKaryaSendiri' => 'required|file|mimes:pdf|max:15360',
            'filePengesahanSkripsi' => 'required|file|mimes:pdf|max:15360',
            'fileSertifikatToafl.*' => 'required|file|mimes:pdf|max:15360',
            'fileSertifikatToef1.*' => 'required|file|mimes:pdf|max:15360',
            'fileNaskahSkripsi' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'judulSkripsi.required' => 'Judul Skripsi wajib diisi',
            'judulSkripsi.string' => 'Judul Skripsi harus berupa string',
            'judulSkripsi.max' => 'Judul Skripsi maksimal 191 karakter',

            'waktuSeminar.required' => 'Waktu Ujian wajib diisi',
            'waktuSeminar.date' => 'Waktu Ujian harus dalam format tanggal',

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

            'filePernyataanKaryaSendiri.required' => 'File Pernyataan Karya Sendiri wajib diisi',
            'filePernyataanKaryaSendiri.file' => 'File Pernyataan Karya Sendiri yang dipilih tidak valid',
            'filePernyataanKaryaSendiri.mimes' => 'File Pernyataan Karya Sendiri harus dalam format PDF',
            'filePernyataanKaryaSendiri.max' => 'Ukuran File Pernyataan Karya Sendiri tidak boleh melebihi 15MB',

            'filePengesahanSkripsi.required' => 'File Pengesahan Skripsi wajib diisi',
            'filePengesahanSkripsi.file' => 'File Pengesahan Skripsi yang dipilih tidak valid',
            'filePengesahanSkripsi.mimes' => 'File Pengesahan Skripsi harus dalam format PDF',
            'filePengesahanSkripsi.max' => 'Ukuran File Pengesahan Skripsi tidak boleh melebihi 15MB',

            'fileSertifikatToafl.required' => 'File Sertifikat TOAFL wajib diisi',
            'fileSertifikatToafl.*.file' => 'File Sertifikat TOAFL yang dipilih tidak valid',
            'fileSertifikatToafl.*.mimes' => 'File Sertifikat TOAFL harus dalam format PDF',
            'fileSertifikatToafl.*.max' => 'Ukuran File Sertifikat TOAFL tidak boleh melebihi 15MB',

            'fileSertifikatToefl.required' => 'File Sertifikat TOEFL wajib diisi',
            'fileSertifikatToefl.*.file' => 'File Sertifikat TOEFL yang dipilih tidak valid',
            'fileSertifikatToefl.*.mimes' => 'File Sertifikat TOEFL harus dalam format PDF',
            'fileSertifikatToefl.*.max' => 'Ukuran File Sertifikat TOEFL tidak boleh melebihi 15MB',

            'fileNaskahSkripsi.required' => 'File Naskah Skripsi wajib diisi',
            'fileNaskahSkripsi.file' => 'File Naskah Skripsi yang dipilih tidak valid',
            'fileNaskahSkripsi.mimes' => 'File Naskah Skripsi harus dalam format PDF',
            'fileNaskahSkripsi.max' => 'Ukuran File Naskah Skripsi tidak boleh melebihi 15MB',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        $files = [
            'fileTranskripNilai',
            'filePernyataanKaryaSendiri',
            'filePengesahanSkripsi',
            'fileSertifikatToafl',
            'fileSertifikatToefl',
            'fileNaskahSkripsi'
        ];

        $sertifikatToaflFiles = $request->file('fileSertifikatToafl');
        $sertifikatToeflFiles = $request->file('fileSertifikatToefl');

        // Array to store the file paths
        $sertifikatToaflPaths = [];
        $sertifikatToeflPaths = [];
        $fileDaftarSemhas = [];
        $fileNameArray = [];

        foreach ($files as $file) {
            if ($file == 'fileSertifikatToafl') {
                foreach ($sertifikatToaflFiles as $sertifikatToaflFile) {
                    $fileName = time() . '_' . random_int(1, 300) . '_' . $sertifikatToaflFile->getClientOriginalName();
                    $filePath = 'public/files/daftar_semhas/';
                    array_push($sertifikatToaflPaths, $fileName);
                    // Save the file
                    Storage::putFileAs($filePath, $sertifikatToaflFile, $fileName);
                    array_push($fileNameArray, $fileName);
                }
            } elseif ($file == 'fileSertifikatToefl') {
                // Handle multiple files on File Sertifikat Toefl
                foreach ($sertifikatToeflFiles as $sertifikatToeflFile) {
                    $fileName = time() . '_' . random_int(1, 300) . '_' . $sertifikatToeflFile->getClientOriginalName();
                    $filePath = 'public/files/daftar_semhas/';
                    array_push($sertifikatToeflPaths, $fileName);
                    // Save the file
                    Storage::putFileAs($filePath, $sertifikatToeflFile, $fileName);
                    array_push($fileNameArray, $fileName);
                }
            } else {
                $fileName = time() . '_' . random_int(1, 300) . '_' . $request->file($file)->getClientOriginalName();
                $filePath = 'public/files/daftar_semhas/';
                $fileDaftarSemhas[$file] = $fileName;
                Storage::putFileAs($filePath, $request->file($file), $fileName);
                array_push($fileNameArray, $fileName);
            }
        }

        DB::beginTransaction();

        try {
            PendaftaranSemhas::create([
                'id_mahasiswa' => $userId,
                'judul_skripsi' => $request->judulSkripsi,
                'waktu_seminar' => $request->waktuSeminar,
                'id_dosen_pembimbing_akademik' => $request->dosenPembimbingAkademik,
                'id_dosen_pembimbing_1' => $request->pembimbing1,
                'id_dosen_pembimbing_2' => $request->pembimbing2,
                'id_calon_penguji_1' => $request->calonPenguji1,
                'id_calon_penguji_2' => $request->calonPenguji2,
                'calon_penguji_3_name' => $request->calonPenguji3,
                'file_transkrip_nilai' => $fileDaftarSemhas['fileTranskripNilai'],
                'file_pernyataan_karya_sendiri' => $fileDaftarSemhas['filePernyataanKaryaSendiri'],
                'file_pengesahan_skripsi' => $fileDaftarSemhas['filePengesahanSkripsi'],
                'file_naskah_skripsi' => $fileDaftarSemhas['fileNaskahSkripsi'],
                'file_sertifikat_toafl_1' => isset($sertifikatToaflPaths[0]) ? $sertifikatToaflPaths[0] : null,
                'file_sertifikat_toafl_2' => isset($sertifikatToaflPaths[1]) ? $sertifikatToaflPaths[1] : null,
                'file_sertifikat_toafl_3' => isset($sertifikatToaflPaths[2]) ? $sertifikatToaflPaths[2] : null,
                'file_sertifikat_toefl_1' => isset($sertifikatToeflPaths[0]) ? $sertifikatToeflPaths[0] : null,
                'file_sertifikat_toefl_2' => isset($sertifikatToeflPaths[1]) ? $sertifikatToeflPaths[1] : null,
                'file_sertifikat_toefl_3' => isset($sertifikatToeflPaths[2]) ? $sertifikatToeflPaths[2] : null,
                'id_kategori_ta' => isset($pendaftaranSempro) ? $pendaftaranSempro->id_kategori_ta : null,
            ]);

            $pendaftaranDiproses = PendaftaranSemhas::where('id_mahasiswa', $userId)
                ->where('judul_skripsi', $request->judulSkripsi)
                ->where('waktu_seminar', $request->waktuSeminar)
                ->where('id_dosen_pembimbing_akademik', $request->dosenPembimbingAkademik)
                ->where('id_dosen_pembimbing_1', $request->pembimbing1)
                ->where('id_dosen_pembimbing_2', $request->pembimbing2)
                ->where('id_calon_penguji_1', $request->calonPenguji1)
                ->where('id_calon_penguji_2', $request->calonPenguji2)
                ->where('calon_penguji_3_name', $request->calonPenguji3)
                ->count();

            if ($pendaftaranDiproses > 1) {
                DB::rollback();
                return redirect()->route('daftar.seminar.hasil')->with('error', 'Gagal mendaftar.');
            }

            DB::commit();

            return redirect()->route('daftar.seminar.hasil')->with('message', 'Berhasil mendaftar.');
        } catch (\Exception $e) {
            DB::rollback();

            foreach ($fileNameArray as $fileName) {
                if (Storage::exists("public/files/daftar_semhas/$fileName")) {
                    Storage::disk('local')->delete("public/files/daftar_semhas/$fileName");
                }
            }

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Gagal mendaftar.');
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $pendaftaranSemhas = PendaftaranSemhas::where('id', $id)
            ->with('mahasiswa', 'dosenPembimbingAkademik', 'calonPenguji1', 'calonPenguji2', 'pembimbing1', 'pembimbing2')
            ->first();

        $namaDosen = collect();
        $namaDosenAkademik = collect();

        // TAMBAHKAN INI: Inisialisasi variabel sebagai koleksi kosong
        $dosen = collect();
        $dosenAkademik = collect();

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Tambahkan prefix 'fakultas.'
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
                // Dosen Lintas Prodi
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($query2) {
                    $query2->select('program_studi.id', 'nama');
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();
                // Dosen Akademik
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

        return view('pages/semhas/daftar_seminar_hasil_edit', compact('user', 'userPivot', 'pendaftaranSemhas', 'namaDosen', 'namaDosenAkademik'));
    }

    public function update(Request $request, $id)
    {
        // return response()->json($request->all());
        $pendaftaranSemhas = PendaftaranSemhas::find($id);
        if (!$pendaftaranSemhas) {
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Data tidak ditemukan.');
        }
        if ($pendaftaranSemhas->status !== 'Revisi') {
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Data tidak dapat diubah karena status bukan "Revisi".');
        }

        // Mendeclare nama file yang akan diupload
        $files = [
            'fileTranskripNilai' => 'file_transkrip_nilai',
            'filePernyataanKaryaSendiri' => 'file_pernyataan_karya_sendiri',
            'filePengesahanSkripsi' => 'file_pengesahan_skripsi',
            'fileSertifikatToafl' => 'file_sertifikat_toafl',
            'fileSertifikatToefl' => 'file_sertifikat_toefl',
            'fileNaskahSkripsi' => 'file_naskah_skripsi',
        ];

        $sertifikatToaflFiles = $request->file('fileSertifikatToafl');
        $sertifikatToeflFiles = $request->file('fileSertifikatToefl');

        // Array untuk menyimpan nama file
        $sertifikatToaflPaths = [];
        $sertifikatToeflPaths = [];
        $fileDaftarSemhas = [];
        $fileNameArray = [];

        DB::beginTransaction();

        try {
            // Handle tiap file
            foreach ($files as $inputName => $dbField) {
                if ($inputName == 'fileSertifikatToafl' && $request->hasFile('fileSertifikatToafl')) {
                    // Delete old files
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_1}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_1}");
                    }
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_2}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_2}");
                    }
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_3}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toafl_3}");
                    }
                    // Save new files
                    foreach ($sertifikatToaflFiles as $sertifikatToaflFile) {
                        $fileName = time() . '_' . random_int(1, 300) . '_' . $sertifikatToaflFile->getClientOriginalName();
                        $filePath = 'public/files/daftar_semhas/';
                        array_push($sertifikatToaflPaths, $fileName);
                        Storage::putFileAs($filePath, $sertifikatToaflFile, $fileName);
                        array_push($fileNameArray, $fileName);
                    }
                    $pendaftaranSemhas->update([
                        'file_sertifikat_toafl_1' => isset($sertifikatToaflPaths[0]) ? $sertifikatToaflPaths[0] : null,
                        'file_sertifikat_toafl_2' => isset($sertifikatToaflPaths[1]) ? $sertifikatToaflPaths[1] : null,
                        'file_sertifikat_toafl_3' => isset($sertifikatToaflPaths[2]) ? $sertifikatToaflPaths[2] : null,
                    ]);
                } elseif ($inputName == 'fileSertifikatToefl' && $request->hasFile('fileSertifikatToefl')) {
                    // Delete old files
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_1}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_1}");
                    }
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_2}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_2}");
                    }
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_3}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->file_sertifikat_toefl_3}");
                    }
                    // Save new files
                    foreach ($sertifikatToeflFiles as $sertifikatToeflFile) {
                        $fileName = time() . '_' . random_int(1, 300) . '_' . $sertifikatToeflFile->getClientOriginalName();
                        $filePath = 'public/files/daftar_semhas/';
                        array_push($sertifikatToeflPaths, $fileName);
                        Storage::putFileAs($filePath, $sertifikatToeflFile, $fileName);
                        array_push($fileNameArray, $fileName);
                    }
                    $pendaftaranSemhas->update([
                        'file_sertifikat_toefl_1' => isset($sertifikatToeflPaths[0]) ? $sertifikatToeflPaths[0] : null,
                        'file_sertifikat_toefl_2' => isset($sertifikatToeflPaths[1]) ? $sertifikatToeflPaths[1] : null,
                        'file_sertifikat_toefl_3' => isset($sertifikatToeflPaths[2]) ? $sertifikatToeflPaths[2] : null,
                    ]);
                } elseif ($request->hasFile($inputName)) {
                    // Delete old file
                    if (Storage::exists("public/files/daftar_semhas/{$pendaftaranSemhas->$dbField}")) {
                        Storage::delete("public/files/daftar_semhas/{$pendaftaranSemhas->$dbField}");
                    }
                    // Save new file
                    $uploadedFile = $request->file($inputName);
                    $fileName = time() . '_' . random_int(1, 300) . '_' . $uploadedFile->getClientOriginalName();
                    $filePath = 'public/files/daftar_semhas/';
                    $fileDaftarSemhas[$dbField] = $fileName;
                    Storage::putFileAs($filePath, $uploadedFile, $fileName);
                    array_push($fileNameArray, $fileName);

                    // Update the record with the new file name
                    $pendaftaranSemhas->update([
                        $dbField => $fileName
                    ]);
                }
            }

            // Update data pendaftaran semhas
            $pendaftaranSemhas->update([
                'judul_skripsi' => $request->judulSkripsi ?? $pendaftaranSemhas->judul_skripsi,
                'waktu_seminar' => $request->waktuSeminar ?? $pendaftaranSemhas->waktu_seminar,
                'id_dosen_pembimbing_akademik' => $request->dosenPembimbingAkademik ?? $pendaftaranSemhas->id_dosen_pembimbing_akademik,
                'id_dosen_pembimbing_1' => $request->pembimbing1 ?? $pendaftaranSemhas->id_dosen_pembimbing_1,
                'id_dosen_pembimbing_2' => $request->pembimbing2 ?? $pendaftaranSemhas->id_dosen_pembimbing_2,
                'id_calon_penguji_1' => $request->calonPenguji1 ?? $pendaftaranSemhas->id_calon_penguji_1,
                'id_calon_penguji_2' => $request->calonPenguji2 ?? $pendaftaranSemhas->id_calon_penguji_2,
                'calon_penguji_3_name' => $request->calonPenguji3 ?? $pendaftaranSemhas->calon_penguji_3_name,
            ]);

            if (Auth::user()->role == 'mahasiswa') {
                $pendaftaranSemhas->update([
                    'status' => 'Revisi Diajukan',
                ]);
            }

            DB::commit();

            return redirect()->route('daftar.seminar.hasil')->with('message', 'Data berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollback();
            // Menghapus file yang telah diupload tadi
            foreach ($fileNameArray as $fileName) {
                Storage::delete('public/files/daftar_semhas/' . $fileName);
            }

            return redirect()->route('daftar.seminar.hasil')->with('error', 'Data gagal diubah.');
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranSemhas = PendaftaranSemhas::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

            $pendaftaranSemhas->status = 'Diterima';
            $pendaftaranSemhas->alasan = '';
            $pendaftaranSemhas->save();

            DB::commit();

            Mail::to($pendaftaranSemhas->mahasiswa->email)
                ->send(new DaftarSemhasAcceptNotification($pendaftaranSemhas));

            return redirect()->route('daftar.seminar.hasil')->with('message', 'Status data berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Status data gagal diubah menjadi disetujui.');
        }
    }

    public function reject(Request $request, $id)
    {
        $pendaftaranSemhas = PendaftaranSemhas::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

        DB::beginTransaction();

        try {
            $pendaftaranSemhas->status = 'Ditolak';
            $pendaftaranSemhas->alasan = $request->alasan ?? 'Tidak ada alasan';
            $pendaftaranSemhas->save();

            DB::commit();

            Mail::to($pendaftaranSemhas->mahasiswa->email)
                ->send(new DaftarSemhasRejectNotification($pendaftaranSemhas));

            return redirect()->route('daftar.seminar.hasil')->with('message', 'Status data ditolak');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Status data gagal diubah menjadi ditolak.');
        }
    }

    public function revise(Request $request, $id)
    {
        $pendaftaranSemhas = PendaftaranSemhas::with('mahasiswa', 'dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calonPenguji1', 'calonPenguji2')->findOrFail($id);

        DB::beginTransaction();

        try {
            $pendaftaranSemhas->status = 'Revisi';
            $pendaftaranSemhas->alasan = $request->alasan ?? 'Tidak ada alasan';
            $pendaftaranSemhas->save();

            DB::commit();

            Mail::to($pendaftaranSemhas->mahasiswa->email)
                ->send(new DaftarSemhasReviseNotification($pendaftaranSemhas));

            return redirect()->back()->with('message', 'Status data revisi.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->back()->with('error', 'Status data gagal diubah menjadi revisi.');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $pendaftaranSemhas = PendaftaranSemhas::findOrFail($id);

            $pendaftaranSemhas->delete();

            DB::commit();

            $listFile = [
                $pendaftaranSemhas->file_transkrip_nilai,
                $pendaftaranSemhas->file_pernyataan_karya_sendiri,
                $pendaftaranSemhas->file_pengesahan_skripsi,
                $pendaftaranSemhas->file_sertifikat_toafl_1,
                $pendaftaranSemhas->file_sertifikat_toafl_2,
                $pendaftaranSemhas->file_sertifikat_toafl_3,
                $pendaftaranSemhas->file_sertifikat_toefl_1,
                $pendaftaranSemhas->file_sertifikat_toefl_2,
                $pendaftaranSemhas->file_sertifikat_toefl_3,
                $pendaftaranSemhas->file_naskah_skripsi,
            ];
            foreach ($listFile as $file) {
                if (Storage::exists("public/files/daftar_semhas/{$file}")) {
                    Storage::disk('local')->delete('public/files/daftar_semhas/' . $file);
                }
            }

            return redirect()->route('daftar.seminar.hasil')->with('message', 'Data berhasil dihapus.');
        } catch (QueryException $e) {
            DB::rollback();

            if ($e->errorInfo[1] === 1451) {
                // Handle the foreign key constraint violation error
                Log::error($e->getMessage());
                return redirect()->route('daftar.seminar.hasil')->with('error', 'Data gagal dihapus karena data ini direferensikan oleh data lain.');
            }

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.hasil')->with('error', 'Data gagal dihapus.');
        }
    }

    public function viewPdf($id)
    {
        $pathDaftarSemhas = storage_path('app/public/files/daftar_semhas/' . $id);

        if (file_exists($pathDaftarSemhas)) {
            $headers = [
                'Content-Type' => 'application/pdf',
            ];
            return response()->file($pathDaftarSemhas, $headers);
        } else {
            abort(404);
        };
    }
}
