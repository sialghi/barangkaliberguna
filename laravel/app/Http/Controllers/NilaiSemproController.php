<?php

namespace App\Http\Controllers;

use App\Models\CatatanNilaiSempro;
use App\Models\NilaiSempro;
use App\Models\PendaftaranSempro;
use App\Models\User;
use App\Models\PeriodeSempro;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use DateTime;
use DateTimeZone;


use function PHPSTORM_META\type;

class NilaiSemproController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Eager Load roles dan programStudi di awal agar tidak query berkali-kali
        $userPivot = UsersPivot::where('id_user', $user->id)
            ->with(['role', 'programStudi', 'fakultas.programStudi'])
            ->orderBy('id_role', 'desc')
            ->get();

        $userRole = $user->roles->pluck('nama')->toArray();
        $data = collect();

        // Eager Load relasi NilaiSempro yang sering dipanggil di Blade
        // Ganti relasi di bawah sesuai dengan nama relasi yang ada di Model NilaiSempro Anda
        $eagerRelations = [
            'mahasiswa.programStudi',
            'mahasiswa.fakultas',
            'pembimbing1',
            'pembimbing2',
            'penguji1',
            'penguji2',
            'penguji3',
            'penguji4',
        ];

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            $query = NilaiSempro::with($eagerRelations);

            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $nilaiSempro = $query->whereHas('mahasiswa.fakultas', function ($q) use ($fakultasId) {
                    $q->where('fakultas.id', $fakultasId);
                })->get();
                $currentRole = 'admin';
            } else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $nilaiSempro = $query->whereHas('mahasiswa.programStudi', function ($q) use ($programStudiId) {
                    $q->where('program_studi.id', $programStudiId);
                })->get();
                $currentRole = 'admin';
            } else if ($role === 'dosen') {
                $nilaiSempro = $query->where(function ($q) use ($user) {
                    $q->where('id_penguji_1', $user->id)->orWhere('id_penguji_2', $user->id)
                        ->orWhere('id_penguji_3', $user->id)->orWhere('id_penguji_4', $user->id)
                        ->orWhere('id_pembimbing_1', $user->id)->orWhere('id_pembimbing_2', $user->id);
                })->get();
                $currentRole = 'dosen';
            } else if ($role === 'mahasiswa') {
                $nilaiSempro = $query->where('id_mahasiswa', $user->id)->get();
                $currentRole = 'mahasiswa';
            }

            // Mapping role dan bersihkan data null mahasiswa dalam satu langkah (Collection power)
            $nilaiSempro = $nilaiSempro->filter(fn($item) => $item->mahasiswa !== null)
                ->each(function ($item) use ($currentRole) {
                    $item->role = $currentRole;
                });

            $data = $data->merge($nilaiSempro);
        }

        // Ambil daftar dosen di luar loop (jika kriteria dosen sama untuk semua role)
        // Cukup ambil sekali saja untuk efisiensi
        $namaDosen = User::whereHas('roles', function ($q) {
            $q->where('nama', 'dosen');
        })
            ->select('id', 'name')
            ->without(['pivot', 'roles'])
            ->orderBy('name', 'asc')
            ->get();

        $data = $data->unique('id')->sortBy('created_at');

        return view('pages/sempro/nilai_seminar_proposal', compact('data', 'userRole', 'userPivot', 'namaDosen'));
    }

    public function show($id)
    {
        $nilaiSempro = NilaiSempro::with('mahasiswa', 'penguji1', 'penguji2', 'penguji3', 'penguji4', 'pembimbing1', 'pembimbing2')->findOrFail($id);
        $pendaftaranSempro = PendaftaranSempro::where('id_mahasiswa', $nilaiSempro->id_mahasiswa)
            ->where('id_periode_sempro', $nilaiSempro->id_periode_sempro)
            ->where('status', 'Diterima')
            ->with('mahasiswa', 'calonDospem1', 'calonDospem2', 'periodeSempro')
            ->first();
        $namaDosen = User::whereHas('roles', function ($query) {
            $query->where('nama', 'dosen');
        })->pluck('name', 'id');

        return response()->json([
            'nilaiSempro' => $nilaiSempro,
            'pendaftaranSempro' => $pendaftaranSempro,
            'namaDosen' => $namaDosen
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
                })->with(['programStudi' => function ($q) {
                    $q->select('id_program_studi', 'nama'); // sesuaikan nama kolomnya
                }])
                    ->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $nilaiSempro = NilaiSempro::with('pendaftaranSempro')->whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->pluck('id_pendaftaran_sempro')->toArray();
                $pendaftarSempro = PendaftaranSempro::whereHas('mahasiswa.fakultas', function ($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->where('status', 'Diterima')
                    ->whereNotIn('id', $nilaiSempro)
                    ->with('mahasiswa', 'calonDospem1', 'calonDospem2', 'periodeSempro')
                    ->get();

                $periode = PeriodeSempro::where('id_fakultas', $fakultasId)->get();

                $pivot->fakultas->programStudi;
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $dosen = User::whereHas('roles', function ($query) {
                    $query->where('nama', 'dosen'); // Checking for 'dosen' role
                })->with(['programStudi' => function ($q) {
                    $q->select('id_program_studi', 'nama'); // sesuaikan nama kolomnya
                }])->select('id', 'name')->without(['pivot', 'roles'])->get();

                $namaDosen = $namaDosen->merge($dosen);

                $nilaiSempro = NilaiSempro::with('pendaftaranSempro')->whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->pluck('id_pendaftaran_sempro')->toArray();
                $pendaftarSempro = PendaftaranSempro::whereHas('mahasiswa.programStudi', function ($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->where('status', 'Diterima')
                    ->whereNotIn('id', $nilaiSempro)
                    ->with('mahasiswa', 'calonDospem1', 'calonDospem2', 'periodeSempro')
                    ->get();

                $periode = PeriodeSempro::where('id_program_studi', $programStudiId)->get();
            }
        }

        $dropDownPendaftar = $pendaftarSempro->map(function ($pendaftar) {
            return (object)[
                'id' => $pendaftar->id,
                'periode' => $pendaftar->periodeSempro->periode,
                'nama' => $pendaftar->mahasiswa->name,
                'nim' => $pendaftar->mahasiswa->nim_nip_nidn
            ];
        });
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

        // return response()->json($pendaftarSempro);
        return view('pages/sempro/nilai_seminar_proposal_add', compact('namaDosen', 'userPivot', 'periode', 'pendaftarSempro', 'dropDownPendaftar'));
    }

    public function store(Request $request)
    {
        $rules = [
            'pendaftarSemproSelect' => 'required',
            'dosenPenguji1' => 'required',
        ];

        $customMessages = [
            'pendaftarSemproSelect.required' => 'Pendaftar Seminar Proposal harus diisi.',
            'dosenPenguji1.required' => 'Dosen Penguji 1 harus diisi.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        DB::beginTransaction();

        try {
            $nilaiSempro = NilaiSempro::create([
                'id_pendaftaran_sempro' => $request->pendaftarSemproSelect,
                'id_mahasiswa' => $request->namaMahasiswaId,
                'id_penguji_1' => $request->dosenPenguji1,
                'id_penguji_2' => $request->dosenPenguji2,
                'id_penguji_3' => $request->dosenPenguji3,
                'id_penguji_4' => $request->dosenPenguji4,
                'id_pembimbing_1' => $request->dosenPembimbing1,
                'id_pembimbing_2' => $request->dosenPembimbing2,
                'judul_proposal' => $request->judulProposalHidden,
                'id_periode_sempro' => $request->periodeProposalId,
                'status' => 'Sedang Diproses',
            ]);

            $nilaiSemproId = $nilaiSempro->id;

            $nilaiSemproDiproses = NilaiSempro::where('id_mahasiswa', $request->namaMahasiswaId)
                ->where('id_periode_sempro', $request->periodeProposalId)
                ->count();

            if ($nilaiSemproDiproses > 1) {
                // return redirect()->route('daftar_seminar_proposal')->with('error', 'Anda masih memiliki pendaftaran yang sedang diproses pada periode yang sama. Silahkan tunggu konfirmasi dari Kaprodi.');
                throw new \Exception('Data pendaftaran sudah pernah diinput, silakan edit data tersebut.');
            }

            DB::commit();

            $this->storeCatatanSempro($request, $nilaiSemproId);

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Berhasil diinput.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Gagal diinput.');
        }
    }

    public function storeCatatanSempro(Request $request, $nilaiSemproId)
    {
        DB::beginTransaction();

        try {
            $pengujiIds = array_filter([
                $request->dosenPenguji1,
                $request->dosenPenguji2,
                $request->dosenPenguji3,
                $request->dosenPenguji4
            ]);

            foreach ($pengujiIds as $pengujiId) {
                CatatanNilaiSempro::create([
                    'id_nilai_sempro' => $nilaiSemproId,
                    'id_penguji' => $pengujiId,
                ]);
            }

            DB::commit();
            return redirect()->route('nilai.seminar.proposal')->with('message', 'Catatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Gagal diinput.');
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $nilaiSempro = NilaiSempro::findOrFail($request->id);

            $changedPengujiIds = [];
            for ($i = 1; $i <= 4; $i++) {
                $newPengujiId = $request->{'dosenPenguji' . $i};
                if ($request->filled('dosenPenguji' . $i) && $newPengujiId != $nilaiSempro->{'id_penguji_' . $i}) {
                    $nilaiSempro->{'id_penguji_' . $i} = $newPengujiId;
                    $changedPengujiIds[] = $newPengujiId;
                }
            }

            for ($i = 1; $i <= 2; $i++) {
                if ($request->filled('dosenPembimbing' . $i)) {
                    $nilaiSempro->{'id_pembimbing_' . $i} = $request->{'dosenPembimbing' . $i};
                }
            }

            $nilaiSempro->save();

            foreach ($changedPengujiIds as $pengujiId) {
                CatatanNilaiSempro::firstOrCreate([
                    'id_nilai_sempro' => $nilaiSempro->id,
                    'id_penguji' => $pengujiId,
                ]);
            }

            DB::commit();

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Nilai Seminar Proposal berhasil diupdate');
        } catch (QueryException $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Gagal melakukan update');
        }
    }

    public function generateDocx($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $nilaiSempro = NilaiSempro::with('mahasiswa', 'pembimbing1', 'pembimbing2', 'penguji1', 'penguji2', 'penguji3', 'penguji4', 'periodeSempro', 'catatan')->findOrFail($id);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }



        if ($nilaiSempro->status != 'Diterima') {
            return redirect()->back()->with('error', 'Data belum diterima');
        }

        // Enable output escaping
        Settings::setOutputEscapingEnabled(true);

        // Load the .docx template
        $template = new TemplateProcessor(storage_path("app/public/files/nilai_sempro/template/Nilai_Seminar_Proposal_Template_Modified.docx"));

        $template->setValue('nama_mahasiswa', $nilaiSempro->mahasiswa->name);
        $template->setValue('nim', $nilaiSempro->mahasiswa->nim_nip_nidn);
        $template->setValue('judul', $nilaiSempro->judul_proposal);

        if ($nilaiSempro->periodeSempro->tanggal) {
            $date = new DateTime($nilaiSempro->periodeSempro->tanggal, new DateTimeZone('Asia/Jakarta'));
            $nama_bulan_id = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
            $dateFormatted = $date->format('d n Y');
            $dateFormatted = explode(" ", $dateFormatted);
            $tanggalSempro = $dateFormatted[0] . " " . $nama_bulan_id[$dateFormatted[1]] . " " . $dateFormatted[2];
        } else {
            $englishToIndonesianMonths = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember',
            ];
            $englishDate = $nilaiSempro->periodeSempro->periode;
            function translateMonthToIndonesian($englishDate, $mapping)
            {
                foreach ($mapping as $english => $indonesian) {
                    if (strpos($englishDate, $english) !== false) {
                        return str_replace($english, $indonesian, $englishDate);
                    }
                }
                return $englishDate; // Return original if no translation found
            }
            $tanggalSempro = translateMonthToIndonesian($englishDate, $englishToIndonesianMonths);
        }

        $template->setValue('tanggal', $tanggalSempro);
        $template->setValue('tanggal_sempro', $tanggalSempro);

        // Catatan Nilai Sempro
        $catatanJudul = '';
        $catatanLatarBelakang = '';
        $catatanIdentifikasiMasalah = '';
        $catatanPembatasanMasalah = '';
        $catatanPerumusanMasalah = '';
        $catatanPenelitianTerdahulu = '';
        $catatanMetodologiPenelitian = '';
        $catatanReferensi = '';

        foreach ($nilaiSempro->catatan as $catatan) {
            if ($catatan->judul) {
                $catatanJudul .= $catatan->judul . ". ";
            }
            if ($catatan->latar_belakang) {
                $catatanLatarBelakang .= $catatan->latar_belakang . ". ";
            }
            if ($catatan->identifikasi_masalah) {
                $catatanIdentifikasiMasalah .= $catatan->identifikasi_masalah . ". ";
            }
            if ($catatan->pembatasan_masalah) {
                $catatanPembatasanMasalah .= $catatan->pembatasan_masalah . ". ";
            }
            if ($catatan->perumusan_masalah) {
                $catatanPerumusanMasalah .= $catatan->perumusan_masalah . ". ";
            }
            if ($catatan->penelitian_terdahulu) {
                $catatanPenelitianTerdahulu .= $catatan->penelitian_terdahulu . ". ";
            }
            if ($catatan->metodologi_penelitian) {
                $catatanMetodologiPenelitian .= $catatan->metodologi_penelitian . ". ";
            }
            if ($catatan->referensi) {
                $catatanReferensi .= $catatan->referensi . ". ";
            }
        }

        $template->setValue('catatan_judul', $catatanJudul);
        $template->setValue('catatan_latar_belakang', $catatanLatarBelakang);
        $template->setValue('catatan_identifikasi_masalah', $catatanIdentifikasiMasalah);
        $template->setValue('catatan_pembatasan_masalah', $catatanPembatasanMasalah);
        $template->setValue('catatan_perumusan_masalah', $catatanPerumusanMasalah);
        $template->setValue('catatan_penelitian_terdahulu', $catatanPenelitianTerdahulu);
        $template->setValue('catatan_metodologi_penelitian', $catatanMetodologiPenelitian);
        $template->setValue('catatan_referensi', $catatanReferensi);

        // Penguji 1
        $template->setValue('penguji_1_nama', $nilaiSempro->penguji1->name);
        $template->setValue('penguji_1_nip', $nilaiSempro->penguji1->nim_nip_nidn);
        if ($nilaiSempro->penguji1->ttd && Storage::exists($nilaiSempro->penguji1->ttd)) {
            $ttdPenguji1 = Storage::path($nilaiSempro->penguji1->ttd);
            $template->setImageValue('penguji_1_ttd', array('path' => $ttdPenguji1, 'width' => 120, 'height' => 120, 'ratio' => true));
        } else {
            $template->setValue('penguji_1_ttd', "Tanda Tangan " . $nilaiSempro->penguji1->name);
        }

        // Penguji 2
        $template->setValue('penguji_2_nama', $nilaiSempro->penguji2->name);
        $template->setValue('penguji_2_nip', $nilaiSempro->penguji2->nim_nip_nidn);
        if ($nilaiSempro->penguji2->ttd && Storage::exists($nilaiSempro->penguji2->ttd)) {
            $ttdPenguji2 = Storage::path($nilaiSempro->penguji2->ttd);
            $template->setImageValue('penguji_2_ttd', array('path' => $ttdPenguji2, 'width' => 120, 'height' => 120, 'ratio' => true));
        } else {
            $template->setValue('penguji_2_ttd', "Tanda Tangan " . $nilaiSempro->penguji2->name);
        }

        // Penguji 3
        $template->setValue('penguji_3_nama', $nilaiSempro->penguji3->name);
        $template->setValue('penguji_3_nip', $nilaiSempro->penguji3->nim_nip_nidn);
        if ($nilaiSempro->penguji3->ttd && Storage::exists($nilaiSempro->penguji3->ttd)) {
            $ttdPenguji3 = Storage::path($nilaiSempro->penguji3->ttd);
            $template->setImageValue('penguji_3_ttd', array('path' => $ttdPenguji3, 'width' => 120, 'height' => 120, 'ratio' => true));
        } else {
            $template->setValue('penguji_3_ttd', "Tanda Tangan " . $nilaiSempro->penguji3->name);
        }

        // Penguji 4
        $template->setValue('penguji_4_nama', $nilaiSempro->penguji4->name);
        $template->setValue('penguji_4_nip', $nilaiSempro->penguji4->nim_nip_nidn);
        if ($nilaiSempro->penguji4->ttd && Storage::exists($nilaiSempro->penguji4->ttd)) {
            $ttdPenguji4 = Storage::path($nilaiSempro->penguji4->ttd);
            $template->setImageValue('penguji_4_ttd', array('path' => $ttdPenguji4, 'width' => 120, 'height' => 120, 'ratio' => true));
        } else {
            $template->setValue('penguji_4_ttd', "Tanda Tangan " . $nilaiSempro->penguji4->name);
        }

        $directory = 'app/public/files/nilai_sempro/';
        $outputFileNameDocx = 'NilaiSeminarProposal_' . preg_replace('/\s+/', '', $nilaiSempro->mahasiswa->name) . '_' . $nilaiSempro->mahasiswa->nim_nip_nidn . '.docx';
        $docxFilePath = storage_path($directory . $outputFileNameDocx);
        $template->saveAs($docxFilePath);

        return response()->download($docxFilePath)->deleteFileAfterSend();
    }

    public function approve($id)
    {
        $nilaiSempro = NilaiSempro::findOrFail($id);

        DB::beginTransaction();

        try {
            $nilaiSempro->status = 'Diterima';
            $nilaiSempro->save();

            DB::commit();

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Status data menjadi diterima.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Status data gagal diubah menjadi diterima.');
        }
    }

    public function reject($id)
    {
        $nilaiSempro = NilaiSempro::findOrFail($id);

        DB::beginTransaction();

        try {
            $nilaiSempro->status = 'Ditolak';
            $nilaiSempro->save();

            DB::commit();

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Status data menjadi ditolak.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Status data gagal diubah menjadi ditolak.');
        }
    }

    public function revise($id)
    {
        $nilaiSempro = NilaiSempro::findOrFail($id);

        DB::beginTransaction();

        try {
            $nilaiSempro->status = 'Revisi';
            $nilaiSempro->save();

            DB::commit();

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Status data menjadi revisi.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Status data gagal diubah menjadi revisi.');
        }
    }

    public function delete($id)
    {
        $nilaiSempro = NilaiSempro::findOrFail($id);

        DB::beginTransaction();

        try {
            $nilaiSempro->delete();

            DB::commit();


            return redirect()->route('nilai.seminar.proposal')->with('message', 'Data berhasil dihapus.');
        } catch (QueryException $e) {
            DB::rollback();

            if ($e->errorInfo[1] === 1451) {
                // Handle the foreign key constraint violation error
                return redirect()->route('nilai.seminar.proposal')->with('error', 'Data gagal dihapus karena data ini direferensikan oleh data lain.');
            }

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Data gagal dihapus.');
        }
    }
}
