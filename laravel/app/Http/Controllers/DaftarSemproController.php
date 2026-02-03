<?php

namespace App\Http\Controllers;

use App\Models\KategoriTA;
use App\Models\PendaftaranSempro;
use App\Models\User;
use App\Models\PeriodeSempro;
use App\Models\UsersPivot;

use App\Notifications\DaftarSemproAcceptNotification;
use App\Notifications\DaftarSemproRejectNotification;
use App\Notifications\DaftarSemproReviseNotification;
use App\Rules\FileNameValidator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class DaftarSemproController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        // Optimasi 1: Eager load pivot, role, prodi, dan fakultas user yang login
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with(['role', 'programStudi', 'fakultas.programStudi'])
            ->orderBy('id_role', 'desc')
            ->get();

        $namaDosen = collect();
        $data = collect();
        $waktuSempro = collect();

        $hasRevise = '';
        $reviseCount = 0;

        // Optimasi 2: Eager load semua relasi yang dipanggil di dalam tabel (View)
        $pendaftaranRelations = [
            'mahasiswa.programStudi',
            'mahasiswa.fakultas',
            'kategoriTa',
            'calonDospem1', // Sesuaikan dengan nama fungsi relasi di model Anda
            'calonDospem2'
        ];

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            $query = PendaftaranSempro::with($pendaftaranRelations);

            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $daftarSempro = $query->whereHas('mahasiswa.fakultas', function ($q) use ($fakultasId) {
                    $q->where('fakultas.id', $fakultasId);
                })->get();
                $currentRole = 'admin';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->whereHas('fakultas', fn($q) => $q->where('fakultas.id', $fakultasId))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();

                $periode = PeriodeSempro::where('id_fakultas', $fakultasId)->get();
            } else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $daftarSempro = $query->whereHas('mahasiswa.programStudi', function ($q) use ($programStudiId) {
                    $q->where('program_studi.id', $programStudiId);
                })->get();
                $currentRole = 'admin';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();

                $periode = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            } else if ($role === 'dosen') {
                // Optimasi 3: Lintas prodi jika dosen terdaftar sebagai pembimbing
                $daftarSempro = $query->where(function ($q) use ($user) {
                    $q->where('id_calon_dospem_1', $user->id)->orWhere('id_calon_dospem_2', $user->id);
                })->get();
                $currentRole = 'dosen';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->whereHas('programStudi', fn($q) => $q->where('program_studi.id', $programStudiId))
                    ->select('id', 'name')->get();

                $periode = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            } else if ($role === 'mahasiswa') {
                $daftarSempro = $query->where('id_mahasiswa', $user->id)->get();
                $currentRole = 'mahasiswa';

                $dosen = User::whereHas('roles', fn($q) => $q->where('nama', 'dosen'))
                    ->with(['programStudi' => fn($q) => $q->select('program_studi.id', 'nama')])
                    ->select('id', 'name')->get();

                $periode = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            }

            // Mapping role & hitung revisi
            $daftarSempro = $daftarSempro->reject(fn($item) => is_null($item->mahasiswa))
                ->each(function ($item) use ($currentRole) {
                    $item->role = $currentRole;
                });

            // Logika hasRevise sesuai kode asli Anda
            $targetStatus = in_array($currentRole, ['mahasiswa', 'dosen']) ? 'Revisi' : 'Revisi Diajukan';
            $reviseCount += $daftarSempro->where('status', $targetStatus)->count();

            $data = $data->merge($daftarSempro);
            $namaDosen = $namaDosen->merge($dosen);
            $waktuSempro = $waktuSempro->merge($periode);
        }

        // Finalisasi Variabel
        $hasRevise = $reviseCount > 0 ? (in_array('mahasiswa', $userRole) || in_array('dosen', $userRole) ? "Revisi" : "Revisi Diajukan") : null;

        $namaDosen = $namaDosen->unique('id')->map(function ($item) {
            $prodi = $item->programStudi->first()->nama ?? 'No Prodi';
            $item->nama_prodi_sort = $prodi;
            $item->display_name = $item->name . " - " . $prodi;
            return $item;
        })->sortBy([['nama_prodi_sort', 'asc'], ['name', 'asc']]);

        $data = $data->sortBy('role')->unique('id')->sortBy('created_at');
        $waktuSempro = $waktuSempro->sortByDesc('created_at')->unique('id');
        $kategoriTa = KategoriTA::orderBy('id', 'asc')->get();

        // Pastikan semua variabel asli dikirim kembali
        return view('pages/sempro/daftar_seminar_proposal', compact(
            'data',
            'userRole',
            'namaDosen',
            'userPivot',
            'waktuSempro',
            'hasRevise',
            'reviseCount',
            'kategoriTa'
        ));
    }


    public function show($id)
    {
        $daftarSempro = PendaftaranSempro::where('id', $id)
            ->with('mahasiswa', 'calonDospem1', 'calonDospem2', 'periodeSempro', 'kategoriTa')
            ->first();

        return response()->json($daftarSempro);
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();
        $waktuSemproLatest = collect();

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                // Get the name of the dosen in the same fakultas as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                }) // Ambil data program studi untuk ditampilkan nanti
                    ->with(['programStudi' => function ($q) {
                        $q->select('id_program_studi', 'nama'); // sesuaikan nama kolomnya
                    }])
                    ->select('id', 'name')->without(['pivot', 'roles'])->get();

                $waktuSempro = PeriodeSempro::where('id_fakultas', $fakultasId)
                    ->select('id', 'periode')
                    ->get();

                $user->listMahasiswa = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'mahasiswa'); // Checking for 'mahasiswa' role
                })->whereHas('fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Filter by the same program studi as the current user
                })->select('id', 'nim_nip_nidn', 'name')
                    ->without(['pivot', 'roles'])
                    ->orderBy('nim_nip_nidn', 'asc') // Sort by nim_nip_nidn in ascending order
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $waktuSemproLatest = $waktuSemproLatest->merge($waktuSempro);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                }) // Ambil data program studi untuk ditampilkan nanti
                    ->with(['programStudi' => function ($q) {
                        $q->select('id_program_studi', 'nama'); // sesuaikan nama kolomnya
                    }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)
                    ->select('id', 'periode')
                    ->get();

                $user->listMahasiswa = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'mahasiswa'); // Checking for 'mahasiswa' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'nim_nip_nidn', 'name')
                    ->without(['pivot', 'roles'])
                    ->orderBy('nim_nip_nidn', 'asc') // Sort by nim_nip_nidn in ascending order
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $waktuSemproLatest = $waktuSemproLatest->merge($waktuSempro);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                }) // Ambil data program studi untuk ditampilkan nanti
                    ->with(['programStudi' => function ($q) {
                        $q->select('id_program_studi', 'nama'); // sesuaikan nama kolomnya
                    }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)
                    // ->latest('periode')
                    ->select('id', 'periode')
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $waktuSemproLatest = $waktuSemproLatest->merge($waktuSempro);
            }
        }

        // 1. Unikkan
        $namaDosen = $namaDosen->unique('id');

        // 2. Map untuk buat atribut tampilan
        $namaDosen = $namaDosen->map(function ($item) {
            $prodi = $item->programStudi->first()->nama ?? 'No Prodi';
            $item->nama_prodi_sort = $prodi; // Simpan sementara untuk sorting
            $item->display_name = $item->name . " - " . $prodi;
            return $item;
        });

        // 3. Sort berdasarkan Prodi, lalu Nama
        $namaDosen = $namaDosen->sortBy([
            ['nama_prodi_sort', 'asc'],
            ['name', 'asc'],
        ]);

        $waktuSemproLatest = $waktuSemproLatest->sortByDesc('created_at');
        $kategoriTa = KategoriTA::orderBy('id', 'asc')->get();

        // return response()->json($user);

        // return response()->json([
        //     'user' => $user,
        //     'userRole' => $userRole,
        //     'userPivot' => $userPivot,
        //     'namaDosen' => $namaDosen,
        //     'waktuSemproLatest' => $waktuSemproLatest,
        // ]);

        return view('pages/sempro/daftar_seminar_proposal_add', compact('user', 'userRole', 'userPivot', 'namaDosen', 'waktuSemproLatest', 'kategoriTa'));
    }

    public function store(Request $request)
    {
        $rules = [
            'judulProposal' => 'required|string|max:191',
            'periodeSempro' => 'required|exists:periode_sempro,id',
            'calonPembimbing1' => 'required|exists:users,id',
            'fileTranskripNilai' => 'required|file|mimes:pdf|max:15360',
            'fileProposalSkripsi' => 'required|file|mimes:pdf|max:15360',
            'kategoriTugasAkhir' => 'required|exists:kategori_ta,id',
        ];

        $customMessages = [
            'judulProposal.required' => 'Judul Proposal wajib diisi',
            'judulProposal.string' => 'Judul Proposal harus berupa string',
            'judulProposal.max' => 'Judul Proposal maksimal 191 karakter',
            'kategoriTugasAkhir.required' => 'Kategori Tugas Akhir wajib diisi',
            'kategoriTugasAkhir.exists' => 'Kategori Tugas Akhir tidak valid',
            'periodeSempro.required' => 'Periode Sempro wajib diisi',
            'periodeSempro.exists' => 'Periode Sempro tidak valid',
            'calonPembimbing1.required' => 'Calon Pembimbing 1 wajib diisi',
            'calonPembimbing1.exists' => 'Calon Pembimbing 1 tidak valid',
            'fileTranskripNilai.required' => 'File Transkrip Nilai wajib diisi',
            'fileTranskripNilai.file' => 'File Transkrip Nilai yang dipilih tidak valid',
            'fileTranskripNilai.mimes' => 'File Transkrip Nilai harus dalam format PDF',
            'fileTranskripNilai.max' => 'Ukuran File Transkrip Nilai tidak boleh melebihi 15MB',
            'fileTranskripNilai.uploaded' => 'Ukuran File Transkrip Nilai tidak boleh melebihi 15MB',
            'fileProposalSkripsi.required' => 'File Proposal Skripsi wajib diisi',
            'fileProposalSkripsi.file' => 'File Proposal Skripsi yang dipilih tidak valid',
            'fileProposalSkripsi.mimes' => 'File Proposal Skripsi harus dalam format PDF',
            'fileProposalSkripsi.max' => 'Ukuran File Proposal Skripsi tidak boleh melebihi 15MB',
            'fileProposalSkripsi.uploaded' => 'Ukuran File Proposal Skripsi tidak boleh melebihi 15MB',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->roles->pluck('nama')->toArray();

        $periodeSempro = $request->periodeSempro;

        $fileProposal = $request->file('fileProposalSkripsi');
        $proposalName = time() . '_' . $fileProposal->getClientOriginalName();
        $proposalPath = 'public/files/daftar_sempro/';

        $fileTranskrip = $request->file('fileTranskripNilai');
        $transkripName = time() . '_' . $fileTranskrip->getClientOriginalName();
        $transkripPath = 'public/files/daftar_sempro/';

        DB::beginTransaction();

        try {
            if (array_intersect(['mahasiswa'], $userRole)) {
                PendaftaranSempro::create([
                    'id_mahasiswa' => $userId,
                    'judul_proposal' => $request->judulProposal,
                    'id_periode_sempro' => $request->periodeSempro,
                    'id_calon_dospem_1' => $request->calonPembimbing1,
                    'id_calon_dospem_2' => $request->calonPembimbing2,
                    'id_kategori_ta' => $request->kategoriTugasAkhir,
                    'file_proposal_skripsi' => $proposalName,
                    'file_transkrip_nilai' => $transkripName,
                ]);

                $daftarCount = PendaftaranSempro::where('id_mahasiswa', $userId)
                    ->where(function ($query) use ($periodeSempro) {
                        $query->where('id_periode_sempro', $periodeSempro);
                    })->count();
                if ($daftarCount > 1) {
                    DB::rollBack();
                    throw new \Exception('Sudah pernah mendaftar sebelumnya pada periode ini. Silahkan tunggu konfirmasi dari Kaprodi.');
                }
            } elseif (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) {
                PendaftaranSempro::create([
                    'id_mahasiswa' => $request->dataMahasiswaSelect,
                    'judul_proposal' => $request->judulProposal,
                    'id_periode_sempro' => $request->periodeSempro,
                    'id_calon_dospem_1' => $request->calonPembimbing1,
                    'id_calon_dospem_2' => $request->calonPembimbing2,
                    'id_kategori_ta' => $request->kategoriTugasAkhir,
                    'file_proposal_skripsi' => $proposalName,
                    'file_transkrip_nilai' => $transkripName,
                ]);

                $daftarCount = PendaftaranSempro::where('id_mahasiswa', $request->dataMahasiswaSelect)
                    ->where(function ($query) use ($periodeSempro) {
                        $query->where('id_periode_sempro', $periodeSempro);
                    })->count();
                if ($daftarCount > 1) {
                    DB::rollBack();
                    throw new \Exception('Sudah pernah mendaftar sebelumnya pada periode ini. Silahkan tunggu konfirmasi dari Kaprodi.');
                }
            }

            DB::commit();

            if ($fileProposal) {
                Storage::putFileAs($proposalPath, $fileProposal, $proposalName);
            }

            if ($fileTranskrip) {
                Storage::putFileAs($transkripPath, $fileTranskrip, $transkripName);
            }

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Berhasil mendaftar.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Gagal mendaftar. ');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $daftarSempro = PendaftaranSempro::findOrFail($id);

            $rules = [
                'proposalJudul' => 'string|max:191',
                'calonDospem1' => 'exists:users,id',
                'calonDospem2' => 'exists:users,id',
                // 'kategoriTugasAkhir' => 'required|exists:kategori_ta,id',
                'fileTranskripNilai' => ['file', 'mimes:pdf', 'max:15360', new FileNameValidator(160)],
                'fileProposalSkripsi' => ['file', 'mimes:pdf', 'max:15360', new FileNameValidator(160)],
            ];

            $customMessages = [
                'proposalJudul.string' => 'Judul Proposal harus berupa string',
                'proposalJudul.max' => 'Judul Proposal maksimal 191 karakter',
                'calonDospem1.exists' => 'Calon Pembimbing 1 tidak valid',
                'calonDospem2.exists' => 'Calon Pembimbing 1 tidak valid',
                'kategoriTugasAkhir.required' => 'Kategori Tugas Akhir wajib diisi',
                'kategoriTugasAkhir.exists' => 'Kategori Tugas Akhir tidak valid',

                'fileTranskripNilai.file' => 'File Transkrip Nilai yang dipilih tidak valid',
                'fileTranskripNilai.mimes' => 'File Transkrip Nilai harus dalam format PDF',
                'fileTranskripNilai.max' => 'Ukuran File Transkrip Nilai tidak boleh melebihi 15MB',

                'fileProposalSkripsi.file' => 'File Proposal Skripsi yang dipilih tidak valid',
                'fileProposalSkripsi.mimes' => 'File Proposal Skripsi harus dalam format PDF',
                'fileProposalSkripsi.max' => 'Ukuran File Proposal Skripsi tidak boleh melebihi 15MB',
            ];

            $validator = Validator::make($request->all(), $rules, $customMessages);
            $validator->validate();


            if ($validator->fails()) {
                return redirect()->route('daftar.seminar.proposal')
                    ->with('error', $validator); // Mengirim pesan error ke view
            }
            if ($daftarSempro->status !== 'Revisi') {
                return redirect()->route('daftar.seminar.proposal')->with('error', 'Data tidak dapat diubah karena status bukan "Revisi".');
            }

            $daftarSempro->update([
                'judul_proposal' => $request->proposalJudul ?? $daftarSempro->judul_proposal,
                'id_calon_dospem_1' => $request->calonDospem1 ?? $daftarSempro->id_calon_dospem_1,
                'id_calon_dospem_2' => $request->calonDospem2 ?? $daftarSempro->id_calon_dospem_2,
                'id_kategori_ta' => $request->kategoriTugasAkhir ?? $daftarSempro->id_kategori_ta,
            ]);

            // Jika fileTranskripNilai tidak diisi maka gunakan file yang sudah ada
            if (is_null($request->fileTranskripNilai)) {
                $daftarSempro->file_transkrip_nilai = $daftarSempro->file_transkrip_nilai;
                // Jika fileTranskripNilai diisi
            } else {
                $fileTranskrip = $request->file('fileTranskripNilai');
                if ($fileTranskrip) {
                    // rename file
                    $transkripName = time() . '_' . $fileTranskrip->getClientOriginalName();
                    // lokasi penyimpanan file
                    $transkripPath = 'public/files/daftar_sempro/';

                    // hapus file lama, dan simpan file baru
                    if (Storage::exists("public/files/daftar_sempro/{$daftarSempro->file_transkrip_nilai}")) {
                        Storage::disk('local')->delete('public/files/daftar_sempro/' . $daftarSempro->file_transkrip_nilai);
                    }
                    Storage::putFileAs($transkripPath, $fileTranskrip, $transkripName);

                    $daftarSempro->file_transkrip_nilai = $transkripName;
                }
            }

            // Jika fileProposalSkripsi tidak diisi maka gunakan file yang sudah ada
            if (is_null($request->fileProposalSkripsi)) {
                $daftarSempro->file_proposal_skripsi = $daftarSempro->file_proposal_skripsi;
                // Jika fileProposalSkripsi diisi
            } else {
                $fileProposal = $request->file('fileProposalSkripsi');

                if ($fileProposal) {
                    // rename file
                    $proposalName = time() . '_' . $fileProposal->getClientOriginalName();
                    // lokasi penyimpanan file
                    $proposalPath = 'public/files/daftar_sempro/';

                    // hapus file lama, dan simpan file baru
                    if (Storage::exists("public/files/daftar_sempro/{$daftarSempro->file_proposal_skripsi}")) {
                        Storage::disk('local')->delete('public/files/daftar_sempro/' . $daftarSempro->file_proposal_skripsi);
                    }
                    Storage::putFileAs($proposalPath, $fileProposal, $proposalName);

                    $daftarSempro->file_proposal_skripsi = $proposalName;
                }
            }

            if (Auth::user()->role == 'mahasiswa') {
                $daftarSempro->status = "Revisi Diajukan";
            }

            $daftarSempro->save();

            DB::commit();

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Data berhasil diubah.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Data gagal diubah.');
        }
    }

    public function approve($id)
    {
        $daftarSempro = PendaftaranSempro::with('mahasiswa', 'periodeSempro')->findOrFail($id);

        DB::beginTransaction();

        try {
            $daftarSempro->status = 'Diterima';
            $daftarSempro->alasan = '';
            $daftarSempro->save();

            DB::commit();

            Mail::to($daftarSempro->mahasiswa->email)
                ->send(new DaftarSemproAcceptNotification($daftarSempro));

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Status data berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Status data gagal diubah menjadi disetujui.');
        }
    }

    public function reject(Request $request, $id)
    {
        $daftarSempro = PendaftaranSempro::with('mahasiswa', 'periodeSempro')->findOrFail($id);

        DB::beginTransaction();

        try {
            $daftarSempro->status = 'Ditolak';
            $daftarSempro->alasan = $request->alasan ?? 'Tidak ada alasan';
            $daftarSempro->save();

            DB::commit();

            Mail::to($daftarSempro->mahasiswa->email)
                ->send(new DaftarSemproRejectNotification($daftarSempro));

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Status data ditolak.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Status data gagal diubah menjadi ditolak.');
        }
    }

    public function revise(Request $request, $id)
    {
        $daftarSempro = PendaftaranSempro::with('mahasiswa', 'periodeSempro')->findOrFail($id);

        DB::beginTransaction();

        try {
            $daftarSempro->status = 'Revisi';
            $daftarSempro->alasan = $request->alasan ?? 'Tidak ada alasan';
            $daftarSempro->save();

            DB::commit();

            // return response()->json($daftarSempro);

            Mail::to($daftarSempro->mahasiswa->email)
                ->send(new DaftarSemproReviseNotification($daftarSempro));

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Status data revisi.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Status data gagal diubah menjadi revisi.');
        }
    }

    public function delete($id)
    {
        $daftarSempro = PendaftaranSempro::findOrFail($id);

        DB::beginTransaction();

        try {
            $daftarSempro->delete();

            DB::commit();

            $fileTranskrip = $daftarSempro->file_transkrip_nilai;
            $fileProposal = $daftarSempro->file_proposal_skripsi;

            if (Storage::exists("public/files/daftar_sempro/{$fileTranskrip}")) {
                Storage::disk('local')->delete('public/files/daftar_sempro/' . $fileTranskrip);
            }
            if (Storage::exists("public/files/daftar_sempro/{$fileProposal}")) {
                Storage::disk('local')->delete('public/files/daftar_sempro/' . $fileProposal);
            }

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Data berhasil dihapus.');
        } catch (QueryException $e) {
            DB::rollback();

            if ($e->errorInfo[1] === 1451) {
                // Handle the foreign key constraint violation error
                return redirect()->route('daftar.seminar.proposal')->with('error', 'Data gagal dihapus karena data ini direferensikan oleh data lain.');
            }

            Log::error($e->getMessage());
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Data gagal dihapus.');
        }
    }

    public function viewPdf($id)
    {
        $pathDaftarSempro = storage_path('app/public/files/daftar_sempro/' . $id);
        $filename = preg_replace('/^\d+_/', '', $id);
        // $pendaftaranSempro = PendaftaranSempro::where('file_proposal_skripsi', $id)->with('mahasiswa')->first();

        if (file_exists($pathDaftarSempro)) {
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ];
            return response()->file($pathDaftarSempro, $headers);
        } else {
            abort(404);
        };
    }
}
