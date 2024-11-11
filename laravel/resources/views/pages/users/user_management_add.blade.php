@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('plugins.KrajeeFileinput', true)

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Tambah Pengguna Baru</h1>
      <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
   </div>
   <hr>
@stop

@php
   $listError = [
               'namaUser',
               'nimNipNidnUser',
               'emailUser',
               'noHp',
               'roleUser',
               'passwordUser',
               'ttdUser',
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

   <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <table style="background-color: transparent;">
            <tr>
               <td>
                  <label for="namaUser">Nama <span class="text-red">*</span></label>
                  <x-adminlte-input id="namaUser" name="namaUser" placeholder="Input nama.." value="{{ old('namaUser') }}">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <label for="nimNipNidnUser">NIM/NIP/NIDN <span class="text-red">*</span></label>
                  <x-adminlte-input id="nimNipNidnUser" name="nimNipNidnUser" type="number" placeholder="Input NIM/NIP/NIDN.." value="{{ old('nimNipNidnUser') }}">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-id-card"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="emailUser">Email <span class="text-red">*</span></label>
                  <x-adminlte-input id="emailUser" name="emailUser" type="email" placeholder="Input email.." value="{{ old('emailUser') }}">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-envelope"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <label for="noHp">Nomor Telepon</label>
                  <x-adminlte-input id="noHp" name="noHp" type="number" placeholder="62xxxxxxxxxxx..." value="{{ old('noHp') }}">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-phone"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="roleUser">Role <span class="text-red">*</span></label>
                  <x-adminlte-select id="roleUser" name="roleUser">
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark text-white">
                              <i class="fas fa-user-tag"></i>
                        </div>
                     </x-slot>
                     <option value="" selected disabled hidden>Pilih role pengguna</option>
                     @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ old('roleUser') == $role->id ? 'selected' : '' }}>{{ $role->nama }}</option>
                    @endforeach
                  </x-adminlte-select>
               </td>
               <td>
                <div id="prodiUserDiv">
                    <label for="prodiUser">Program Studi <span class="text-red">*</span></label>
                    <x-adminlte-select id="prodiUser" name="prodiUser">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark text-white">
                                <i class="fas fa-building"></i>
                        </div>
                    </x-slot>
                    <option value="" selected disabled hidden>Pilih program studi pengguna</option>
                    @foreach ($programStudi as $prodi)
                        <option value="{{ $prodi->id }}" {{ old('prodiUser') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                    @endforeach
                    </x-adminlte-select>
                </div>
                <div id="fakultasUserDiv" style="display: none;">
                    <label for="fakultasUser">Fakultas <span class="text-red">*</span></label>
                    <x-adminlte-select id="fakultasUser" name="fakultasUser">
                    <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark text-white">
                                <i class="fas fa-building"></i>
                        </div>
                    </x-slot>
                    <option value="" selected disabled hidden>Pilih fakultas pengguna</option>
                    @foreach ($fakultas as $fak)
                        <option value="{{ $fak->id }}" {{ old('fakultasUser') == $role->id ? 'selected' : '' }}>{{ $fak->nama }}</option>
                    @endforeach
                    </x-adminlte-select>
                </div>
               </td>
            </tr>
            <tr id="roleAddRow">
                <td>
                    <button class="btn btn-warning">Tambah Role</button>
                </td>
            </tr>
            <tr id="role2" style="display: none">
                <td>
                    <label for="roleUser2">Role <span class="text-red">*</span></label>
                    <x-adminlte-select id="roleUser2" name="roleUser2">
                       <x-slot name="prependSlot">
                          <div class="input-group-text bg-gradient-dark text-white">
                                <i class="fas fa-user-tag"></i>
                          </div>
                       </x-slot>
                       <option value="" selected disabled hidden>Pilih role pengguna</option>
                       @foreach ($roles as $role)
                          <option value="{{ $role->id }}" {{ old('roleUser2') == $role->id ? 'selected' : '' }}>{{ $role->nama }}</option>
                      @endforeach
                    </x-adminlte-select>
                 </td>
                 <td>
                  <div id="prodiUserDiv2">
                      <label for="prodiUser2">Program Studi <span class="text-red">*</span></label>
                      <x-adminlte-select id="prodiUser2" name="prodiUser2">
                      <x-slot name="prependSlot">
                          <div class="input-group-text bg-gradient-dark text-white">
                                  <i class="fas fa-building"></i>
                          </div>
                      </x-slot>
                      <option value="" selected disabled hidden>Pilih program studi pengguna</option>
                      @foreach ($programStudi as $prodi)
                          <option value="{{ $prodi->id }}" {{ old('prodiUser2') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                      @endforeach
                      </x-adminlte-select>
                  </div>
                  <div id="fakultasUserDiv2" style="display: none;">
                      <label for="fakultasUser2">Fakultas <span class="text-red">*</span></label>
                      <x-adminlte-select id="fakultasUser2" name="fakultasUser2">
                      <x-slot name="prependSlot">
                          <div class="input-group-text bg-gradient-dark text-white">
                                  <i class="fas fa-building"></i>
                          </div>
                      </x-slot>
                      <option value="" selected disabled hidden>Pilih fakultas pengguna</option>
                      @foreach ($fakultas as $fak)
                          <option value="{{ $fak->id }}" {{ old('fakultasUser2') == $role->id ? 'selected' : '' }}>{{ $fak->nama }}</option>
                      @endforeach
                      </x-adminlte-select>
                  </div>
                 </td>
            </tr>
            <tr>
                <td>
                    <label for="passwordUser">Password <span class="text-red">*</span></label>
                    <x-adminlte-input id="passwordUser" name="passwordUser" type="password" placeholder="Input password..">
                          <x-slot name="prependSlot">
                             <div class="input-group-text bg-gradient-dark text-white">
                                <i class="fas fa-key"></i>
                             </div>
                          </x-slot>
                    </x-adminlte-input>
                 </td>
            </tr>
            <tr>
               <td>
                  @php
                     $configTtd = [
                        'allowedFileTypes' => ['image'],
                        'browseOnZoneClick' => true,
                        'theme' => 'explorer-fa5',
                     ];
                  @endphp
                  <label for="ttdUser">Tanda Tangan</label>
                  <x-adminlte-input-file-krajee name="ttdUser"
                     data-msg-placeholder="Pilih gambar..."
                     label-class="text-primary" :config="$configTtd" accept="image/*" disable-feedback/>
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
            $('#roleUser').change(function() {
                var role = $(this).val();
                if(role == 1 || role == 2 || role == 3 || role == 4 || role == 5) {
                    $('#prodiUserDiv').hide();
                    $('#fakultasUserDiv').show();
                } else {
                    $('#prodiUserDiv').show();
                    $('#fakultasUserDiv').hide();
                }
            });
        });
    </script>
@endpush
