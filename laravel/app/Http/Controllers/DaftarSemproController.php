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
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $namaDosen = collect();
        $data = collect(); // Initialize the main collection
        $waktuSempro = collect(); // Initialize waktuSempro collection

        $hasRevise = ''; // Initialize hasRevise as empty
        $reviseCount = 0; // Initialize reviseCount

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $daftarSempro = PendaftaranSempro::whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $daftarSempro->each(function ($item) {
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

                $waktuSempro = PeriodeSempro::where('id_fakultas', $fakultasId)->get();
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $daftarSempro = PendaftaranSempro::whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->get()->reject(function ($item) {
                    return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                });
                $daftarSempro->each(function ($item) {
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

                // Merge waktuSempro for the current program studi
                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $daftarSempro = PendaftaranSempro::where('id_calon_dospem_1', $user->id)
                    ->orWhere('id_calon_dospem_2', $user->id)
                    ->get()->reject(function ($item) {
                        return is_null($item->mahasiswa); // Remove items where mahasiswa is null
                    });
                $daftarSempro->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Dosen does not need revisi count
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

                // Merge waktuSempro for the current program studi
                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $daftarSempro = PendaftaranSempro::where('id_mahasiswa', $user->id)->get();
                $daftarSempro->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Calculate revisi count for Mahasiswa
                $reviseCount += $daftarSempro->filter(function ($row) {
                    return $row->status === 'Revisi';
                })->count();
                $hasRevise = $reviseCount > 0 ? "Revisi" : null;

                // Get the name of the dosen in the same program studi as the current user
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                // Merge waktuSempro for the current program studi
                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            }

            // Merge the fetched daftarSempro with the existing data collection
            $namaDosen = $namaDosen->merge($dosen);
            $data = $data->merge($daftarSempro);
            $waktuSempro = $waktuSempro->merge($waktuSempro);
        }

        // Sort data by 'role' to prioritize higher role and make it unique by 'id'
        $namaDosen = $namaDosen->sortBy('name')->unique('id');
        $data = $data->sortBy('role')->unique('id');
        $data = $data->sortBy('created_at');
        $waktuSempro = $waktuSempro->sortByDesc('created_at')->unique('id');
        $kategoriTa = KategoriTA::orderBy('id', 'asc')->get();

        // return response()->json($data);
        // Return to the view with merged data
        return view('pages/sempro/daftar_seminar_proposal', compact('data', 'userRole', 'namaDosen', 'userPivot', 'waktuSempro', 'hasRevise', 'reviseCount', 'kategoriTa'));
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
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

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
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

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
                })->whereHas('programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Filter by the same program studi as the current user
                })->select('id', 'name')->without(['pivot', 'roles'])->get();

                $waktuSempro = PeriodeSempro::where('id_program_studi', $programStudiId)
                    // ->latest('periode')
                    ->select('id', 'periode')
                    ->get();

                $namaDosen = $namaDosen->merge($dosen);
                $waktuSemproLatest = $waktuSemproLatest->merge($waktuSempro);
            }
        }

        $namaDosen = $namaDosen->sortBy('name')->unique('id');
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
                'kategoriTugasAkhir' => 'required|exists:kategori_ta,id',
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
