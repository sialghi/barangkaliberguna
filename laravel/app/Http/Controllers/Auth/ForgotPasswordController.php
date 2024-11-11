<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ResetPasswordNotification;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Custom validation rules
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|string|max:255',
            ],
            [
                'email.required' => 'Email harus diisi',
                'email.email' => 'Email tidak valid',
                'email.string' => 'Email harus berupa string',
                'email.max' => 'Email maksimal 255 karakter'
            ]
        );

        // Redirect back with error message if validation fails
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->validateEmail($request);

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Email belum terdaftar');
        }

        if (!$user->hasVerifiedEmail()) {
            return redirect()->back()->with('error', 'Email belum terverifikasi');
        }

        // Generate the password reset token
        $token = Password::getRepository()->create($user);

        // Send the password reset notification
        $user->notify(new ResetPasswordNotification($token));

        return redirect()->back()->with('status', 'Tautan atur ulang kata sandi telah dikirimkan ke email Anda');
    }
}
