@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php($login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login'))
@php($register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register'))
@php($password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset'))

@if (config('adminlte.use_route_url', false))
    @php($login_url = $login_url ? route($login_url) : '')
    @php($register_url = $register_url ? route($register_url) : '')
    @php($password_reset_url = $password_reset_url ? route($password_reset_url) : '')
@else
    @php($login_url = $login_url ? url($login_url) : '')
    @php($register_url = $register_url ? url($register_url) : '')
    @php($password_reset_url = $password_reset_url ? url($password_reset_url) : '')
@endif

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    {{-- === MULAI KODE ALERT === --}}
    {{-- Menangani pesan Warning (Sesi Habis) --}}
    @if ($message = Session::get('warning'))
        <div id="alert-box">
            <x-adminlte-alert theme="warning" title="Peringatan" icon="fas fa-exclamation-triangle">
                {{ $message }}
            </x-adminlte-alert>
        </div>
        <script>
            setTimeout(function() {
                var alertBox = document.getElementById('alert-box');
                if (alertBox) {
                    alertBox.style.transition = "opacity 0.5s ease";
                    alertBox.style.opacity = 0;
                    setTimeout(function() {
                        alertBox.remove();
                    }, 500);
                }
            }, 4000);
        </script>
    @endif

    {{-- Menangani pesan Error (Login Gagal) --}}
    @if ($message = Session::get('error'))
        <x-adminlte-alert theme="danger" title="Gagal" icon="fas fa-ban">
            {{ $message }}
        </x-adminlte-alert>
    @endif
    {{-- === SELESAI KODE ALERT === --}}
    <form action="{{ $login_url }}" method="post">
        @csrf

        {{-- Email field --}}
        <div class="input-group mb-3" style="background-color: #e6f4fd;">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                style="background-color: #e6f4fd;" value="{{ old('email') }}"
                placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>

            <div class="input-group-append" style="background-color: #e6f4fd;">
                <div class="input-group-text" style="background-color: #e6f4fd;">
                    <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Password field --}}
        {{-- Input Group Password --}}
        <div class="input-group mb-3" style="background-color: #e6f4fd;">

            {{-- 1. Tambahkan id="password" disini --}}
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" style="background-color: #e6f4fd;"
                placeholder="{{ __('adminlte::adminlte.password') }}">

            {{-- 2. Tambahkan event onclick pada div ini --}}
            <div class="input-group-append" style="background-color: #e6f4fd; cursor: pointer;" onclick="togglePassword()">
                <div class="input-group-text" style="background-color: #e6f4fd;">
                    {{-- 3. Ganti icon lock jadi eye, beri id="toggleIcon" --}}
                    <span id="toggleIcon" class="fas fa-eye-slash {{ config('adminlte.classes_auth_icon', '') }}"></span>
                </div>
            </div>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- 4. Tambahkan Script Javascript ini di bawahnya atau di section JS --}}
        <script>
            function togglePassword() {
                var passwordInput = document.getElementById("password");
                var toggleIcon = document.getElementById("toggleIcon");

                if (passwordInput.type === "password") {
                    // Ubah jadi text (terlihat)
                    passwordInput.type = "text";
                    // Ubah ikon jadi mata dicoret
                    toggleIcon.classList.remove("fa-eye-slash");
                    toggleIcon.classList.add("fa-eye");
                } else {
                    // Balikkan jadi password (titik-titik)
                    passwordInput.type = "password";
                    // Ubah ikon jadi mata biasa
                    toggleIcon.classList.remove("fa-eye");
                    toggleIcon.classList.add("fa-eye-slash");
                }
            }
        </script>

        {{-- Login field --}}
        <div class="row">
            <div class="col-7">
                <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label for="remember">
                        {{ __('adminlte::adminlte.remember_me') }}
                    </label>
                </div>
            </div>

            <div class="col-5">
                <button type=submit
                    class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                    <span class="fas fa-sign-in-alt"></span>
                    {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </div>

    </form>
@stop

@section('auth_footer')
    {{-- Password reset link --}}
    @if ($password_reset_url)
        <p class="my-0">
            <a href="{{ $password_reset_url }}">
                {{ __('adminlte::adminlte.i_forgot_my_password') }}
            </a>
        </p>
    @endif

    {{-- Register link --}}
    @if ($register_url)
        <p class="my-0">
            <a href="{{ $register_url }}">
                {{ __('adminlte::adminlte.register_a_new_membership') }}
            </a>
        </p>
    @endif
    <a href="https://chat.whatsapp.com/B87uLWeQEFVECsL54S6go5" target="_blank">
        <p class="my-0" style="color: #4FCE5D">
            <i class="fab fa-whatsapp"></i> Hubungi kami via WhatsApp
        </p>
    </a>
    <p class="my-0 text-sm" style="color: #343a40">© Fakultas Sains dan Teknologi UIN Jakarta 2024</p>
@stop
