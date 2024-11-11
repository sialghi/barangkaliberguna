@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

{{-- @section('plugins.KrajeeFileinput', true) --}}

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Pendaftaran MBKM</h1>
      <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
   </div>
   <hr>
@stop

@php
   $listError = [
               'jenisMbkm',
               'dosenPembimbing',
               'learningPath',
               'jumlahSks',
               'mkKonversi',
               'fileKomitmen',
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

   <form action="{{ route('store.daftar.mbkm') }}" method="POST" enctype="multipart/form-data">
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

      @csrf
      <table style="background-color: transparent;">
         <tr>
               <td>
                  {{-- Jenis MBKM --}}
                  <label for="jenisMbkm">Jenis MBKM <span class="text-red">*</span></label>
                  <x-adminlte-select name="jenisMbkm" id="jenisMbkm">
                     <option value="" selected disabled hidden>Pilih Jenis MBKM</option>
                     <option value="Pertukaran Pelajar" {{ old('jenisMbkm') == 'Pertukaran Pelajar' ? 'selected' : '' }}>Pertukaran Pelajar</option>
                     <option value="Magang" {{ old('jenisMbkm') == 'Magang' ? 'selected' : '' }}>Magang</option>
                     <option value="Transfer SKS" {{ old('jenisMbkm') == 'Transfer SKS' ? 'selected' : '' }}>Transfer SKS</option>
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-purple">
                              <i class="fas fa-list"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
               <td>
                  {{-- Dosen Pembimbing --}}
                  <label for="dosenPembimbing">Dosen Pembimbing <span class="text-red">*</span></label>
                  <x-adminlte-select name="dosenPembimbing">
                  <option value="" selected disabled hidden>Pilih Dosen Pembimbing</option>
                    @foreach ($namaDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ old('dosenPembimbing') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                    @endforeach
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-dark">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
         </tr>
         <tr id="mitraDisplay" style="display: none;">
            <td>
               {{-- Mitra --}}
               <label for="mitra">Mitra <span class="text-red">*</span></label>
               <x-adminlte-input name="mitra" id="mitra" placeholder="Masukkan mitra..."  value="{{ old('mitra') }}" autocomplete="off">
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-info">
                           <i class="fas fa-pen"></i>
                     </div>
                  </x-slot>
               </x-adminlte-input>
            </td>
            <td>
               {{-- Learning Path --}}
               <label for="learningPath">Learning Path <span class="text-grey small">(opsional)</span> </label>
               <x-adminlte-input name="learningPath" id="learningPath" placeholder="Masukkan learning path..."  value="{{ old('learningPath') }}" autocomplete="off">
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-info">
                           <i class="fas fa-pen"></i>
                     </div>
                  </x-slot>
               </x-adminlte-input>
            </td>
         </tr>
         <tr>
            <td>
               {{-- Mata Kuliah Dikonversi --}}
               <label for="mkKonversi">Mata Kuliah yand Dikonversi <span class="text-red">*</span></label>
               <x-adminlte-textarea name="mkKonversi" placeholder="Masukkan mata kuliah yang akan dikonversi..." autocomplete="off">
                  {{ old('mkKonversi') }}
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-info">
                           <i class="fas fa-pen"></i>
                     </div>
                  </x-slot>
               </x-adminlte-textarea>
               <small class="text-muted">Pisah mata kuliah menggunakan koma ' , ', dengan maksimal 191 karakter</small>
            </td>
            <td>
               {{-- NIM --}}
               <label for="jumlahSks">Total SKS <span class="text-red">*</span></label>
               <x-adminlte-input name="jumlahSks" id="jumlahSks" placeholder="Masukkan total sks yang dikonversi" type="number" value="{{ old('jumlahSks') }}" autocomplete="off">
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
               {{-- File Pernyataan Kesanggupan/Komitmen --}}
               <label for="fileKomitmen">File Pernyataan Kesanggupan/Komitmen (PDF) <span class="text-red">*</span></label>
               <x-adminlte-input-file name="fileKomitmen" placeholder="Klik untuk upload file..."
                  disable-feedback onchange="displayFileName(this)" accept=".pdf">
                  <x-slot name="prependSlot">
                  <div class="input-group-text bg-gradient-primary">
                        <i class="fas fa-file-upload"></i>
                  </x-slot>
               </x-adminlte-input-file>
            </td>
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
   $(document).ready(function() {
      $('#jenisMbkm').change(function() {
            // var selectedType = $(this).val();
            $('#mitraDisplay').show();
      });
      var oldTypeValue = "{{ old('jenisMbkm') }}";
      if (oldTypeValue) {
            $('#jenisMbkm').val(oldTypeValue).change();
      }
   });
</script>
@endpush

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop
