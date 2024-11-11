<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = User::where('id', $user->id)->with('fakultas', 'programStudi')->first();

        return view('setting', compact('data', 'userRole', 'userPivot'));
    }

    public function edit()
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        $data = User::where('id', $user->id)->with('fakultas', 'programStudi')->first();

        return view('setting_edit', compact('data', 'userRole', 'userPivot'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->roles->pluck('nama')->toArray();
        $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi')->orderBy('id_role', 'desc')->get();

        DB::beginTransaction();

        try {
            $data = User::find($user->id);

            $regex = "/^62\d{9,13}$/";
            $no__hp = "62".$request->noHp;

            if (!preg_match($regex, $no__hp)) {
                return redirect()->back()->withErrors(['no_hp_regex' => 'No HP harus dimulai dengan 62xxxxxxx dengan minimal 9 digit dan maksimal 13 digit']);
            }

            if ($request->altEmail == $data->email) {
                return redirect()->back()->withErrors(['alt_email' => 'Email alternatif tidak boleh sama dengan email utama!']);
            }

            $data->update([
                'no_hp' => $no__hp ?? $data->no_hp,
                'alt_email' => $request->altEmail ?? $data->alt_email,
                'jalur_masuk' => $request->jalurMasuk ?? $data->jalur_masuk,
            ]);
            $data->save();

            DB::commit();

            return redirect()->route('setting')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error($e->getMessage());
            return redirect()->route('setting')->withErrors(['update_error' => 'Data gagal diperbarui.']);
        }
    }

    public function updateNoHp(Request $request)
    {
        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $user = User::find($userId);
            $regex = "/^62\d{9,13}$/";
            $no__hp = "62".$request->no_hp;

            if (!preg_match($regex, $no__hp)) {
                return redirect()->back()->withErrors(['no_hp_regex' => 'No HP harus dimulai dengan 62xxxxxxx dengan minimal 9 digit dan maksimal 13 digit']);
            }

            $user->no_hp = $no__hp;
            $user->save();

            DB::commit();

            return redirect()->back()->with('success', 'No HP berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->back()->withErrors(['no_hp_error' => 'No HP gagal diperbarui.']);
        }
    }

    public function updatePassword(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $user = User::find($userId);

            if ($request->current_password == '') {
                return redirect()->back()->withErrors(['current_password_required' => 'Kata sandi saat ini harus diisi']);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password_wrong' => 'Kata sandi saat ini tidak valid']);
            }

            // Perbarui password baru
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            DB::commit();

            Auth::logoutOtherDevices($request->new_password);

            return redirect()->route('logout');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->back()->withErrors(['password_error' => 'Kata sandi gagal diperbarui.']);
        }
    }

    public function inputTtd(Request $request)
    {
        $rules = [
            'ttd' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048'
            ]
        ];

        $message = [
            'ttd.required' => 'File harus diisi',
            'ttd.image' => 'File harus berupa gambar',
            'ttd.mimes' => 'Format file salah',
            'ttd.max' => 'File harus dibawah 2 mb'
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        $validator->validate();

        DB::beginTransaction();

        try {
            if ($validator->fails()) {
                return redirect()->route('setting')->withErrors($validator)->withInput();
            } else {
                $userId = Auth::id();
                $user = User::find($userId);
                if ($user->ttd) {
                    Storage::delete($user->ttd);
                }

                // Unggah file TTD baru
                $ttdFileName = 'ttd_' . Auth::user()->nim_nip_nidn . '.png';
                $ttdPath = $request->file('ttd')->storeAs('public/images/ttd', $ttdFileName);

                // Simpan path TTD baru ke kolom 'ttd' di tabel 'users'
                $user->ttd = $ttdPath;
                $user->save();

                DB::commit();

                return redirect()->route('setting')->with('success', 'TTD berhasil Diinput.');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('setting')->withErrors(['ttd_error' => 'TTD gagal Diinput.']);
        }
    }

    public function updateSecondaryEmail(Request $request)
    {
        $id = Auth::id();

        $rules = [
            'secondaryEmail' => 'required|email|unique:users,secondary_email,' . $id
        ];

        $customMessages = [
            'secondaryEmail.required' => 'Email harus diisi',
            'secondaryEmail.email' => 'Email tidak valid',
            'secondaryEmail.unique' => 'Email sudah terdaftar',
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->route('setting')->with('error', 'Gagal mengubah data '. $validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $user = User::find($id);
            $user->secondary_email = $request->secondaryEmail;
            $user->save();

            DB::commit();

            return redirect()->route('setting')->with('success', 'Email berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('setting')->with('error', 'Gagal mengubah data.');
        }
    }

    public function updateJalurMasuk(Request $request)
    {
        $id = Auth::id();

        $rules = [
            'jalurMasuk' => 'required'
        ];

        $customMessages = [
            'jalurMasuk.required' => 'Jalur masuk harus diisi'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return redirect()->route('setting')->with('error', 'Gagal mengubah data '. $validator->errors()->first());
        }

        DB::beginTransaction();

        try {
            $user = User::find($id);
            $user->jalur_masuk = $request->jalurMasuk;
            $user->save();

            DB::commit();

            return redirect()->route('setting')->with('success', 'Jalur masuk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('setting')->with('error', 'Gagal mengubah jalur masuk.');
        }
    }
}
