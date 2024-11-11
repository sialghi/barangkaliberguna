<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\UsersPivot;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    public function showRegistrationForm()
    {
        $programStudi = \App\Models\ProgramStudi::all();
        return view('auth.register', \compact('programStudi'));
    }

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/email/verify';

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\\s]+$/'
            ],
            'nim_nip_nidn' => ['required', 'numeric', 'digits_between:1,18', 'unique:users'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[\w.+-]+@(?:mhs\.)?uinjkt\.ac\.id$/', // only uinjkt email
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)(?!.*\\s).{8,}$/',
                'confirmed',
            ],
        ];

        $messages = [
            // custom messages for name field
            'name.required' => 'Nama lengkap harus diisi',
            'name.string' => 'Nama lengkap harus berupa string',
            'name.max' => 'Nama lengkap maksimal 255 karakter',
            'name.regex' => 'Nama lengkap hanya boleh mengandung huruf dan spasi',

            // custom messages for nim_nip_nidn field
            'nim_nip_nidn.required' => 'NIM/NIP/NIDN harus diisi',
            'nim_nip_nidn.numeric' => 'NIM/NIP/NIDN harus berupa angka',
            'nim_nip_nidn.digits_between' => 'NIM/NIP/NIDN harus berupa angka dengan panjang 1-18 karakter',
            'nim_nip_nidn.unique' => 'NIM/NIP/NIDN sudah terdaftar',

            // custom messages for email field
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa string',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.regex' => 'Email harus berakhiran @uinjkt.ac.id atau @mhs.uinjkt.ac.id',
            'email.unique' => 'Email sudah terdaftar',

            // custom messages for password field
            'password.required' => 'Kata sandi harus diisi',
            'password.string' => 'Kata sandi harus berupa string',
            'password.confirmed' => 'Kata sandi tidak cocok',
            'password.min' => 'Kata sandi minimal 8 karakter',
            'password.regex' => 'Kata sandi harus mengandung setidaknya satu angka, satu huruf kecil, satu huruf besar, dan satu simbol',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $data['name'],
                'nim_nip_nidn' => $data['nim_nip_nidn'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $fakultasId = \App\Models\ProgramStudi::find($data['programStudi'])->id_fakultas;

            UsersPivot::create([
                'id_user' => $user->id,
                'id_role' => 10,
                'id_program_studi' => $data['programStudi'],
                'id_fakultas' => $fakultasId,
            ]);

            // Send the verification email
            Notification::send($user, new VerifyEmailNotification($user));

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
