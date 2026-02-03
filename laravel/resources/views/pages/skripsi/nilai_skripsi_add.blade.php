@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Input Nilai Tugas Akhir</h1>
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
                        <div>
                            <p>Panduan Tombol</p>
                            <table>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnSubmitNilai.png" /></th>
                                    <td>Tombol untuk submit input nilai.</td>
                                </tr>
                            </table>
                        </div>
                        <div class="mt-4">
                            <p>Panduan Pengisian</p>
                            <table>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <td>Nama mahasiswa yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Nomor Induk Mahasiswa</th>
                                    <td>Nomor Induk Mahasiswa yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Judul Tugas Akhir</th>
                                    <td>Judul Tugas Akhir yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Sidang</th>
                                    <td>Tanggal Sidang yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Dosen Pembimbing 1</th>
                                    <td>Dosen pembimbing 1 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Nilai Dosen Pembimbing 1</th>
                                    <td>Nilai dosen pembimbing 1 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Dosen Pembimbing 2</th>
                                    <td>Dosen pembimbing 2 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Nilai Dosen Pembimbing 2</th>
                                    <td>Nilai dosen pembimbing 2 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Dosen Penguji 1</th>
                                    <td>Dosen penguji 1 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Nilai Dosen Penguji 1</th>
                                    <td>Nilai dosen penguji 1 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Dosen Penguji 2</th>
                                    <td>Dosen penguji 2 yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Nilai Dosen Penguji 2</th>
                                    <td>Nilai dosen penguji 2 yang diinput.</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@stop

@php
    $config = ['format' => 'YYYY-MM-DD'];
@endphp

