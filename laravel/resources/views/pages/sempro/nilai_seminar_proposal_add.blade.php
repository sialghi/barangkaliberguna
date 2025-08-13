@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('plugins.BootstrapSelect', true)

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Input Nilai Seminar Proposal</h1>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@stop

@php
    $listError = [
                'pendaftarSemproSelect',
                'dosenPenguji1',
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
            }, 4000);
        </script>
    @endif

    <form method="POST" action="{{ route('store.nilai.seminar.proposal') }}">
        @csrf
        <label for="pendaftarSemproSelect">Data Mahasiswa <span class="text-red">*</span> </label>
        <x-adminlte-select-bs name="pendaftarSemproSelect" label-class="text-black"
            igroup-size="md" data-title="Pilih Mahasiswa..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('pendaftarSemproSelect') }}">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-info">
                    <i class="fas fa-id-card"></i>
                </div>
            </x-slot>
            @if (count($pendaftarSempro) == 0)
                <option value="" disabled>Tidak ada data mahasiswa pendaftar seminar proposal</option>
            @else
                @foreach ($pendaftarSempro as $data)
                <option value="{{ $data->id }}" {{ old('pendaftarSemproSelect') == $data->id ? 'selected' : '' }}>
                    {{ $data->periodeSempro->periode }}, {{ $data->mahasiswa->name }}, {{ $data->mahasiswa->nim_nip_nidn }}
                </option>
                @endforeach
            @endif
        </x-adminlte-select-bs>

        {{-- <x-adminlte-select id="pendaftarSemproSelect" name="pendaftarSemproSelect" label="Data Mahasiswa">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-info">
                    <i class="fas fa-id-card"></i>
                </div>
            </x-slot>
            <option value="" selected disabled hidden>Pilih Pendaftar</option>
            @if (count($pendaftarSempro) == 0)
                <option value="" disabled>Tidak ada data mahasiswa pendaftar seminar proposal</option>
            @else
                @foreach ($pendaftarSempro as $data)
                    <option value="{{ $data->id }}" {{ old('pendaftarSemproSelect') == $data->id ? 'selected' : '' }}>
                        {{ $data->periodeSempro->periode }}, {{ $data->mahasiswa->name }}, {{ $data->mahasiswa->nim_nip_nidn }}
                    </option>
                @endforeach
            @endif
        </x-adminlte-select> --}}
        <input id="pendaftarSemproId" type="hidden" name="pendaftarSemproId" value="">
        <div class="d-flex flex-row">
            <table style="background-color: transparent;">
                <tr>
                    <td colspan="2">
                        <h2>Data Diri</h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            NIM/NIP/NIDN
                        </h5>
                    </td>
                    <td id="nimMahasiswa">
                        None
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Nama Mahasiswa
                        </h5>
                    </td>
                    <td>
                        <input id="namaMahasiswa" type="text" name="namaMahasiswa" value="None" class="bg-transparent border-0 text-dark" disabled>
                        <input id="namaMahasiswaId" type="hidden" name="namaMahasiswaId" value="">
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Nomor Telepon
                        </h5>
                    </td>
                    <td id="teleponMahasiswa">
                        None
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h2>Data Proposal Tugas Akhir</h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Judul Proposal
                        </h5>
                    </td>
                    <td>
                        <input id="judulProposal" type="textarea" name="judulProposal" value="None" class="bg-transparent border-0 text-dark w-100 " disabled>
                        <input type="hidden" id="judulProposalHidden" name="judulProposalHidden" value="">
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Periode
                        </h5>
                    </td>
                    <td>
                        <input id="periodeProposal" type="text" name="periodeProposal" value="None" class="bg-transparent border-0 text-dark" disabled>
                        <input id="periodeProposalId" type="hidden" name="periodeProposalId" value="">
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Calon Dosen Pembimbing 1
                        </h5>
                    </td>
                    <td>
                        <input id="calonDosenPembimbing1" type="text" name="calonDosenPembimbing1" value="None" class="bg-transparent border-0 text-dark" disabled>
                        <input id="calonDosenPembimbing1Id" type="hidden" name="calonDosenPembimbing1Id" value="">
                    </td>
                </tr>
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Calon Dosen Pembimbing 2
                        </h5>
                    </td>
                    <td>
                        <input id="calonDosenPembimbing2" type="text" name="calonDosenPembimbing2" value="None" class="bg-transparent border-0 text-dark" disabled>
                        <input id="calonDosenPembimbing2Id" type="hidden" name="calonDosenPembimbing2Id" value="">
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <h2>Data Penguji</h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPenguji1">Dosen Penguji 1 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPenguji1" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Penguji 1..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPenguji1') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <label for="dosenPenguji1">Dosen Penguji 1 <span class="text-red">*</span></label>
                        <x-adminlte-select name="dosenPenguji1">
                            <option value="" selected disabled hidden>Pilih Dosen Penguji 1</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPenguji2">Dosen Penguji 2 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPenguji2" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Penguji 2..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPenguji2') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <x-adminlte-select name="dosenPenguji2" label="Dosen Penguji 2">
                            <option value="" selected disabled hidden>Pilih Dosen Penguji 2</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPenguji3">Dosen Penguji 3 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPenguji3" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Penguji 3..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPenguji3') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji3') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <x-adminlte-select name="dosenPenguji3" label="Dosen Penguji 3">
                            <option value="" selected disabled hidden>Pilih Dosen Penguji 3</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji3') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPenguji4">Dosen Penguji 4 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPenguji4" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Penguji 4..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPenguji4') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji4') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <x-adminlte-select name="dosenPenguji4" label="Dosen Penguji 4">
                            <option value="" selected disabled hidden>Pilih Dosen Penguji 4</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPenguji4') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <h2>Data Pembimbing</h2>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPembimbing1" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Pembimbing 1..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPembimbing1') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <x-adminlte-select name="dosenPembimbing1" label="Dosen Pembimbing 1">
                            <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="dosenPembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span> </label>
                        <x-adminlte-select-bs name="dosenPembimbing2" label-class="text-black"
                            igroup-size="md" data-title="Pilih Dosen Pembimbing 2..." data-live-search data-style='border: 1px solid #ced4da; background-color: #fff;'
                            data-live-search-placeholder="Cari..." data-show-tick value="{{ old('dosenPembimbing2') }}">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                        </x-adminlte-select-bs>

                        {{-- <x-adminlte-select name="dosenPembimbing2" label="Dosen Pembimbing 2">
                            <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('dosenPembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select> --}}
                    </td>
                </tr>
            </table>
        </div>
        <br>
        <x-adminlte-button type="submit" name="submit" label="Submit" theme="primary" style="float: left; width: 20%;"/>
    </form>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('#pendaftarSemproSelect').change(function() {
                var pendaftarId = $(this).val();
                if (pendaftarId) {
                    // Assuming you have an endpoint to fetch data for a specific pendaftar ID
                    var apiUrl = '/api/seminar_proposal/daftar/detail/' + pendaftarId;

                    // Make an AJAX request to fetch the data
                    $.get(apiUrl, function(data) {
                        // console.log(data);
                        // Update the content of the table cells with the fetched data
                        $('#nimMahasiswa').text(data.mahasiswa.nim_nip_nidn)
                        $('#namaMahasiswa').val(data.mahasiswa.name)
                        $('#namaMahasiswaId').val(data.mahasiswa.id)
                        $('#teleponMahasiswa').text(data.mahasiswa.no_hp)

                        $('#judulProposal').val(data.judul_proposal)
                        $('#judulProposalHidden').val(data.judul_proposal)
                        // console.log(data.judul_proposal)
                        $('#periodeProposal').val(data.periode_sempro.periode)
                        $('#periodeProposalId').val(data.periode_sempro.id)

                        $('#calonDosenPembimbing1').val(data.calon_dospem1.name)
                        $('#calonDosenPembimbing1Id').val(data.calon_dospem1.id)

                        $('#calonDosenPembimbing2').val(data.calon_dospem2.name)
                        $('#calonDosenPembimbing2Id').val(data.calon_dospem2.id)
                    })
                } else {
                    // If no pendaftar is selected, reset the content to "None"
                    $('#nimMahasiswa').text('None')
                    $('#namaMahasiswa').val('None')
                    $('#namaMahasiswaId').val('')
                    $('#teleponMahasiswa').text('None')

                    $('#judulProposal').val('None')
                    $('#periodeProposal').val('None')
                    $('#periodeProposalId').val('')

                    $('#calonDosenPembimbing1').val('None')
                    $('#calonDosenPembimbing1Id').val('')

                    $('#calonDosenPembimbing2').val('None')
                    $('#calonDosenPembimbing2Id').val('')
                }
            });

            var oldValue = "{{ old('pendaftarSemproSelect') }}";
            if (oldValue) {
                $('#pendaftarSemproSelect').val(oldValue).change();
            }
        });
    </script>
@endpush

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop
