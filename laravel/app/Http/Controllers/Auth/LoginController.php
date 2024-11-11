<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the validation rules that apply to the login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function rules(Request $request)
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                // // regex to use email uin jkt in logins
                // function ($attribute, $value, $fail) {
                //     if (!preg_match('/^[\w.+-]+@(?:mhs\.)?uinjkt\.ac\.id$/', $value)) {
                //         $fail('Email harus berakhiran @uinjkt.ac.id');
                //     }
                // },
            ],
            'password' => 'required|string',
        ];
    }

    /**
     * Get the validation messages for the login request.
     *
     * @return array
     */
    protected function validationMessages()
    {
        return [
            'email.required' => 'Email harus diisi',
            'email.string' => 'Email harus berupa string',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Kata sandi harus diisi',
            'password.string' => 'Kata sandi harus berupa string',
        ];
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $request->validate($this->rules($request), $this->validationMessages());
    }

    /**
     * Send the response after the user was not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => ['Email atau kata sandi salah'],
        ])->status(422);
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user->hasVerifiedEmail()) {

            // Send the verification email
            Notification::send($user, new VerifyEmailNotification($user));

            return redirect()->route('verification.notice');
        }
    }
}
