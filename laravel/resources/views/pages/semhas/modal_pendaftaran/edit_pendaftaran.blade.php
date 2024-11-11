<x-adminlte-modal id="editPendaftaran" title="Detail Pendaftaran" theme="blue" size='lg'>
   <div class="modal-body">
      <form id="editFormPendaftaran" method="POST" enctype="multipart/form-data">
         @csrf
         @method('PUT')
         <table style=" background-color: transparent;" class="w-50">
            <tr>
                  {{-- NIM Mahasiswa --}}
                  <td>
                     <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
                  </td>
                  <td id="mahasiswaNim">None</td>
            </tr>
            <tr>
                  {{-- Nama Mahasiswa --}}
                  <td>
                     <h5 class="font-weight-bold">Nama Mahasiswa</h5>
                  </td>
                  <td id="mahasiswaNama">None</td>
            </tr>
         </table>
         <table class="mt-4">
            <tr>
               <td colspan="2" class="w-50">
                  {{-- Judul Proposal --}}
                  <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" label="Judul Skripsi" placeholder="Masukkan judul skripsi..."  value="{{ old('judulSkripsi') }}" autocomplete="off" >
                     None
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-textarea>
               </td>
               <td colspan="2">
                  {{-- Pembimbing 1 --}}
                  <x-adminlte-select id="pembimbing1" name="pembimbing1" label="Dosen Pembimbing 1" >
                     <option value="" selected disabled hidden>None</option>
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
                  <x-adminlte-select id="pembimbing2" name="pembimbing2" label="Dosen Pembimbing 2" >
                     <option value="" selected disabled hidden>None</option>
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
                  <x-adminlte-select id="dosenPembimbingAkademik" name="dosenPembimbingAkademik" label="Dosen Pembimbing Akademik" >
                     <option value="" selected disabled hidden>None</option>
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-purple">
                              <i class="fas fa-user"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-select>
               </td>
               <td colspan="2">
                  {{-- Calon Penguji 1 --}}
                  <x-adminlte-select id="calon_penguji1" name="calon_penguji1" label="Dosen Penguji 1" >
                     <option value="" selected disabled hidden>None</option>
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
                  <x-adminlte-select id="calon_penguji2" name="calon_penguji2" label="Dosen Penguji 2" >
                     <option value="" selected disabled hidden>None</option>
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
                  <x-adminlte-input id="calonPenguji3" name="calonPenguji3" label="Calon Dosen Penguji 3" placeholder="Calon dosen penguji di luar prodi..."  value="None" autocomplete="off" >
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
                  {{-- File Transkrip Nilai --}}
                  <label id="fileTranskripNilai" for="fileTranskripNilai">Transkrip Nilai (PDF)</label>
                  <x-adminlte-input-file name="fileTranskripNilai" placeholder="Disabled"
                     disable-feedback onchange="displayFileName(this)" accept=".pdf" >
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  {{-- File Sertifikat TOEFL --}}
                  <label id="fileSertifikatToefl" for="fileSertifikatToefl">Sertifikat TOEFL (PBI) (PDF)</label>
                  <x-adminlte-input-file name="fileSertifikatToefl[]" placeholder="Disabled"
                     disable-feedback accept=".pdf" multiple >
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  {{-- File Pengesahan Skripsi --}}
                  <label id="filePengesahanSkripsi" for="filePengesahanSkripsi">Lembar Pengesahan Skripsi (PDF)</label>
                  <x-adminlte-input-file name="filePengesahanSkripsi" placeholder="Disabled"
                     disable-feedback onchange="displayFileName(this)" accept=".pdf" >
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
                  <label id="filePernyataanKaryaSendiri" for="filePernyataanKaryaSendiri">Surat Pernyataan Karya Sendiri (PDF)</label>
                  <x-adminlte-input-file name="filePernyataanKaryaSendiri" placeholder="Disabled"
                     disable-feedback onchange="displayFileName(this)" accept=".pdf" >
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td >
                  {{-- File Sertifikat TOAFL --}}
                  <label id="fileSertifikatToafl" for="fileSertifikatToafl">Sertifikat TOAFL (PBA) (PDF)</label>
                  <x-adminlte-input-file name="fileSertifikatToafl[]" placeholder="Disabled"
                     disable-feedback accept=".pdf" multiple >
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
               <td>
                  {{-- File Naskah Skripsi --}}
                  <label id="fileNaskahSkripsi" for="fileNaskahSkripsi">Naskah Skripsi (PDF)</label>
                  <x-adminlte-input-file name="fileNaskahSkripsi" placeholder="Disabled"
                     disable-feedback onchange="displayFileName(this)" accept=".pdf" >
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
            </tr>
         </table>
         <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
            <button type="submit" id="editButton" class="btn btn-success">Simpan</button>
         </x-slot>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function(){
         $('#editPendaftaran').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget)
            let modal = $(this)

            let rowId = button.data('row-id')
            let apiUrl = '/api/seminar_hasil/daftar/detail/' + rowId

            $.get(apiUrl, function (data) {
               modal.find('#mahasiswaNim').text(data.pendaftaranSemhas.mahasiswa.nim_nip_nidn)
               modal.find('#mahasiswaNama').text(data.pendaftaranSemhas.mahasiswa.name)

               modal.find('#judulSkripsi').val(data.pendaftaranSemhas.judul_skripsi)
               modal.find('#waktuSeminar').val(data.pendaftaranSemhas.waktuSeminar)

               let listDosen = ['dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calon_penguji1', 'calon_penguji2']
               for (var item of listDosen) {
                  if (item == 'dosenPembimbingAkademik') {
                     modal.find('#'+ item).html('<option value="'+ data.pendaftaranSemhas['dosen_pembimbing_akademik'].id +'" selected disabled hidden>'+ data.pendaftaranSemhas['dosen_pembimbing_akademik'].name +'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                  } else if (data.pendaftaranSemhas[item] != null) {
                     modal.find('#'+ item).html('<option value="'+ data.pendaftaranSemhas[item].id +'" selected disabled hidden>'+ data.pendaftaranSemhas[item].name +'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                  } else if (data.pendaftaranSemhas[item] == null) {
                     modal.find('#'+ item).html('<option value="" selected disabled hidden>None</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('pembimbing1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                  }
               }

               if (data.pendaftaranSemhas.penguji3) {
                  modal.find('#calonPenguji3').val(data.pendaftaranSemhas.penguji3)
               }

               modal.find('#fileTranskripNilai').html('Transkrip Nilai (PDF)')
               modal.find('#filePengesahanSkripsi').html('Lembar Pengesahan Skripsi (PDF)')
               modal.find('#filePernyataanKaryaSendiri').html('Surat Pernyataan Karya Sendiri (PDF)')
               modal.find('#fileNaskahSkripsi').html('Naskah Skripsi (PDF)')
               modal.find('#fileSertifikatToefl').html('Sertifikat TOEFL (PBI) (PDF)')
               modal.find('#fileSertifikatToafl').html('Sertifikat TOAFL (PBA) (PDF)')

               let listFile = [
                  { 'fileTranskripNilai': 'file_transkrip_nilai' },
                  { 'filePengesahanSkripsi': 'file_pengesahan_skripsi' },
                  { 'filePernyataanKaryaSendiri': 'file_pernyataan_karya_sendiri' },
                  { 'fileNaskahSkripsi': 'file_naskah_skripsi' },
                  { 'fileSertifikatToefl': ['file_sertifikat_toefl_1', 'file_sertifikat_toefl_2', 'file_sertifikat_toefl_3', ] },
                  { 'fileSertifikatToafl': ['file_sertifikat_toafl_1', 'file_sertifikat_toafl_2', 'file_sertifikat_toafl_3', ] }
               ]
               listFile.forEach(fileObject => {
                  for (let key in fileObject) {
                     let value = fileObject[key];
                     if (Array.isArray(value)) {
                        for (let j = 0; j < value.length; j++) {
                           if (data.pendaftaranSemhas[value[j]]) {
                              modal.find('#'+ key).append('<br>Current File:<br><a href="/api/seminar_hasil/daftar/berkas/'+ data.pendaftaranSemhas[value[j]] +'" target="_blank" style="display: inline-block; width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + data.pendaftaranSemhas[value[j]] + '</a>')
                           }
                        }
                     } else {
                        modal.find('#'+ key).append('<br>Current File:<br><a href="/api/seminar_hasil/daftar/berkas/'+ data.pendaftaranSemhas[value] +'" target="_blank" style="display: inline-block; width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + data.pendaftaranSemhas[value] + '</a>')
                     }
                  }
               });
            })

            $('#editButton').on('click', function() {
               let form = $('#editFormPendaftaran')
               form.attr('action', editDaftarSempro.replace(':id', rowId));
               form.submit();
            });
         });
      });
   </script>
@endpush
