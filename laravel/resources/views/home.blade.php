@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
    <script src="/vendor/dist/jquery/jquery.slim.min.js">
    <script src="/vendor/dist/js/bootstrap.bundle.min.js">
@stop

@section('content_header')
    <h5 class="font-weight-light">Selamat Datang di</h5>
    <div class="d-flex flex-row align-items-center">
        <div>
            <h1>Sistem Informasi Layanan</h1>
            <h1>Fakultas Sains dan Teknologi</h1>
        </div>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
    </div>
    <hr>
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Panduan Halaman</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="panduanSection">
                        <img id="imgPanduan" src="/img/panduan/totalSurat.png"/>
                        <p>"Total Surat" adalah seluruh surat yang dimiliki oleh pengguna baik yang sudah di tanda tangan (TTD), belum di TTD, dan surat yang ditolak.</p>
                    </div>
                    <div id="panduanSection" class="my-4">
                        <img id="imgPanduan" src="/img/panduan/belumTTD.png"/>
                        <p>"Belum di TTD atau Sedang Diproses" adalah seluruh surat yang sudah diunggah oleh pengguna tetapi belum mendapatkan tanda tangan Ketua Prodi TI.</p>
                    </div>
                    <div id="panduanSection">
                        <img id="imgPanduan" src="/img/panduan/sudahTTD.png"/>
                        <p>"Sudah di TTD" adalah seluruh surat yang sudah diunggah oleh pengguna dan sudah mendapatkan tanda tangan Ketua Prodi TI.</p>
                    </div>
                    <div id="panduanSection" class="mt-4">
                        <img id="imgPanduan" src="/img/panduan/suratDitolak.png"/>
                        <p>"Surat Ditolak atau Ditolak" adalah seluruh surat yang sudah diunggah oleh pengguna namun ditolak karena ketidaksesuaian format ataupun isi surat.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <h3>TTD Kaprodi</h3>
        <div class="info_box">
                <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat" icon="fas fa-lg fa-inbox"/>
                <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD" icon="fas fa-lg fa-file"/>
                <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD" icon="fas fa-lg fa-file-signature"/>
                <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak" icon="fas fa-lg fa-file-excel"/>
        </div>

        <hr>

        <h3>Permohonan Surat Tugas</h3>
        <div class="info_box">
                <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratPT }}" title="Total Surat" icon="fas fa-lg fa-inbox"/>
                <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $PTdiproses }}" title="Sedang Diproses" icon="fas fa-lg fa-file"/>
                <x-adminlte-info-box class="mr-3" theme="success" text="{{ $PTditerima }}" title="Diterima" icon="fas fa-lg fa-file-signature"/>
                <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $PTditolak }}" title="Ditolak" icon="fas fa-lg fa-file-excel"/>
        </div>

        <hr>
    @endif

    @if(array_intersect(['dosen'], $userRole))
        <h3>Permohonan Surat Tugas oleh Anda (Dosen)</h3>
        <div class="info_box">
                <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratPT }}" title="Total Surat" icon="fas fa-lg fa-inbox"/>
                <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $PTdiproses }}" title="Sedang Diproses" icon="fas fa-lg fa-file"/>
                <x-adminlte-info-box class="mr-3" theme="success" text="{{ $PTditerima }}" title="Diterima" icon="fas fa-lg fa-file-signature"/>
                <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $PTditolak }}" title="Ditolak" icon="fas fa-lg fa-file-excel"/>
        </div>

        <hr>
    @endif

    @if(array_intersect(['mahasiswa'], $userRole))
        <h3>TTD Kaprodi</h3>
        <div class="info_box">
                <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat" icon="fas fa-lg fa-inbox"/>
                <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD" icon="fas fa-lg fa-file"/>
                <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD" icon="fas fa-lg fa-file-signature"/>
                <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak" icon="fas fa-lg fa-file-excel"/>
        </div>

        <hr>
    @endif

    <p> Butuh bantuan? </p>
    <a href="https://chat.whatsapp.com/B87uLWeQEFVECsL54S6go5" target="_blank">
        <p style="color: #4FCE5D">
            <i class="fab fa-whatsapp"></i> Hubungi kami via WhatsApp
        </p>
    </a>
@stop

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

