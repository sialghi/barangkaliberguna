<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- PERBAIKAN 1: Mencegah Zoom Out/In --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if (!config('adminlte.enabled_laravel_mix'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@100..800&display=swap" rel="stylesheet">

        @if (config('adminlte.google_fonts.allowed', true))
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        @endif
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if (config('adminlte.livewire'))
        @if (intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if (config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body @yield('body_data') class="login-page-custom">

    {{-- 
        =========================================================
        PERBAIKAN 2: BACKGROUND FIXED
        Div ini 'keluar' dari aliran dokumen (fixed). 
        Apapun ukuran background di dalamnya, tidak akan menambah scroll bar halaman.
        =========================================================
    --}}
    <div class="fixed-background">
        <div class="background-shape"></div>
    </div>

    {{-- 
        =========================================================
        PERBAIKAN 3: KONTEN FLEXBOX
        Wadah utama yang membungkus Logo dan Form.
        Menggunakan min-height: 100vh agar jika konten sedikit, dia pas satu layar.
        Jika konten banyak (form panjang), dia akan memanjang dan bisa di-scroll secukupnya.
        =========================================================
    --}}
    <div class="main-content-wrapper">

        {{-- BAGIAN KIRI/ATAS: HEADER & LOGO --}}
        <div class="auth-header">
            <div class="{{ $auth_type ?? 'login' }}-logo mb-2">
                <a href="{{ $dashboard_url }}">
                    @if (config('adminlte.auth_logo.enabled', false))
                        <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                            alt="{{ config('adminlte.auth_logo.img.alt') }}"
                            @if (config('adminlte.auth_logo.img.class', null)) class="{{ config('adminlte.auth_logo.img.class') }}" @endif
                            @if (config('adminlte.auth_logo.img.width', null)) width="{{ config('adminlte.auth_logo.img.width') }}" @endif
                            @if (config('adminlte.auth_logo.img.height', null)) height="{{ config('adminlte.auth_logo.img.height') }}" @endif>
                    @else
                        <img src="{{ asset(config('adminlte.logo_img')) }}"
                            alt="{{ config('adminlte.logo_img_alt') }}" height="50">
                    @endif
                    {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
                </a>
            </div>

            <a href="/home" class="text-center text-dark link-dark title-text">
                Sistem Informasi Layanan<br>Fakultas Sains dan Teknologi
            </a>
        </div>

        {{-- BAGIAN KANAN/BAWAH: FORM LOGIN --}}
        <div class="auth-body">
            @yield('body')
        </div>

    </div>

    {{-- CSS CUSTOM --}}
    <style>
        /* Reset Dasar */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Source Sans Pro', sans-serif;
            overflow-x: hidden;
            /* Matikan scroll horizontal */
        }

        /* ----------------------------------------------------
           SETTING BACKGROUND (FIXED)
           ---------------------------------------------------- */
        .fixed-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            /* Di belakang konten */
            overflow: hidden;
            /* Potong background yang berlebih */
            pointer-events: none;
            /* Klik tembus ke bawah */
        }

        .background-shape {
            display: flex;
            position: absolute;
            top: 0;
            left: -15%;
            /* Ukuran raksasa tetap dipertahankan untuk efek visual */
            width: 100vw;
            height: 500vh;
            margin-top: -400px;
            background: rgb(212, 236, 240);
            background: linear-gradient(25deg, rgba(212, 236, 240, 1) 80%, rgba(255, 255, 255, 1) 90%);
            transform: translateX(-50%) rotate(12deg);
            box-shadow: rgba(0, 0, 0, 0.6) 0px 14px 50px, rgba(0, 0, 0, 0.2) 0px 10px 10px;
        }

        /* ----------------------------------------------------
           SETTING LAYOUT KONTEN (FLEXBOX)
           ---------------------------------------------------- */
        .main-content-wrapper {
            display: flex;
            min-height: 100vh;
            /* Minimal setinggi layar */
            width: 100%;
            align-items: center;
            /* Vertikal center */
            justify-content: center;
            position: relative;
        }

        /* Judul Text */
        .title-text {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            text-decoration: none;
            line-height: 1.3;
            display: block;
            margin-top: 10px;
        }

        /* --- TAMPILAN DESKTOP (Layar Besar) --- */
        @media (min-width: 769px) {
            .main-content-wrapper {
                flex-direction: row;
                /* Kiri Kanan */
            }

            .auth-header {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding-left: 5%;
            }

            .auth-body {
                flex: 1;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }

        /* --- TAMPILAN MOBILE (HP / Tablet Kecil) --- */
        @media (max-width: 768px) {
            .main-content-wrapper {
                flex-direction: column;
                /* Atas Bawah */
                justify-content: center;
                padding: 20px 0;
                /* Padding atas bawah agar tidak mepet */
            }

            .auth-header {
                width: 100%;
                text-align: center;
                margin-bottom: 30px;
                /* Jarak antara logo dan form */
                margin-top: 20px;
            }

            .title-text {
                font-size: 20px;
                /* Font agak kecil di HP */
            }

            .auth-body {
                width: 90%;
                /* Lebar form di HP */
                max-width: 400px;
            }
        }
    </style>


    {{-- Base Scripts --}}
    @if (!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if (config('adminlte.livewire'))
        @if (intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

    <a href="https://wa.me/628983636919?text=Hi" target="_blank" id="whatsapp-button" aria-label="Chat on WhatsApp">
        <img src="{{ asset(config('adminlte.icon_wa', 'vendor/adminlte/dist/img/wa-bot.png')) }}" alt="WhatsApp" />
    </a>

    <style>
        #whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #25d366;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease;
        }

        #whatsapp-button:hover {
            transform: scale(1.1);
        }

        #whatsapp-button img {
            width: 30px;
            height: 30px;
        }
    </style>

</body>

</html>
