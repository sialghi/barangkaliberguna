<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function reset(Request $request)
    {
        $validator = $this->validateReset($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return $this->sendResetResponse($request, $response);
        } else if ($response == Password::INVALID_TOKEN) {
            throw ValidationException::withMessages([
                'email' => 'Token atur ulang kata sandi tidak valid',
            ]);
        } else if ($response == Password::INVALID_USER) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak terdaftar',
            ]);
        } else {
            throw ValidationException::withMessages([
                'email' => 'Gagal atur ulang kata sandi. Silakan coba lagi',
            ]);
        }
    }

    /**
     * Get the password reset validation rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateReset(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                // Custom validation rules
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'regex:/^[\w.+-]+@(?:mhs\.)?uinjkt\.ac\.id$/' // only uinjkt email
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)(?!.*\\s).{8,}$/',
                    function ($attribute, $value, $fail) use ($request) {
                        $user = $this->broker()->getUser($request->only('email'));
                        if ($user && Hash::check($value, $user->password)) {
                            $fail('Kata sandi baru tidak boleh sama dengan kata sandi sebelumnya');
                        }
                    },
                ],
            ],
            [
                // Custom validation messages
                'email.required' => 'Email harus diisi',
                'email.string' => 'Email harus berupa string',
                'email.email' => 'Format email tidak valid',
                'email.max' => 'Email maksimal 255 karakter',
                'email.regex' => 'Email harus berakhiran @uinjkt.ac.id',

                'password.required' => 'Kata sandi harus diisi',
                'password.string' => 'Kata sandi harus berupa string',
                'password.confirmed' => 'Kata sandi tidak cocok',
                'password.min' => 'Kata sandi minimal 8 karakter',
                'password.regex' => 'Kata sandi harus mengandung setidaknya satu angka, satu huruf kecil, satu huruf besar, dan satu simbol',
            ]
        );
    }
}
