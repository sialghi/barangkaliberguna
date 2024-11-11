@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

{{-- @section('plugins.KrajeeFileinput', true) --}}
@section('plugins.BootstrapSelect', true)

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Pendaftaran Ujian Seminar Proposal</h1>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
    </div>
    <hr>
@stop

@php
    $listError = [
                'judulProposal',
                'periodeSempro',
                'calonPembimbing1',
                'fileTranskripNilai',
                'fileProposalSkripsi',
                ];
@endphp

@section('content')
    @if($errors->hasAny($listError))
        <div id="fail-alert"class="alert alert-danger" style="width:50%">
            @foreach($listError as $error)
                @if($errors->has($error))
                    <i class="fas fa-exclamation text-white"></i>&nbsp;&nbsp;{{ $errors->first($error) }} <br>
                @endif
            @endforeach
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('fail-alert').style.display = 'none';
            }, 10000);
        </script>
    @endif

    <form action="{{ route('store.daftar.seminar.proposal') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if (array_intersect(['mahasiswa'], $userRole))
            <div class="flex pb-4">
                <h2>Data Diri</h2>
                <table>
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
            </div>
        @else
        <label for="dataMahasiswaSelect">Data Mahasiswa <span class="text-red">*</span> </label>
            <x-adminlte-select-bs name="dataMahasiswaSelect" label-class="text-black"
                igroup-size="md" data-title="Pilih Mahasiswa..." data-live-search
                data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dataMahasiswaSelect') }}">
                <x-slot name="prependSlot">
                    <div class="input-group-text bg-gradient-info">
                        <i class="fas fa-id-card"></i>
                    </div>
                </x-slot>
                @if (count($user->listMahasiswa) == 0 || $user->listMahasiswa == null)
                    <option value="" disabled>Tidak ada data mahasiswa</option>
                @else
                    @foreach ($user->listMahasiswa as $mahasiswa)
                        <option value="{{ $mahasiswa->id }}" {{ old('dataMahasiswaSelect') == $mahasiswa->id ? 'selected' : '' }}>
                            {{ $mahasiswa->nim_nip_nidn }}, {{ $mahasiswa->name }}
                        </option>
                    @endforeach
                @endif
            </x-adminlte-select-bs>
        @endif

        {{-- With prepend slot, label and data-* config --}}


        @csrf
        <table style="background-color: transparent;">
            <tr>
                <td>
                    {{-- Judul Proposal --}}
                    <label for="judulProposal">Judul Proposal <span class="text-red">*</span></label>
                    <x-adminlte-textarea name="judulProposal" placeholder="Masukkan judul proposal..." autocomplete="off">
                        {{ old('judulProposal') }}
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-info">
                                <i class="fas fa-pen"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-textarea>
                    <small class="text-muted">Max length: 191 characters</small>
                </td>
                <td>
                    {{-- Periode Seminar --}}
                    <label for="periodeSempro">Periode Sempro <span class="text-red">*</span></label>
                    <x-adminlte-select name="periodeSempro">
                        <option value="" selected disabled hidden>Pilih Periode Pelaksanaan Sempro</option>
                            @if (count($waktuSemproLatest) == 0)
                                <option value="" disabled>Tidak ada periode yang tersedia</option>
                            @else
                                @foreach ($waktuSemproLatest as $periode)
                                    <option value="{{ $periode->id }}" {{ old('periodeSempro') == $periode->id ? 'selected' : '' }}>{{ $periode->periode }}</option>
                                @endforeach
                            @endif
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-danger">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- Calon Pembimbing 1 --}}
                    <label for="calonPembimbing1">Calon Dosen Pembimbing 1 <span class="text-red">*</span> </label>
                    <x-adminlte-select name="calonPembimbing1" id="calonPembimbing1Select">
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('calonPembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                        @endforeach
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                        <!-- Option for text input -->
                        {{-- <option value="custom" {{ old('calonPembimbing1') == 'custom' ? 'selected' : '' }}>Custom</option> --}}
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>

                    <!-- Text input for custom text -->
                    <div id="customTextInput" style="display: none;">
                        <input type="text" name="customCalonPembimbing1" class="form-control" placeholder="Masukkan Nama Dosen Pembimbing 1" autocomplete="off">
                    </div>
                </td>
                <td>
                    {{-- Calon Pembimbing 2 --}}
                    <label for="calonPembimbing2">Calon Dosen Pembimbing 2 <span class="text-grey small">(opsional)</span> </label>
                    <x-adminlte-select name="calonPembimbing2">
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('calonPembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                        @endforeach
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>
                </td>
            </tr>
            <tr>
                <td>
                    {{-- File Transkrip Nilai --}}
                    <label for="fileTranskripNilai">Transkrip Nilai (PDF) <span class="text-red">*</span></label>
                    <x-adminlte-input-file name="fileTranskripNilai" placeholder="Klik untuk upload file..."
                        disable-feedback onchange="displayFileName(this)" accept=".pdf">
                        <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-primary">
                            <i class="fas fa-file-upload"></i>
                        </x-slot>
                    </x-adminlte-input-file>
                </td>
                <td>
                    {{-- File Proposal Skripsi --}}
                    <label for="fileProposalSkripsi">Proposal Skripsi (PDF) <span class="text-red">*</span></label>
                    <x-adminlte-input-file name="fileProposalSkripsi" placeholder="Klik untuk upload file..."
                        disable-feedback onchange="displayFileName(this)" accept=".pdf">
                        <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-primary">
                            <i class="fas fa-file-upload"></i>
                        </x-slot>
                    </x-adminlte-input-file>
                </td>
            </tr>
            <tr>

            </tr>

        </table>

        <br>
        <x-adminlte-button type="submit" name="submit" label="Submit" theme="primary" style="float: left; width: 20%;"/>
    </form>
@stop

@push('js')
<script>
    function displayFileName(input) {
        const fileName = input.files[0]?.name || 'Klik di sini...';
        input.parentNode.querySelector('.custom-file-label').innerText = fileName;
    }

    function toggleCustomTextInput() {
        var selectElement = document.getElementById('calonPembimbing1Select');
        var customTextInput = document.getElementById('customTextInput');

        if (selectElement.value === 'custom') {
            customTextInput.style.display = 'block';
        } else {
            customTextInput.style.display = 'none';
        }
    }

    // Add event listener to select element
    document.getElementById('calonPembimbing1Select').addEventListener('change', toggleCustomTextInput);

    // Call the function initially
    toggleCustomTextInput();
</script>
@endpush

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop
