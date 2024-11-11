<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $userRole = $user->roles->pluck('nama')->toArray();
            $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

            $data = collect();

            foreach ($userPivot as $pivot) {
                $role = $pivot->role->nama;
                $programStudiId = $pivot->id_program_studi;
                $fakultasId = $pivot->id_fakultas;

                // If user has the role of Dekan, Wadek_Satu, Wadek_Dua, Wadek_Tiga, or Admin_Dekanat
                if (in_array($role, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'])) {
                    $userData = User::whereHas('fakultas', function($query) use ($fakultasId) {
                        $query->where('fakultas.id', $fakultasId); // Adjust based on your column name
                    })->with('roles', 'programStudi', 'fakultas')->withTrashed()->get();

                    $pivot->fakultas->programStudi;

                    // Merge the fetched user with the existing data collection
                    $data = $data->merge($userData);
                }
                // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
                else if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                    $userData = User::whereHas('programStudi', function($query) use ($programStudiId) {
                        $query->where('program_studi.id', $programStudiId); // Adjust based on your column name
                    })->with('roles', 'programStudi', 'fakultas')->withTrashed()->get();

                    // Merge the fetched user with the existing data collection
                    $data = $data->merge($userData);
                }
            }

            // Sort data by 'role' to prioritize higher role and make it unique by 'id'
            $data = $data->sortBy('created_at');

            // return response()->json([
            //     'status' => 'success',
            //     'data' => $data,
            // ]);

            return view('pages.users.user_management', compact('user', 'userRole', 'userPivot', 'data'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuka halaman.'. $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = User::with('pivot.programStudi', 'pivot.role', 'pivot.fakultas')->find($id);
            $prodi = ProgramStudi::all();
            $fakultas = Fakultas::all();
            $role = Role::all();

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'prodi' => $prodi,
                'fakultas' => $fakultas,
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuka detail.');
        }
    }

    public function add()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $roles = Role::all();
        $programStudi = ProgramStudi::all();
        $fakultas = Fakultas::all();

        return view('pages.users.user_management_add', compact('user', 'userRole', 'userPivot', 'roles', 'programStudi', 'fakultas'));
    }

    public function store(Request $request)
    {
        $rules = [
            'namaUser' => ['required', 'string', 'max:255'],
            'emailUser' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email'],
            'roleUser' => ['required', 'string'],
            'nimNipNidnUser' => 'nullable|string|regex:/^[0-9]{6,18}$/',
            'noHp' => 'nullable|string|regex:/^62\d{9,13}$/|max:15',
            'ttdUser' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'passwordUser' => 'required|string|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)(?!.*\\s).{8,}$/',
        ];

        $customMessages = [
            'namaUser.required' => 'Nama harus diisi',
            'namaUser.string' => 'Nama harus berupa huruf',
            'namaUser.max' => 'Nama maksimal 255 karakter',
            'emailUser.required' => 'Email harus diisi',
            'emailUser.string' => 'Email harus berupa huruf',
            'emailUser.lowercase' => 'Email harus berupa huruf kecil',
            'emailUser.email' => 'Email harus valid',
            'emailUser.max' => 'Email maksimal 255 karakter',
            'emailUser.unique' => 'Email sudah terdaftar',
            'roleUser.required' => 'Role harus diisi',
            'roleUser.string' => 'Role harus berupa huruf',
            'nimNipNidnUser.string' => 'NIM/NIP/NIDN harus berupa huruf',
            'nimNipNidnUser.regex' => 'NIM/NIP/NIDN harus berupa angka dengan minimal 6 digit dan maksimal 18 digit',
            'noHp.string' => 'No HP harus berupa huruf',
            'noHp.regex' => 'No HP harus dimulai dengan 62xxxxxxx dengan minimal 9 digit dan maksimal 13 digit',
            'noHp.max' => 'No HP maksimal 15 karakter',
            'ttdUser.image' => 'TTD harus berupa gambar',
            'ttdUser.mimes' => 'TTD harus berformat jpeg, jpg, atau png',
            'ttdUser.max' => 'TTD maksimal 2048 KB',
            'passwordUser.required' => 'Password harus diisi',
            'passwordUser.string' => 'Password harus berupa huruf',
            'passwordUser.regex' => 'Password harus mengandung setidaknya satu angka, satu huruf kecil, satu huruf besar, satu simbol, dan minimal 8 karakter',
        ];

        $validator = Validator::make(request()->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Gagal menambahkan user '. $validator->errors()->first());
        }

        $validator->validate();

        if ($request->roleUser && !($request->prodiUser || $request->fakultasUser)) {
            return redirect()->back()->with('error', 'Gagal menambahkan user. Program Studi atau Fakultas harus diisi.');
        }

        DB::beginTransaction();
        try {
            $fileNameDatabase = '';
            if ($request->hasFile('ttdUser')) {
                $file = $request->file('ttdUser');
                $fileName = 'ttd_'.time().$file->getClientOriginalName();
                $fileNameDatabase = 'public/images/ttd/'.$fileName;
            }

            $userData = User::create([
                'name' => $request->namaUser,
                'email' => $request->emailUser,
                'nim_nip_nidn' => $request->nimNipNidnUser,
                'no_hp' => $request->noHp,
                'ttd' => $fileNameDatabase != '' ? $fileNameDatabase : null,
                'password' => Hash::make($request->passwordUser),
            ]);

            if ($request->prodiUser) {
                $fakId = ProgramStudi::find($request->prodiUser)->id_fakultas;
            }

            if ($request->fakultasUser) {
                $prodiId = Fakultas::find($request->fakultasUser)->programStudi->first()->id;
            }

            UsersPivot::create([
                'id_user' => $userData->id,
                'id_role' => $request->roleUser,
                'id_program_studi' => $request->prodiUser ?? $prodiId,
                'id_fakultas' => $request->fakultasUser ?? $fakId,
            ]);

            DB::commit();

            if ($fileNameDatabase != '') {
                $file->storeAs('public/images/ttd', $fileName);
            }

            return redirect()->route('user.index')->with('message', 'Berhasil menambahkan user.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->route('user.index')->with('error', 'Gagal menambahkan user.');
        }
    }

    public function update(Request $request, $id)
    {
        // return \response()->json($request->all());

        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

        $rules = [
            'namaUser' => ['required', 'string', 'max:255'],
            'emailUser' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email,'.$id],
            // 'roleUser' => ['required', 'string', 'in:admin,kaprodi,sekprodi,dosen,mahasiswa'],
            'nimNipNidnUser' => 'nullable|string|regex:/^[0-9]{6,18}$/',
            'noHp' => 'nullable|string|regex:/^62\d{9,13}$/|max:15',
            'ttdUser' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $customMessages = [
            'namaUser.required' => 'Nama harus diisi',
            'namaUser.string' => 'Nama harus berupa huruf',
            'namaUser.max' => 'Nama maksimal 255 karakter',
            'emailUser.required' => 'Email harus diisi',
            'emailUser.string' => 'Email harus berupa huruf',
            'emailUser.lowercase' => 'Email harus berupa huruf kecil',
            'emailUser.email' => 'Email harus valid',
            'emailUser.max' => 'Email maksimal 255 karakter',
            'emailUser.unique' => 'Email sudah terdaftar',
            // 'roleUser.required' => 'Role harus diisi',
            // 'roleUser.string' => 'Role harus berupa huruf',
            // 'roleUser.in' => 'Role harus admin, kaprodi, sekprodi, dosen, atau mahasiswa',
            'nimNipNidnUser.string' => 'NIM/NIP/NIDN harus berupa huruf',
            'nimNipNidnUser.regex' => 'NIM/NIP/NIDN harus berupa angka dengan minimal 6 digit dan maksimal 18 digit',
            'noHp.string' => 'No HP harus berupa huruf',
            'noHp.regex' => 'No HP harus dimulai dengan 62xxxxxxx dengan minimal 9 digit dan maksimal 13 digit',
            'noHp.max' => 'No HP maksimal 15 karakter',
            'ttdUser.image' => 'TTD harus berupa gambar',
            'ttdUser.mimes' => 'TTD harus berformat jpeg, jpg, atau png',
            'ttdUser.max' => 'TTD maksimal 2048 KB',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Gagal mengubah data '. $validator->errors()->first());
        }

        $validator->validate();

        DB::beginTransaction();
        try {
            $userData = User::find($id);
            $userDataCopied = clone $userData;

            $fileNameDatabase = '';
            if ($request->hasFile('ttdUser')) {
                $file = $request->file('ttdUser');
                $fileName = 'ttd_'.time().$file->getClientOriginalName();
                $fileNameDatabase = 'public/images/ttd/'.$fileName;
            }

            $userData->update([
                'name' => $request->namaUser,
                'email' => $request->emailUser,
                'nim_nip_nidn' => $request->nimNipNidnUser,
                'no_hp' => $request->noHp,
                'ttd' => $fileNameDatabase != '' ? $fileNameDatabase : $userData->ttd
            ]);

            if ($request->roleUser1) {
                UsersPivot::where('id_user', $id)->forceDelete();
                if ($request->prodiUser1) {
                    $fakId1 = ProgramStudi::find($request->prodiUser1)->id_fakultas;
                    $userDataPivotUpdate = UsersPivot::where('id_user', $userData->id)->where('id_role', $request->roleUser1)->first();
                    UsersPivot::create([
                        'id_user' => $id,
                        'id_role' => $request->roleUser1,
                        'id_program_studi' => $request->prodiUser1,
                        'id_fakultas' => $fakId1,
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Gagal mengubah data. Program Studi harus diisi.');
                }
            }

            if ($request->roleUser2) {
                if ($request->prodiUser2) {
                    $fakId2 = ProgramStudi::find($request->prodiUser2)->id_fakultas;
                    $userDataPivotUpdate = UsersPivot::where('id_user', $userData->id)->where('id_role', $request->roleUser2)->first();
                    $userDataPivotUpdate->update([
                        'id_program_studi' => $request->prodiUser2,
                        'id_fakultas' => $fakId2,
                    ]);
                } else {
                    return redirect()->back()->with('error', 'Gagal mengubah data. Program Studi harus diisi.');
                }
            }

            DB::commit();

            if ($request->hasFile('ttdUser') && $userDataCopied->ttd) {
                if (Storage::exists($userDataCopied->ttd)) {
                    Storage::disk('local')->delete($userDataCopied->ttd);
                }
            }

            if ($fileNameDatabase != '') {
                $file->storeAs('public/images/ttd', $fileName);
            }

            return redirect()->back()->with('message', 'Berhasil mengubah data.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengubah data.'. $e->getMessage());
        }
    }

    public function destroyRole2(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $userPivot = UsersPivot::where('id_user', $id)->where('id_role', $request->roleUser2)->first();
            $userPivot->forceDelete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus role data.');
        }
    }

    public function verifyEmail($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);

            $currentDateTime = Carbon::now();

            $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');

            $user->update([
                'email_verified_at' => $formattedDateTime,
            ]);

            DB::commit();

            return redirect()->back()->with('message', 'Berhasil memverifikasi email.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal memverifikasi email.');
        }
    }

    public function unverifyEmail($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);

            $user->update([
                'email_verified_at' => null,
            ]);

            DB::commit();

            return redirect()->back()->with('message', 'Berhasil membatalkan verifikasi email.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal membatalkan verifikasi email.');
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $rule = [
            'passwordUser' => 'required|string|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)(?!.*\\s).{8,}$/',
        ];

        $customMessages = [
            'passwordUser.required' => 'Kata sandi harus diisi',
            'passwordUser.string' => 'Kata sandi harus berupa huruf',
            'passwordUser.regex' => 'Kata sandi harus mengandung setidaknya satu angka, satu huruf kecil, satu huruf besar, satu simbol, dan minimal 8 karakter',
        ];

        $validator = Validator::make(request()->all(), $rule, $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Gagal mengubah kata sandi '. $validator->errors()->first());
        }

        $validator->validate();

        DB::beginTransaction();
        try {
            $user = User::find($id);
            $user->update([
                'password' => Hash::make($request->passwordUser),
            ]);

            DB::commit();

            Auth::logoutOtherDevices($request->passwordUser);

            return redirect()->back()->with('message', 'Berhasil mengubah kata sandi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengubah kata sandi.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            $userCopied = $user;
            $user->delete();

            DB::commit();

            if ($userCopied->ttd){
                if (Storage::exists($userCopied->ttd)) {
                    Storage::disk('local')->delete($userCopied->ttd);
                }
            }

            return redirect()->back()->with('message', 'Berhasil menghapus data.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->find($id);

            $user->restore();

            DB::commit();

            return redirect()->back()->with('message', 'Berhasil memulihkan data.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->with('error', 'Gagal memulihkan data.');
        }
    }
}
