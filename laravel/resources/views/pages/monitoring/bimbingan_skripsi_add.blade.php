@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Input Bimbingan Skripsi</h1>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
    </div>
    <hr>
@stop

@php
    $listError = [
                'mahasiswaId',
                'judulSkripsi',
                'pembimbing',
                'sesiBimbingan',
                'tanggalBimbingan',
                'jenisBimbingan',
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

    <form action="{{ route('store.monitoring.bimbingan.skripsi') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="flex pb-4 w-50">
            <h2>Data Diri</h2>
            <table>
                @if(empty(array_intersect(['mahasiswa'], $userRole)))
                    <tr>
                        <td colspan="2">
                            <x-adminlte-select id="nilaiSemproSelect" name="nilaiSemproSelect" label="Pilih Mahasiswa">
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-id-card"></i>
                                </div>
                            </x-slot>
                            <option value="" selected disabled hidden>Pilih Mahasiswa</option>
                            @if (count($data) == 0)
                                <option value="" disabled>Tidak ada data yang tersedia</option>
                            @else
                                @foreach ($data as $sempro)
                                    <option value="{{ $sempro->id }}" {{ old('pendaftarSempro') == $sempro->mahasiswa->id ? 'selected' : '' }}>
                                        {{ $sempro->mahasiswa->nim_nip_nidn }}, {{ $sempro->mahasiswa->name }}, {{ $sempro->periodeSempro->periode }}
                                    </option>
                                @endforeach
                            @endif
                            </x-adminlte-select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h5 class="font-weight-bold">Nama Mahasiswa</h5>
                        </td>
                        <td id="namaMahasiswa">None</td>
                        <input id="mahasiswaId" type="hidden" name="mahasiswaId" value="">
                    </tr>
                    <tr>
                        <td>
                            <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
                        </td>
                        <td id="nimMahasiswa">None</td>
                    </tr>
                @else
                    <tr>
                        <td>
                            <h5 class="font-weight-bold">Nama Mahasiswa</h5>
                        </td>
                        <td>{{ $user->name }}</td>
                        <input id="mahasiswaId" type="hidden" name="mahasiswaId" value="{{ $user->id }}">
                    </tr>
                    <tr>
                        <td>
                            <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
                        </td>
                        <td id="nimMahasiswa">{{ $user->nim_nip_nidn }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <table style="background-color: transparent;">
            <tr>
                <td colspan="6">
                    <tr>
                        @if (array_intersect(['mahasiswa'], $userRole) && $bimbinganTerakhir !== null)
                            <label for="judulSkripsi">Judul Skripsi <span class="text-red">*</span></label>
                            <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" placeholder="Masukkan judul skripsi...">
                                {{ $bimbinganTerakhir->judul_skripsi }}
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-warning">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-textarea>
                        @elseif (array_intersect(['mahasiswa'], $userRole) && $bimbinganTerakhir === null)
                            <label for="judulSkripsi">Judul Skripsi <span class="text-red">*</span></label>
                            <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" placeholder="Masukkan judul skripsi...">
                                @if($data !== null || $data !== '')
                                    {{ $data->first()->judul_proposal }}
                                @endif
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-warning">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-textarea>
                        @else
                            <label for="judulSkripsi">Judul Skripsi <span class="text-red">*</span></label>
                            <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" placeholder="Masukkan judul skripsi..."  value="{{ old('judulSkripsi') }}">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-warning">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-textarea>
                        @endif
                    </tr>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <x-adminlte-input name="catatanBimbingan" label="Catatan Bimbingan" placeholder="Masukkan catatan bimbingan..."  value="{{ old('catatanBimbingan') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-info">
                                <i class="fas fa-pen"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
                <td colspan="3">
                    <label for="dosenPembimbing">Dosen Pembimbing <span class="text-red">*</span></label>
                    <x-adminlte-select name="dosenPembimbing">
                        @foreach ($namaDosen as $dosen)
                            <option value="{{ $dosen->id }}" {{ old('dosenPembimbing') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                        @endforeach
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing</option>
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    @php
                        $configDate = [
                            'format' => 'YYYY-MM-DD',
                        ];
                    @endphp
                    <label for="tanggalBimbingan">Tanggal Bimbingan <span class="text-red">*</span></label>
                    <x-adminlte-input-date name="tanggalBimbingan" :config="$configDate" placeholder="Pilih tanggal bimbingan.." value="{{ old('tanggalBimbingan') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-primary">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input-date>
                </td>
                <td colspan="2">
                    <label for="sesiBimbingan">Sesi Bimbingan <span class="text-red">*</span></label>
                    <x-adminlte-input id="sesiBimbingan" name="sesiBimbingan" placeholder="Sesi Bimbingan Ke-" type="number" value="{{ old('sesiBimbingan') }}" autocomplete="off">
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-success">
                                <i class="fas fa-sort-numeric-up"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>
                </td>
                <td colspan="2">
                    @php
                        $jenisArray = [
                            'Offline',
                            'Online',
                        ];
                    @endphp
                    <label for="jenisBimbingan">Jenis Bimbingan <span class="text-red">*</span></label>
                    <x-adminlte-select name="jenisBimbingan">
                        <option value="" selected disabled hidden>Pilih Jenis Bimbingan</option>
                        @foreach ($jenisArray as $jenis)
                            <option value="{{ $jenis }}" {{ old('jenisBimbingan') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                        @endforeach
                        <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-purple">
                                <i class="fas fa-laptop-house"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-select>
                </td>
            </tr>
        </table>
        <br>
        <x-adminlte-button type="submit" name="submit" label="Submit" theme="primary" style="float: left; width: 20%;"/>
    </form>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('#nilaiSemproSelect').change(function() {
                var nilaiSemproId = $(this).val();
                if (nilaiSemproId) {
                    // Assuming you have an endpoint to fetch data for a specific pendaftar ID
                    var apiUrl = '/api/pages/monitoring/bimbingan_skripsi/add/' + nilaiSemproId;
                    console.log(apiUrl)
                    // Make an AJAX request to fetch the data
                    $.get(apiUrl, function(data) {
                        console.log(data);
                        // Update the content of the table cells with the fetched data
                        $('#nimMahasiswa').text(data.nilaiSempro.mahasiswa.nim_nip_nidn)
                        $('#namaMahasiswa').text(data.nilaiSempro.mahasiswa.name)
                        $('#mahasiswaId').val(data.nilaiSempro.mahasiswa.id)

                        // if (data.NilaiSempro.judul_proposal) {
                        //     $('#judulSkripsi').val(data.nilaiSempro.judul_proposal)
                        // }
                        // $('#pembimbing').html('<option value="" selected>Pilih dosen pembimbing..</option>@foreach ($namaDosen as $id => $nama)<option value="{{ $id }}" {{ old('pembimbing') == $id ? 'selected' : '' }}>{{ $nama }}</option>@endforeach')
                        var pembimbingSelect = $('#pembimbing');
                        pembimbingSelect.empty();
                        pembimbingSelect.append('<option value="" selected disabled hidden>Pilih dosen pembimbing..</option>');
                        $.each(data.namaDosen, function(id, nama) {
                            pembimbingSelect.append('<option value="' + id + '">' + nama + '</option>');
                        });
                    })
                } else {
                    // If no pendaftar is selected, reset the content to "None"
                    $('#nimMahasiswa').text('None')
                    $('#namaMahasiswa').text('None')
                    $('#mahasiswaId').val('')

                    $('#judulSkripsi').val('')
                }
            });
        });
    </script>
@endpush

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop
