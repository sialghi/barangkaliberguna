@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row mb-4">
      <h1>Edit Pendaftaran Seminar Hasil</h1>
      <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
   </div>
@stop

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
                  'waktuUjian',
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

   <form id="formEditDaftarSemhas" action="{{ route('update.daftar.seminar.hasil', ['id' => $pendaftaranSemhas->id])}}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <h3>Data Mahasiswa</h3>
      <table style=" background-color: transparent;" class="w-50">
         <tr>
               {{-- NIM Mahasiswa --}}
               <td>
                  <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
               </td>
               <td>{{ $pendaftaranSemhas->mahasiswa->nim_nip_nidn }}</td>
         </tr>
         <tr>
               {{-- Nama Mahasiswa --}}
               <td>
                  <h5 class="font-weight-bold">Nama Mahasiswa</h5>
               </td>
               <td>{{ $pendaftaranSemhas->mahasiswa->name }}</td>
         </tr>
      </table>
      <table class="mt-4">
         <tr>
               <td colspan="2" class="w-50">
                  {{-- Judul Proposal --}}
                  <label for="judulSkripsi">Judul Skripsi <span class="text-red">*</span></label>
                  <x-adminlte-textarea name="judulSkripsi" placeholder="Masukkan judul skripsi..." autocomplete="off">
                     {{ old('judulSkripsi') }}{{ $pendaftaranSemhas->judul_skripsi }}
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
                  <label for="pembimbing1">Dosen Pembimbing 1 <span class="text-red">*</span></label>
                  <x-adminlte-select name="pembimbing1">
                     @if ($pendaftaranSemhas->pembimbing1)
                        <option value="{{ $pendaftaranSemhas->pembimbing1->id }}" selected disabled>{{ $pendaftaranSemhas->pembimbing1->name }}</option>
                     @else
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                     @endif
                     @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-teal">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
         </tr>
         <tr>
               <td colspan="2">
                  {{-- Waktu Ujian --}}
                  @php
                     $configTanggal = ['format' => 'YYYY-MM-DD HH:mm'];
                  @endphp
                  <label for="waktuUjian">Tanggal dan Waktu Ujian <span class="text-red">*</span></label>
                  <x-adminlte-input-date id="waktuUjian" name="waktuUjian" :config="$configTanggal" placeholder="Pilih tanggal dan waktu ujian..." autocomplete="off" value="{{$pendaftaranSemhas->waktu_seminar}}">
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-green">
                              <i class="fas fa-clock"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-input-date>
               </td>
               <td colspan="2">
                  {{-- Pembimbing 2 --}}
                  <label for="pembimbing2">Dosen Pembimbing 2 <span class="text-red">*</span></label>
                  <x-adminlte-select name="pembimbing2">
                     @if ($pendaftaranSemhas->pembimbing2)
                        <option value="{{ $pendaftaranSemhas->pembimbing2->id }}" selected disabled>{{ $pendaftaranSemhas->pembimbing2->name }}</option>
                     @else
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                     @endif
                     @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('pembimbing2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-teal">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
         </tr>
         <tr>
               <td colspan="2">
                  {{-- Dosen Pembimbing Akademik --}}
                  <label for="dosenPembimbingAkademik">Dosen Pembimbing Akademik <span class="text-red">*</span></label>
                  <x-adminlte-select name="dosenPembimbingAkademik">
                     @if ($pendaftaranSemhas->dosenPembimbingAkademik)
                        <option value="{{ $pendaftaranSemhas->dosenPembimbingAkademik->id }}" selected disabled>{{ $pendaftaranSemhas->dosenPembimbingAkademik->name }}</option>
                     @else
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing Akademik</option>
                     @endif
                     @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('dosenPembimbingAkademik') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-purple">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
               <td colspan="2">
                  {{-- Calon Penguji 1 --}}
                  <label for="calonPenguji1">Calon Dosen Penguji 1</label>
                  <x-adminlte-select name="calonPenguji1">
                     @if ($pendaftaranSemhas->calonPenguji1)
                        <option value="{{ $pendaftaranSemhas->calonPenguji1->id }}" selected disabled>{{ $pendaftaranSemhas->calonPenguji1->name }}</option>
                     @else
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing Akademik</option>
                     @endif
                     @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-teal">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
         </tr>
         <tr>
               <td colspan="2">
                  <br>
               </td>
               <td colspan="2">
                  {{-- Calon Penguji 2 --}}
                  <label for="calonPenguji2">Calon Dosen Penguji 2</label>
                  <x-adminlte-select name="calonPenguji2">
                     @if ($pendaftaranSemhas->calonPenguji2)
                        <option value="{{ $pendaftaranSemhas->calonPenguji2->id }}" selected disabled>{{ $pendaftaranSemhas->calonPenguji2->name }}</option>
                     @else
                        <option value="" selected disabled hidden>Pilih Dosen Pembimbing Akademik</option>
                     @endif
                     @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('calonPenguji2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-teal">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
         </tr>
         <tr>
               <td colspan="2">
                  <br>
               </td>
               <td colspan="2">
                  {{-- Calon Penguji 3 --}}
                  <label for="calonPenguji3">Calon Dosen Penguji 3</label>
                  <x-adminlte-input name="calonPenguji3" placeholder="Nama calon dosen penguji di luar prodi..."  value="{{$pendaftaranSemhas->calon_penguji_3_name}}" autocomplete="off">
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
                  <label class="mb-1" id="fileTranskripNilai" for="fileTranskripNilai">Transkrip Nilai (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                  <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_transkrip_nilai }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_transkrip_nilai) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_transkrip_nilai) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                  <x-adminlte-input-file name="fileTranskripNilai" placeholder="Klik di sini untuk mengubah file..."
                     disable-feedback onchange="displayFileName(this)" accept=".pdf">
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  <label class="mb-1" id="fileNaskahSkripsi" for="fileNaskahSkripsi">Naskah Skripsi (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                  <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_naskah_skripsi }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_naskah_skripsi) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_naskah_skripsi) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                  <x-adminlte-input-file name="fileNaskahSkripsi" placeholder="Klik di sini untuk mengubah file..."
                     disable-feedback onchange="displayFileName(this)" accept=".pdf">
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  <label class="mb-1" id="filePengesahanSkripsi" for="filePengesahanSkripsi">Lembar Pengesahan Skripsi (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                  <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_pengesahan_skripsi }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_pengesahan_skripsi) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_pengesahan_skripsi) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                  <x-adminlte-input-file name="filePengesahanSkripsi" placeholder="Klik di sini untuk mengubah file..."
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
                  {{-- File Pernyataan Karya Sendiri --}}
                  {{-- <div class="">
                     <label for="filePernyataanKaryaSendiri">Surat Pernyataan Karya Sendiri (PDF) <span class="text-red">*</span></label>
                     <x-adminlte-input-file-krajee name="filePernyataanKaryaSendiri"
                           data-msg-placeholder="Pilih gambar..."
                           label-class="text-primary"
                           :config="$configPdf"
                           preset-mode="minimalist"
                           accept=".pdf"
                           disable-feedback/>
                  </div> --}}
                  <label class="mb-1" id="filePernyataanKaryaSendiri" for="filePernyataanKaryaSendiri">Surat Pernyataan Karya Sendiri (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                  <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_pernyataan_karya_sendiri }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_pernyataan_karya_sendiri) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_pernyataan_karya_sendiri) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                  <x-adminlte-input-file name="filePernyataanKaryaSendiri" placeholder="Klik di sini untuk mengubah file..."
                     disable-feedback onchange="displayFileName(this)" accept=".pdf">
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  {{-- File Sertifikat TOAFL --}}
                  {{-- <div class="">
                     <label for="fileSertifikatToafl">Sertifikat TOAFL (PBA) (PDF) <span class="text-red">*</span></label>
                     <x-adminlte-input-file-krajee name="fileSertifikatToafl"
                           data-msg-placeholder="Pilih gambar..."
                           label-class="text-primary"
                           :config="$configPdfMultiple"
                           preset-mode="minimalist"
                           accept=".pdf"
                           disable-feedback
                           multiple/>
                  </div> --}}
                  <label class="mb-1" id="fileSertifikatToafl" for="fileSertifikatToafl">Sertifikat TOAFL (PBA) (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                  @if ($pendaftaranSemhas->file_sertifikat_toafl_3)
                     @for ($i = 1; $i <= 3; $i++)
                        <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas['file_sertifikat_toafl_'.$i] }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas['file_sertifikat_toafl_'.$i]) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas['file_sertifikat_toafl_'.$i]) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                     @endfor
                  @else
                     <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_sertifikat_toafl_1 }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_sertifikat_toafl_1) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_sertifikat_toafl_1) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                  @endif
                  <x-adminlte-input-file name="fileSertifikatToafl[]" placeholder="Klik di sini untuk mengubah file..."
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
                  <label class="mb-1" id="fileSertifikatToefl" for="fileSertifikatToefl">Sertifikat TOEFL (PBI) (PDF) <span class="text-red">*</span>
                     <br>
                     <span class="text-xs">File sekarang:</span>
                  </label>
                     @if ($pendaftaranSemhas->file_sertifikat_toefl_3)
                        @for ($i = 1; $i <= 3; $i++)
                           <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas['file_sertifikat_toefl_'.$i] }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas['file_sertifikat_toefl_'.$i]) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas['file_sertifikat_toefl_'.$i]) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                        @endfor
                     @else
                        <a href="/api/seminar_hasil/daftar/berkas/{{ $pendaftaranSemhas->file_sertifikat_toefl_1 }}" target="_blank" class="btn btn-default px-1 d-flex align-items-center mb-1" style="max-width: 220px;" title="{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_sertifikat_toefl_1) }}"><span style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ preg_replace('/^\d+_\d+_/', '', $pendaftaranSemhas->file_sertifikat_toefl_1) }}</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>
                     @endif
                  </label>
                  <x-adminlte-input-file name="fileSertifikatToefl[]" placeholder="Klik di sini untuk mengubah file..."
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
      <div class="flex py-4">
         <a href="{{ url()->previous() }}" class="btn btn-danger">Batalkan</a>
         <button id="submitButton" type="submit" class="btn btn-primary ml-2">Submit</button>
      </div>
   </form>
@stop

@push('js')
   <script>
      function displayFileName(input) {
         const fileName = input.files[0]?.name || 'Klik di sini...';
         input.parentNode.querySelector('.custom-file-label').innerText = fileName;
      }
   </script>
@endpush
