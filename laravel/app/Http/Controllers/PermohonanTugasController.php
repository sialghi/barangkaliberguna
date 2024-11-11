<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PermohonanTugas;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Notifications\SignatureCompletedNotification;
use App\Notifications\SignatureRejectedNotification;

class PermohonanTugasController extends Controller
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
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = collect(); // Initialize the data collection to store merged data

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If the user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $letters = PermohonanTugas::whereHas('dosen.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with(['dosen' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->get();
                $letters->each(function ($item) {
                    $item->role = 'admin';
                });

                $pivot->fakultas->programStudi;

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $letters = PermohonanTugas::whereHas('dosen.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with(['dosen' => function ($query) {
                    $query->select('id', 'name', 'nim_nip_nidn'); // Specify columns to fetch
                }])->get();
                $letters->each(function ($item) {
                    $item->role = 'admin';
                });

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
            // If user has the role of Dosen
            else if (in_array($role, ['dosen'])) {
                $letters = PermohonanTugas::where('id_user', $user->id)->get();
                $letters->each(function ($item) {
                    $item->role = 'dosen';
                });

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
        }

        // Sort the data by 'created_at' ascending and make it unique by 'id'
        $data = $data->sortBy('created_at')->unique('id');

        // Return the view with the merged data
        return view('pages/persuratan/permohonan_tugas', compact('data', 'userRole', 'userPivot'));
    }


    public function add()
    {
        $data = Auth::user();
        $userPivot = UsersPivot::where('id_user', $data->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        return view('pages/persuratan/permohonan_tugas_add', compact('data', 'userPivot'));
    }

    public function store(Request $request)
    {
        // Validate the form data
        $rules = [
            'taDesc' => 'required|string|max:191',
            'uploadSurat' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'uploadSurat.required' => 'File wajib diisi.',
            'uploadSurat.file' => 'File yang dipilih tidak valid.',
            'uploadSurat.max' => 'Ukuran file tidak boleh melebihi 15MB.',
            'uploadSurat.uploaded' => 'Ukuran file tidak boleh melebihi 15MB.',
            'uploadSurat.mimes' => 'File harus dalam format PDF.',

            'taDesc.required' => 'Deskripsi wajib diisi.',
            'taDesc.string' => 'Deskripsi harus berupa string.',
            'taDesc.max' => 'Deskripsi maksimal 191 karakter.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        // Retrieve the form input values
        $description = $request->input('taDesc');
        $uploadedFile = $request->file('uploadSurat');

        // Get the authenticated user's ID, name, and nim/nip/nidn
        $userId = Auth::user()->id;
        $userName = Auth::user()->name;
        $nim_nip_nidn = Auth::user()->nim_nip_nidn;

        $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
        $filePath = 'public/files/permohonan_surat/';

        DB::beginTransaction();

        try {
            PermohonanTugas::create([
                'id_user' => $userId,
                'deskripsi_surat' => $description,
                'file_1' => $fileName
            ]);

            DB::commit();

            if ($uploadedFile) {
                Storage::putFileAs($filePath, $uploadedFile, $fileName);
            }

            // Redirect to a success page or perform any additional logic
            return redirect()->route('surat.tugas')->with('message', 'Ajukan surat berhasil, mohon tunggu konfirmasi dari Kaprodi.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('surat.tugas')->with('error', 'Ajukan surat gagal, mohon coba lagi.');
        };
    }

    public function downloadInputFile($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $letter = PermohonanTugas::findOrFail($id);
            $filename = preg_replace('/^\d+_/', '', $letter->file_1);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];

        if(Storage::exists("public/files/permohonan_surat/{$letter->file_1}") && $letter->file_1 != null) {
            $filePath = storage_path("app/public/files/permohonan_surat/{$letter->file_1}");
            return response()->file($filePath, $headers);
        } else {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }
    }

    public function downloadOutputFile($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $letter = PermohonanTugas::findOrFail($id);
            $filename = preg_replace('/^\d+_/', '', $letter->file_2);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];

        // Check if the file exists
        if (Storage::exists("public/files/permohonan_surat/{$letter->file_2}") && $letter->file_2 != null) {
            $filePath = storage_path("app/public/files/permohonan_surat/{$letter->file_2}");
            // Generate the appropriate file response
            return response()->file($filePath, $headers);
        } else {
            // If the file does not exist, redirect or show an error message
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }
    }

    public function rejectService(Request $request)
    {
        try {
            $id = $request->input('id');
            $letter = PermohonanTugas::findOrFail($id);

            // Validate the form data if necessary
            $validatedData = $request->validate([
                'taAlasan' => 'required'
            ]);

            // Retrieve the form input values
            $reason = $request->input('taAlasan');

            $letter->alasan_penolakan = $reason;
            $letter->status = "Ditolak";
            $letter->save();

            // send notification to dosen
            // get the relationship
            $dosen = $letter->dosen;
            // get the letter data to send to dosen via notification
            $dosen->notify(new SignatureRejectedNotification($letter));

            // Redirect to a success page or perform any additional logic
            return redirect()->route('surat.tugas')->with('message', 'Permohonan berhasil ditolak.');
        } catch (ValidationException $exception) {
            // Validation failed, handle the errors
            return redirect()->back()->with('error', 'Error');
        }
    }

    public function uploadNewFile(Request $request)
    {
        $id = $request->input('id');
        $letter = PermohonanTugas::findOrFail($id);
        $validatedData = [
            'uploadFileBaru' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'uploadFileBaru.required' => 'The file field is required.',
            'uploadFileBaru.file' => 'The selected file is invalid.',
            'uploadFileBaru.max' => 'The file size must not exceed 15MB.',
            'uploadFileBaru.mimes' => 'The file must be in PDF format.',
        ];

        $validator = Validator::make($request->all(), $validatedData, $customMessages);
        $validator->validate();

        // Validation passed, continue with file upload

        $uploadedFile = $request->file('uploadFileBaru');

        // Check if the file exists
        if (Storage::exists("public/files/permohonan_surat/{$letter->file_1}") && $letter->file_1 != null) {
            // Save the new uploaded file
            if ($uploadedFile) {
                $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
                $filePath = 'public/files/permohonan_surat/';
                Storage::putFileAs($filePath, $uploadedFile, $fileName);
                $letter->file_2 = $fileName;
            }
        }

        $letter->status = "Diterima";
        $letter->tanggal_ttd = Carbon::today();
        $letter->save();

        // send notification to dosen
        $dosen = $letter->dosen;
        $dosen->notify(new SignatureCompletedNotification($letter));

        // Redirect to a success page or perform any additional logic
        return redirect()->route('surat.tugas')->with('message', 'File berhasil diunggah.');
    }

    public function delete($id)
    {
        $letter = PermohonanTugas::findOrFail($id);

        DB::beginTransaction();
        try {
            $letter->delete();

            DB::commit();

            if (Storage::exists("public/files/permohonan_surat/{$letter->file_1}") && $letter->file_1 != null) {
                if (Storage::exists("public/files/permohonan_surat/{$letter->file_1}")) {
                    Storage::disk('local')->delete('public/files/permohonan_surat/'.$letter->file_1);
                }
            }

            if (Storage::exists("public/files/permohonan_surat/{$letter->file_2}") && $letter->file_2 != null) {
                if (Storage::exists("public/files/permohonan_surat/{$letter->file_2}")) {
                    Storage::disk('local')->delete('public/files/permohonan_surat/'.$letter->file_2);
                }
            }

            return redirect()->route('surat.tugas')->with('message', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('surat.tugas')->with('error', 'Data gagal dihapus.');
        }
    }
}
