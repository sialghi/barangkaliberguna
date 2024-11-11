@extends('adminlte::auth.auth-page', ['auth_type' => 'register'])

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )

@if (config('adminlte.use_route_url', false))
@php( $login_url = $login_url ? route($login_url) : '' )
@php( $register_url = $register_url ? route($register_url) : '' )
@else
@php( $login_url = $login_url ? url($login_url) : '' )
@php( $register_url = $register_url ? url($register_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.register_message'))

@section('auth_body')
<form action="{{ $register_url }}" method="post">
    @csrf

    {{-- Name field --}}
    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="{{ __('adminlte::adminlte.full_name') }}" autofocus style="background-color: #e6f4fd;">

        <div class="input-group-append" style="background-color: #e6f4fd;">
            <div class="input-group-text">
                <span class="fas fa-user {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('name')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- nim_nip_nidn field --}}
    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <input type="text" name="nim_nip_nidn" class="form-control @error('nim_nip_nidn') is-invalid @enderror" value="{{ old('nim_nip_nidn') }}" placeholder="{{ __('adminlte::adminlte.nim_nip_nidn') }}" autofocus style="background-color: #e6f4fd;">

        <div class="input-group-append" style="background-color: #e6f4fd;">
            <div class="input-group-text">
                <span class="fas fa-address-card {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('nim_nip_nidn')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- Email field --}}
    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" style="background-color: #e6f4fd;">

        <div class="input-group-append" style="background-color: #e6f4fd;">
            <div class="input-group-text">
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
    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('adminlte::adminlte.password') }}" style="background-color: #e6f4fd;">

        <div class="input-group-append" style="background-color: #e6f4fd;">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    {{-- Confirm password field --}}
    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="{{ __('adminlte::adminlte.retype_password') }}" style="background-color: #e6f4fd;">

        <div class="input-group-append" style="background-color: #e6f4fd;">
            <div class="input-group-text">
                <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
            </div>
        </div>

        @error('password_confirmation')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>

    <div class="input-group mb-3" style="background-color: #e6f4fd;">
        <select class="form-control" name="programStudi" id="programStudi" style="background-color: #e6f4fd;">
            <option value="" selected disabled hidden>Pilih program studi anda</option>
            @foreach ($programStudi as $prodi)
                <option value="{{ $prodi->id }}" {{ old('programStudi') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
            @endforeach
        </select>
    </div>

    {{-- Register button --}}
    <button type="submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
        <span class="fas fa-user-plus"></span>
        {{ __('adminlte::adminlte.register') }}
    </button>

</form>
@stop

@section('auth_footer')
<p class="my-0">
    <a href="{{ $login_url }}">
        {{ __('adminlte::adminlte.i_already_have_a_membership') }}
    </a>
</p>
<p class="my-0 text-sm" style="color: #343a40">© Fakultas Sains dan Teknologi UIN Jakarta 2024</p>
@stop