@section('content')
    @if ($errors->hasAny(['pembimbing1_not_found', 'pembimbing2_not_found', 'penguji1_not_found', 'penguji2_not_found']))
        <x-adminlte-alert id="error-alert" theme="danger" title="Error">
            @foreach (['pembimbing1_not_found', 'pembimbing2_not_found', 'penguji1_not_found', 'penguji2_not_found'] as $field)
                @if ($errors->has($field))
                    <li>{{ $errors->first($field) }}</li>
                @endif
            @endforeach
        </x-adminlte-alert>

        <script>
            setTimeout(function() {
                document.getElementById('error-alert').style.display = 'none';
            }, 3000);
        </script>
    @endif

    <form method="POST" action="{{ route('store.nilai.sidang.skripsi') }}" enctype="multipart/form-data">
        @csrf
        <label for="pendaftarSkripsiSelect">Data Mahasiswa <span class="text-red">*</span> </label>
        <x-adminlte-select-bs name="pendaftarSkripsiSelect" label-class="text-black" igroup-size="md" data-live-search
            data-style="border: 1px solid #ced4da; background-color: #fff;" data-live-search-placeholder="Cari..."
            data-show-tick data-title="Pilih Mahasiswa..." value="{{ old('pendaftarSkripsiSelect') }}">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-info">
                    <i class="fas fa-id-card"></i>
                </div>
            </x-slot>

            {{-- ✅ PLACEHOLDER WAJIB --}}
            <option value="" selected disabled hidden>Pilih Mahasiswa...</option>

            @foreach ($pendaftarSkripsi as $data)
                <option value="{{ $data->id }}" {{ old('pendaftarSkripsiSelect') == $data->id ? 'selected' : '' }}>
                    {{ $data->waktu_ujian }},
                    {{ $data->mahasiswa->nim_nip_nidn }} -
                    {{ $data->mahasiswa->name }}
                </option>
            @endforeach
        </x-adminlte-select-bs>

        {{-- <x-adminlte-select id="pendaftarSkripsiSelect" name="pendaftarSkripsiSelect" label="Data Mahasiswa">
        <x-slot name="prependSlot">
            <div class="input-group-text bg-gradient-info">
                <i class="fas fa-id-card"></i>
            </div>
        </x-slot>
        <option value="" selected disabled hidden>Pilih Pendaftar</option>
        @if (count($pendaftarSkripsi) == 0)
            <option value="" disabled>Tidak ada data mahasiswa pendaftar sidang tugas akhir</option>
        @else
            @foreach ($pendaftarSkripsi as $data)
                <option value="{{ $data->id }}" {{ old('pendaftarSkripsiSelect') == $data->id ? 'selected' : '' }}>
                    {{ $data->waktu_ujian }}, {{ $data->mahasiswa->nim_nip_nidn }} - {{ $data->mahasiswa->name }}
                </option>
            @endforeach
        @endif
    </x-adminlte-select> --}}
        <input id="pendaftarSkripsiId" type="hidden" name="pendaftarSkripsiId" value="">

        <table style=" background-color: transparent;">
            <tr>
                <td>
                    {{-- NAMA MAHASISWA --}}
                    <label for="nama_mahasiswa">Nama Mahasiswa <span class="text-red">*</span></label>
                    <x-adminlte-input name="nama_mahasiswa" id="nama_mahasiswa" placeholder="Nama Mahasiswa"
                        value="{{ old('nama_mahasiswa') }}" autocomplete="off" disabled>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-info">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
                <td>
                    {{-- NIM --}}
                    <label for="nim">Nomor Induk Mahasiswa <span class="text-red">*</span></label>
                    <x-adminlte-input name="nim" id="nim" placeholder="Nomor Induk Mahasiswa"
                        value="{{ old('nim') }}" type="number" autocomplete="off" disabled>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-info">
                                <i class="fas fa-address-card"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- Judul Skripsi --}}
                    <label for="judulSkripsi">Judul Tugas Akhir <span class="text-red">*</span></label>
                    <x-adminlte-input name="judulSkripsi" id="judulSkripsi" placeholder="Masukkan judul skripsi..."
                        value="{{ old('judulSkripsi') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-info">
                                <i class="fas fa-pen"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
                <td>
                    {{-- Tanggal Sidang --}}
                    <label for="tanggalUjian">Tanggal Sidang <span class="text-red">*</span></label>
                    <x-adminlte-input-date name="tanggalUjian" id="tanggalUjian" :config="$config"
                        placeholder="Pilih tanggal sidang..." value="{{ old('tanggalUjian') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-danger">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input-date>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tipeUjian">Tipe Ujian <span class="text-red">*</span></label>
                    <x-adminlte-select name="tipeUjian" id="tipeUjian">
                        <option value="" selected disabled hidden>Pilih Tipe Ujian</option>
                        <option value="online" {{ old('tipeUjian') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ old('tipeUjian') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-purple">
                                <i class="fas fa-list"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>
                </td>
                <td>
                    @php
                        $configJam = ['format' => 'HH:mm'];
                    @endphp
                    {{-- Jam Ujian --}}
                    <label for="jamUjian">Jam Ujian</label>
                    <x-adminlte-input-date name="jamUjian" id="jamUjian" :config="$configJam"
                        placeholder="Pilih jam ujian..." value="{{ old('jamUjian') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-cyan">
                                <i class="fas fa-clock"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input-date>
                </td>
            </tr>
            <tr>
                <td>
                    <div id="ruanganUjian" style="display: none;">
                        {{-- Ruangan Ujian --}}
                        <label for="ruanganUjian">Ruangan Ujian <span class="text-grey small">(opsional)</span> </label>
                        <x-adminlte-input name="ruanganUjian" id="ruanganUjian" placeholder="Masukkan ruangan ujian..."
                            value="{{ old('ruanganUjian') }}" autocomplete="off">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-store-alt"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div id="linkUjian" style="display: none;">
                        {{-- Link Webinar --}}
                        <label for="linkUjian">Link Webinar</label>
                        <x-adminlte-input name="linkUjian" id="linkUjian" placeholder="Masukkan link webinar..."
                            value="{{ old('linkUjian') }}" autocomplete="off">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-blue">
                                    <i class="fas fa-globe"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        <small class="text-muted">Max length: 191 characters</small>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- NAMA PEMBIMBING 1 --}}
                    <label for="pembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span> </label>
                    <x-adminlte-select-bs name="pembimbing1" label-class="text-black" igroup-size="md"
                        data-title="Pilih Dosen Pembimbing 1..." data-live-search
                        data-style='border: 1px solid #ced4da; background-color: #fff;'
                        data-live-search-placeholder="Cari..." data-show-tick value="{{ old('pembimbing1') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                        {{-- ✅ PLACEHOLDER WAJIB --}}
                        <option value="" selected disabled hidden>...</option>
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->display_name }}</option>
                        @endforeach
                    </x-adminlte-select-bs>

                    {{-- <label for="pembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span></label>
                <x-adminlte-select name="pembimbing1" id="pembimbing1">
                    <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
                </td>
                <td>
                    {{-- NILAI PEMBIMBING 1 --}}
                    <x-adminlte-input name="nilaiPembimbing1" label="Nilai Pembimbing 1" placeholder="Nilai Pembimbing 1"
                        type="number" min="0" max="100" value="{{ old('nilaiPembimbing1') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-success">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- NAMA PEMBIMBING 2 --}}
                    <label for="pembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span> </label>
                    <x-adminlte-select-bs name="pembimbing2" label-class="text-black" igroup-size="md"
                        data-title="Pilih Dosen Pembimbing 2..." data-live-search
                        data-style='border: 1px solid #ced4da; background-color: #fff;'
                        data-live-search-placeholder="Cari..." data-show-tick value="{{ old('pembimbing2') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                        {{-- ✅ PLACEHOLDER WAJIB --}}
                        <option value="" selected disabled hidden>...</option>
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->display_name }}</option>
                        @endforeach
                    </x-adminlte-select-bs>

                    {{-- <label for="pembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span></label>
                <x-adminlte-select name="pembimbing2" id="pembimbing2">
                    <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
                </td>
                <td>
                    {{-- NILAI PEMBIMBING 2 --}}
                    <x-adminlte-input name="nilaiPembimbing2" label="Nilai Pembimbing 2" placeholder="Nilai Pembimbing 2"
                        type="number" min="0" max="100" value="{{ old('nilaiPembimbing2') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-success">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- NAMA PENGUJI 1 --}}
                    <label for="penguji1">Dosen Penguji 1 <span class="text-red">*</span> </label>
                    <x-adminlte-select-bs name="penguji1" label-class="text-black" igroup-size="md"
                        data-title="Pilih Dosen Penguji 1..." data-live-search
                        data-style='border: 1px solid #ced4da; background-color: #fff;'
                        data-live-search-placeholder="Cari..." data-show-tick value="{{ old('penguji1') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                        {{-- ✅ PLACEHOLDER WAJIB --}}
                        <option value="" selected disabled hidden>...</option>
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('penguji1') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->display_name }}</option>
                        @endforeach
                    </x-adminlte-select-bs>

                    {{-- <label for="penguji1">Dosen Penguji 1 <span class="text-red">*</span></label>
                <x-adminlte-select name="penguji1" id="penguji1">
                    <option value="" selected disabled hidden>Pilih Dosen Penguji 1</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('penguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
                </td>
                <td>
                    {{-- NILAI PENGUJI 1 --}}
                    <x-adminlte-input name="nilaiPenguji1" label="Nilai Penguji 1" placeholder="Nilai Penguji 1"
                        type="number" min="0" max="100" value="{{ old('nilaiPenguji1') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-success">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- NAMA PENGUJI 2 --}}
                    <label for="penguji2">Dosen Penguji 2 <span class="text-red">*</span> </label>
                    <x-adminlte-select-bs name="penguji2" label-class="text-black" igroup-size="md"
                        data-title="Pilih Dosen Penguji 2..." data-live-search
                        data-style='border: 1px solid #ced4da; background-color: #fff;'
                        data-live-search-placeholder="Cari..." data-show-tick value="{{ old('penguji2') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                        {{-- ✅ PLACEHOLDER WAJIB --}}
                        <option value="" selected disabled hidden>...</option>
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('penguji2') == $dosen->id ? 'selected' : '' }}>
                                {{ $dosen->display_name }}</option>
                        @endforeach
                    </x-adminlte-select-bs>

                    {{-- <label for="penguji2">Dosen Penguji 2 <span class="text-red">*</span></label>
                <x-adminlte-select name="penguji2" id="penguji2">
                    <option value="" selected disabled hidden>Pilih Dosen Penguji 2</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('penguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
                </td>
                <td>
                    {{-- NILAI PENGUJI 2 --}}
                    <x-adminlte-input name="nilaiPenguji2" label="Nilai Penguji 2" placeholder="Nilai Penguji 2"
                        type="number" min="0" max="100" value="{{ old('nilaiPenguji2') }}">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-success">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
            </tr>
        </table>

        <br>
        <x-adminlte-button type="submit" name="submit" label="Submit" theme="primary"
            style="float: left; width: 20%;" />
    </form>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('#pendaftarSkripsiSelect').change(function() {
                var pendaftarId = $(this).val();
                if (pendaftarId) {
                    // Assuming you have an endpoint to fetch data for a specific pendaftar ID
                    var apiUrl = '/api/sidang_skripsi/daftar/detail/' + pendaftarId;

                    // Make an AJAX request to fetch the data
                    $.get(apiUrl, function(data) {
                        // Update the content of the table cells with the fetched data
                        $('#pendaftarSkripsiId').val(data.pendaftaranSkripsi.mahasiswa.id)
                        $('#nama_mahasiswa').val(data.pendaftaranSkripsi.mahasiswa.name)
                        $('#nim').val(data.pendaftaranSkripsi.mahasiswa.nim_nip_nidn)

                        $('#judulSkripsi').val(data.pendaftaranSkripsi.judul_skripsi)
                        var tanggalUjian = data.pendaftaranSkripsi.waktu_ujian.split(' ')[0];
                        $('#tanggalUjian').val(tanggalUjian)
                        var timeParts = data.pendaftaranSkripsi.waktu_ujian.split(' ')[1].split(
                            ':');
                        var jam = timeParts[0] + ':' + timeParts[1];
                        $('#jamUjian').val(jam);

                        $('#pembimbing1').html('<option value="' + data.pendaftaranSkripsi
                            .pembimbing1.id + '" selected hidden>' + data.pendaftaranSkripsi
                            .pembimbing1.name +
                            '</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                        )
                        $('#pembimbing2').html('<option value="' + data.pendaftaranSkripsi
                            .pembimbing2.id + '" selected hidden>' + data.pendaftaranSkripsi
                            .pembimbing2.name +
                            '</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                        )

                        if (data.pendaftaranSkripsi.calon_penguji1) {
                            $('#penguji1').html('<option value="' + data.pendaftaranSkripsi
                                .calon_penguji1.id + '" selected hidden>' + data
                                .pendaftaranSkripsi.calon_penguji1.name +
                                '</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                            )
                        } else {
                            $('#penguji1').html(
                                '<option value="" selected disabled hidden>Pilih Dosen Penguji 1</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                            )
                        }

                        if (data.pendaftaranSkripsi.calon_penguji2) {
                            $('#penguji2').html('<option value="' + data.pendaftaranSkripsi
                                .calon_penguji2.id + '" selected hidden>' + data
                                .pendaftaranSkripsi.calon_penguji2.name +
                                '</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                            )
                        } else {
                            $('#penguji2').html(
                                '<option value="" selected disabled hidden>Pilih Dosen Penguji 2</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                            )
                        }
                    })
                } else {
                    // If no pendaftar is selected, reset the content to "None"
                    $('#pendaftarSkripsiId').val('')
                    $('#nama_mahasiswa').val('')
                    $('#nim').val('')
                    $('#judulSkripsi').val('')
                    $('#tanggalUjian').val('')
                    $('#jamUjian').val('')
                    // $('#pembimbing1').html(
                    //     '<option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                    // )
                    // $('#pembimbing2').html(
                    //     '<option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                    // )
                    // $('#penguji1').html(
                    //     '<option value="" selected disabled hidden>Pilih Dosen Penguji 1</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                    // )
                    // $('#penguji2').html(
                    //     '<option value="" selected disabled hidden>Pilih Dosen Penguji 2</option>@foreach ($namaDosen as $dosen) <option value="{{ $dosen->id }}" {{ old('penguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->display_name }}</option> @endforeach'
                    // )
                }
            });
            var oldValue = "{{ old('pendaftarSkripsiSelect') }}";
            if (oldValue) {
                $('#pendaftarSkripsiSelect').val(oldValue).change();
            }

            $('#tipeUjian').change(function() {
                var selectedType = $(this).val();
                if (selectedType === 'online') {
                    $('#linkUjian').show();
                    $('#ruanganUjian').hide();
                } else if (selectedType === 'offline') {
                    $('#linkUjian').hide();
                    $('#ruanganUjian').show();
                } else {
                    $('#linkUjian').hide();
                    $('#ruanganUjian').hide();
                }
            });
            var oldTypeValue = "{{ old('tipeUjian') }}";
            if (oldTypeValue) {
                $('#tipeUjian').val(oldTypeValue).change();
            }
        });
    </script>
@endpush

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop
