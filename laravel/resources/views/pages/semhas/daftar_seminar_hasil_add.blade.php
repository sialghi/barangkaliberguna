@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

{{-- @section('plugins.KrajeeFileinput', true) --}}
@section('plugins.BootstrapSelect', true)


@section('content_header')
    <div class="d-flex flex-row mb-4">
        <h1>Daftar Seminar Hasil</h1>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
    </div>
@stop

@php
    $config = ['format' => 'YYYY-MM-DD'];
@endphp

@section('content')
@php
    $listError = ['fileTranskripNilai',
                'fileSertifikatToefl',
                'filePengesahanSkripsi',
                'filePernyataanKaryaSendiri',
                'fileSertifikatToafl',
                'fileNaskahSkripsi',
                'judulSkripsi',
                'pembimbing1',
                'pembimbing2',
                'waktuSeminar',
                'dosenPembimbingAkademik'];
@endphp
@if($errors->hasAny($listError))
    <div id="fail-alert"class="alert alert-danger" style="width:50%">
        @foreach($listError as $error)
            @if($errors->has($error))
                {{ $errors->first($error) }} <br>
            @endif
        @endforeach
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('fail-alert').style.display = 'none';
        }, 10000);
    </script>
@endif

<form id="formSubmitDaftarSemhas" action="{{ route('store.daftar.seminar.hasil')}}" method="POST" enctype="multipart/form-data">
    @csrf
    <h3>Data Mahasiswa</h3>
    <table style=" background-color: transparent;" class="w-50">
        <tr>
            {{-- NIM Mahasiswa --}}
            <td>
                <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
            </td>
            <td>{{ $user->nim_nip_nidn }}</td>
        </tr>
        <tr>
            {{-- Nama Mahasiswa --}}
            <td>
                <h5 class="font-weight-bold">Nama Mahasiswa</h5>
            </td>
            <td>{{ $user->name }}</td>
        </tr>
    </table>
    <table class="mt-4">
        <tr>
            <td colspan="2" class="w-50">
                {{-- Judul Proposal --}}
                <label for="judulSkripsi">Judul Skripsi <span class="text-red">*</span></label>
                <x-adminlte-textarea name="judulSkripsi" placeholder="Masukkan judul skripsi..." autocomplete="off">
                    {{ old('judulSkripsi') }}
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-info">
                            <i class="fas fa-pen"></i>
                        </div>
                    </x-slot>
                </x-adminlte-textarea>
                <small class="text-muted">Max length: 191 characters</small>
            </td>
            <td colspan="2">
                {{-- Pembimbing 1 --}}
                <label for="pembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span> </label>
                <x-adminlte-select-bs name="pembimbing1" label-class="text-black"
                    igroup-size="md" data-title="Pilih Dosen Pembimbing 1..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                    data-live-search-placeholder="Cari..." data-show-tick value="{{ old('pembimbing1') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                </x-adminlte-select-bs>

                {{-- <label for="pembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span></label>
                <x-adminlte-select name="pembimbing1">
                    <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{-- Waktu Seminar --}}
                @php
                    $configTanggal = ['format' => 'YYYY-MM-DD HH:mm'];
                @endphp
                <label for="waktuSeminar">Tanggal dan Waktu Seminar <span class="text-red">*</span></label>
                <x-adminlte-input-date id="waktuSeminar" name="waktuSeminar" :config="$configTanggal" placeholder="Pilih tanggal dan waktu ujian..." autocomplete="off" value="{{ old('waktuSeminar') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-green">
                            <i class="fas fa-clock"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input-date>
            </td>
            <td colspan="2">
                {{-- Pembimbing 2 --}}
                <label for="pembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span> </label>
                <x-adminlte-select-bs name="pembimbing2" label-class="text-black"
                    igroup-size="md" data-title="Pilih Dosen Pembimbing 2..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                    data-live-search-placeholder="Cari..." data-show-tick value="{{ old('pembimbing2') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                </x-adminlte-select-bs>

                {{-- <label for="pembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span></label>
                <x-adminlte-select name="pembimbing2">
                    <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                {{-- Dosen Pembimbing Akademik --}}
                <label for="dosenPembimbingAkademik">Dosen Pembimbing Akademik <span class="text-red">*</span> </label>
                <x-adminlte-select-bs name="dosenPembimbingAkademik" label-class="text-black"
                    igroup-size="md" data-title="Pilih Dosen Pembimbing Akademik..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                    data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPembimbingAkademik') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-purple">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('dosenPembimbingAkademik') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                </x-adminlte-select-bs>

                {{-- <label for="dosenPembimbingAkademik">Dosen Pembimbing Akademik <span class="text-red">*</span></label>
                <x-adminlte-select name="dosenPembimbingAkademik">
                    <option value="" selected disabled hidden>Pilih Dosen Pembimbing Akademik</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('dosenPembimbingAkademik') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-purple">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
            </td>
            <td colspan="2">
                {{-- Calon Penguji 1 --}}
                <label for="calonPenguji1">Calon Dosen Penguji 1 <span class="text-grey small">(opsional)</span> </label>
                <x-adminlte-select-bs name="calonPenguji1" label-class="text-black"
                    igroup-size="md" data-title="Ajukan Calon Dosen Penguji 1..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                    data-live-search-placeholder="Cari..." data-show-tick value="{{ old('calonPenguji1') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                </x-adminlte-select-bs>

                {{-- <label for="calonPenguji1">Calon Dosen Penguji 1 <span class="text-grey small">(opsional)</span> </label>
                <x-adminlte-select name="calonPenguji1">
                    <option value="" selected disabled hidden>Ajukan Calon Dosen Penguji 1</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br>
            </td>
            <td colspan="2">
                {{-- Calon Penguji 2 --}}
                <label for="calonPenguji2">Calon Dosen Penguji 2 <span class="text-grey small">(opsional)</span> </label>
                <x-adminlte-select-bs name="calonPenguji2" label-class="text-black"
                    igroup-size="md" data-title="Ajukan Calon Dosen Penguji 2..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                    data-live-search-placeholder="Cari..." data-show-tick value="{{ old('calonPenguji2') }}">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                </x-adminlte-select-bs>

                {{-- <label for="calonPenguji2">Calon Dosen Penguji 2 <span class="text-grey small">(opsional)</span> </label>
                <x-adminlte-select name="calonPenguji2">
                    <option value="" selected disabled hidden>Ajukan Calon Dosen Penguji 2</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-select> --}}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <br>
            </td>
            <td colspan="2">
                {{-- Calon Penguji 3 --}}
                <label for="calonPenguji3">Calon Dosen Penguji 3 <span class="text-grey small">(opsional)</span> </label>
                <x-adminlte-input name="calonPenguji3" placeholder="Nama calon dosen penguji di luar prodi..."  value="{{ old('calonPenguji3') }}" autocomplete="off">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-teal">
                            <i class="fas fa-user"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </td>
        </tr>
    </table>
    <table class="d-flex justify-content-center mt-3">
        <tr>
            <td >
                <label for="fileTranskripNilai">Transkrip Nilai (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="fileTranskripNilai" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf">
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
            </td>
            <td >
                <label for="fileNaskahSkripsi">Naskah Skripsi (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="fileNaskahSkripsi" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf">
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
            </td>
            <td >
                <label for="filePengesahanSkripsi">Lembar Pengesahan Skripsi (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="filePengesahanSkripsi" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf">
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
            </td>
        </tr>
        <tr>
            <td >
                <label for="filePernyataanKaryaSendiri">Surat Pernyataan Karya Sendiri (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="filePernyataanKaryaSendiri" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf">
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
            </td>
            <td >
                <label for="fileSertifikatToafl">Sertifikat TOAFL (PBA) (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="fileSertifikatToafl[]" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf" multiple>
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
                <small class="text-muted" style="max-width: 400px; word-wrap: break-word; display: block;">
                    Syarat Lulus nilai TOAFL <b>375</b>, jika belum memenuhi maka mahasiswa wajib melampirkan tiga berkas akreditasi nilai TOAFL
                </small>
            </td>
            <td>
                <label for="fileSertifikatToefl">Sertifikat TOEFL (PBI) (PDF) <span class="text-red">*</span></label>
                <x-adminlte-input-file name="fileSertifikatToefl[]" placeholder="Klik untuk upload file..."
                    disable-feedback onchange="displayFileName(this)" accept=".pdf" multiple>
                    <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                    </x-slot>
                </x-adminlte-input-file>
                <small class="text-muted" style="max-width: 400px; word-wrap: break-word; display: block;">
                    Syarat Lulus nilai TOEFL <b>450</b>, jika belum memenuhi maka mahasiswa wajib melampirkan tiga berkas akreditasi nilai TOEFL
                </small>
            </td>
        </tr>
    </table>
    <br>
    <x-adminlte-button id="submitPendaftaran" type="submit" name="submit" label="Submit" theme="primary" style="float: left; width: 20%;"/>
</form>
@stop

@push('js')
<script>
    function displayFileName(input) {
        const fileName = input.files[0]?.name || 'Klik di sini...';
        input.parentNode.querySelector('.custom-file-label').innerText = fileName;
    }

    $(document).ready(function() {
        let form = $('#formSubmitDaftarSemhas')

        $('#submitPendaftaran').on('click', function() {
            form.submit();
        });
    });

    // document.querySelector('input[name="fileSertifikatToefl[]"]').addEventListener('change', function(e) {
    //     if (this.files.length > 3) {
    //         alert('Hanya dapat mengupload maksimal 3 file.');
    //         // Clear the selected files
    //         this.value = '';
    //         // Reset the placeholder text
    //         document.querySelector('input[name="fileSertifikatToefl[]"] + div.input-group-append > input').value = 'Klik di sini...';
    //     } else {
    //         displayFileName(this);
    //     }
    // });

    // document.querySelector('input[name="fileSertifikatToafl[]"]').addEventListener('change', function(e) {
    //     if (this.files.length > 3) {
    //         alert('Hanya dapat mengupload maksimal 3 file.');
    //         // Clear the selected files
    //         this.value = '';
    //         // Reset the placeholder text
    //         document.querySelector('input[name="fileSertifikatToafl[]"] + div.input-group-append > input').value = 'Klik di sini...';
    //     } else {
    //         displayFileName(this);
    //     }
    // });
</script>
@endpush
