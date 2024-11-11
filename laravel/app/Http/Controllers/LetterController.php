<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Letter;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Notifications\SignatureCompletedNotification;
use App\Notifications\SignatureRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LetterController extends Controller
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

        $data = collect(); // Initialize an empty collection for merging

        foreach ($userPivot as $pivot) {
            $role = $pivot->role->nama;
            $programStudiId = $pivot->id_program_studi;
            $fakultasId = $pivot->id_fakultas;

            // If the user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, and Admin_Dekanat
            if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                $letters = Letter::whereHas('mahasiswa.fakultas', function($query) use ($fakultasId) {
                    $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                })->with('mahasiswa')->get();
                $letters->each(function ($item) {
                    $item->role = 'admin';
                });

                $pivot->fakultas->programStudi;

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
            // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
            else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                $letters = Letter::whereHas('mahasiswa.programStudi', function($query) use ($programStudiId) {
                    $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                })->with('mahasiswa')->get();
                $letters->each(function ($item) {
                    $item->role = 'admin';
                });

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
            // If user has the role of Mahasiswa
            else if (in_array($role, ['mahasiswa'])) {
                $letters = Letter::where('id_mahasiswa', $user->id)->get();
                $letters->each(function ($item) {
                    $item->role = 'mahasiswa';
                });

                // Merge the fetched letters with the existing data collection
                $data = $data->merge($letters);
            }
        }

        // Sort the data by 'created_at' ascending and make it unique by 'id'
        $data = $data->sortBy('created_at')->unique('id');

        // Return the view with the merged data
        return view('pages/persuratan/ttd_kaprodi', compact('data', 'userRole', 'userPivot'));
    }


    public function add()
    {
        $data = Auth::user();
        $userPivot = UsersPivot::where('id_user', $data->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        return view('pages/persuratan/ttd_kaprodi_add', compact('data', 'userPivot'));
    }

    public function store(Request $request)
    {
        // Validate the form data
        $rules = [
            'deskripsiSurat' => 'required|string|max:191',
            'fileSurat' => 'required|file|mimes:pdf|max:15360',
        ];

        $customMessages = [
            'fileSurat.required' => 'File wajib diisi.',
            'fileSurat.file' => 'File yang dipilih tidak valid.',
            'fileSurat.max' => 'Ukuran file tidak boleh melebihi 15MB.',
            'fileSurat.uploaded' => 'Ukuran file tidak boleh melebihi 15MB.',
            'fileSurat.mimes' => 'File harus dalam format PDF.',

            'deskripsiSurat.required' => 'Deskripsi wajib diisi.',
            'deskripsiSurat.string' => 'Deskripsi harus berupa string.',
            'deskripsiSurat.max' => 'Deskripsi maksimal 191 karakter.',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);
        $validator->validate();

        // Retrieve the form input values
        $description = $request->input('deskripsiSurat');
        $uploadedFile = $request->file('fileSurat');

        // Get the authenticated user's ID, name, and nim/nip/nidn
        $userId = Auth::user()->id;

        if ($uploadedFile) {
            $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
            $filePath = 'public/files/ttd_kaprodi/';
        }

        DB::beginTransaction();

        try {
            // Process the form data and save to the database
            Letter::create([
                'id_mahasiswa' => $userId,
                'deskripsi_surat' => $description,
                'file' => $fileName
            ]);

            // Commit the transaction
            DB::commit();

            if ($uploadedFile) {
                // Save the uploaded file
                Storage::putFileAs($filePath, $uploadedFile, $fileName);
            }

            // Redirect to a success page or perform any additional logic
            return redirect()->route('ttd.kaprodi')->with('message', 'Berhasil mengajukan surat, mohon tunggu konfirmasi dari Kaprodi.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('ttd.kaprodi')->with('error', 'Gagal mengajukan surat, mohon coba lagi.');
        };
    }

    public function downloadFile($encryptedId)
    {
        try {
            $id = (int) Crypt::decryptString($encryptedId);
            $letter = Letter::findOrFail($id);
            $filename = preg_replace('/^\d+_/', '', $letter->file);
        } catch (DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid Request');
        }

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];

        if (Storage::exists("public/files/ttd_kaprodi/{$letter->file}")) {
            $filePath = storage_path("app/public/files/ttd_kaprodi/{$letter->file}");

            return response()->file($filePath, $headers);
        } else {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }
    }

    public function rejectService(Request $request)
    {
        try {
            $id = $request->input('id');
            $letter = letter::findOrFail($id);

            // Validate the form data if necessary
            $validatedData = $request->validate([
                'taAlasan' => 'required'
            ]);

            // Retrieve the form input values
            $reason = $request->input('taAlasan');

            $letter->alasan_penolakan = $reason;
            $letter->status = "Ditolak";
            $letter->save();

            // send notification to mahasiswa
            // get the relationship
            $mahasiswa = $letter->mahasiswa;
            // get the letter data to send to mahasiswa via notification
            $mahasiswa->notify(new SignatureRejectedNotification($letter));

            // Redirect to a success page or perform any additional logic
            return redirect()->route('ttd.kaprodi')->with('message', 'Permohonan berhasil ditolak.');
        } catch (ValidationException $exception) {
            // Validation failed, handle the errors
            return redirect()->back()->with('error', 'Error');
        }
    }

    public function uploadNewFile(Request $request)
    {
        $id = $request->input('id');
        $letter = letter::findOrFail($id);
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
        if (Storage::exists("public/files/ttd_kaprodi/{$letter->file}")) {
            Storage::disk('local')->delete('public/files/ttd_kaprodi/'.$letter->file);
        }

        // Save the new uploaded file
        if ($uploadedFile) {
            $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
            $filePath = 'public/files/ttd_kaprodi/';
            Storage::putFileAs($filePath, $uploadedFile, $fileName);
            $letter->file = $fileName;
        }

        $letter->status = "Sudah di TTD";
        $letter->tanggal_ttd = Carbon::today();
        $letter->save();

        // send notification to mahasiswa
        $mahasiswa = $letter->mahasiswa;
        $mahasiswa->notify(new SignatureCompletedNotification($letter));

        // Redirect to a success page or perform any additional logic
        return redirect()->route('ttd.kaprodi')->with('message', 'File berhasil diunggah.');
    }

    public function delete($id)
    {
        $letter = letter::findOrFail($id);

        DB::beginTransaction();

        try {
            $letter->delete();

            DB::commit();

            if (Storage::exists("public/files/ttd_kaprodi/{$letter->file}") && $letter->file != null) {
                if (Storage::exists("public/files/ttd_kaprodi/{$letter->file}")) {
                    Storage::disk('local')->delete('public/files/ttd_kaprodi/'.$letter->file);
                }
            }

            return redirect()->route('ttd.kaprodi')->with('message', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('ttd.kaprodi')->with('error', 'Data gagal dihapus.');
        }
    }
}
