<x-adminlte-modal id="detailPendaftaran" title="Detail Pendaftaran" theme="blue" size='lg'>
   <div class="modal-body">
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
                  <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" label="Judul Skripsi" placeholder="Masukkan judul skripsi..."  value="{{ old('judulSkripsi') }}" autocomplete="off" disabled>
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
                  <x-adminlte-select id="pembimbing1" name="pembimbing1" label="Dosen Pembimbing 1" disabled>
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
                  {{-- Waktu Ujian --}}
                  @php
                     $configTanggal = ['format' => 'YYYY-MM-DD HH:mm'];
                  @endphp
                  <x-adminlte-input-date id="waktuSeminar" name="waktuSeminar" :config="$configTanggal" label="Waktu Ujian" placeholder="Pilih waktu ujian..." value="None" autocomplete="off" disabled>
                     <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-green">
                              <i class="fas fa-clock"></i>
                           </div>
                     </x-slot>
                  </x-adminlte-input-date>
               </td>
               <td colspan="2">
                  {{-- Pembimbing 2 --}}
                  <x-adminlte-select id="pembimbing2" name="pembimbing2" label="Dosen Pembimbing 2" disabled>
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
                  <x-adminlte-select id="dosenPembimbingAkademik" name="dosenPembimbingAkademik" label="Dosen Pembimbing Akademik" disabled>
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
                  <x-adminlte-select id="calon_penguji1" name="calon_penguji1" label="Dosen Penguji 1" disabled>
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
                  <x-adminlte-select id="calon_penguji2" name="calon_penguji2" label="Dosen Penguji 2" disabled>
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
                  <x-adminlte-input id="calon_penguji3" name="calon_penguji3" label="Calon Dosen Penguji 3" placeholder="Calon dosen penguji di luar prodi..."  value="None" autocomplete="off" disabled>
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
                  <label id="fileTranskripNilai" for="fileTranskripNilai">Transkrip Nilai (PDF):</label>
               </td>
               <td>
                  {{-- File Naskah Skripsi --}}
                  <label id="fileNaskahSkripsi" for="fileNaskahSkripsi">Naskah Skripsi (PDF):</label>
               </td>
               <td >
                  {{-- File Pengesahan Skripsi --}}
                  <label id="filePengesahanSkripsi" for="filePengesahanSkripsi">Lembar Pengesahan Skripsi (PDF):</label>
               </td>
         </tr>
         <tr>
               <td >
                  {{-- File Pernyataan Karya Sendiri --}}
                  <label id="filePernyataanKaryaSendiri" for="filePernyataanKaryaSendiri">Surat Pernyataan Karya Sendiri (PDF):</label>
               </td>
               <td >
                  {{-- File Sertifikat TOAFL --}}
                  <label id="fileSertifikatToafl" for="fileSertifikatToafl">Sertifikat TOAFL (PBA) (PDF):</label>
               </td>
               <td >
                  {{-- File Sertifikat TOEFL --}}
                  <label id="fileSertifikatToefl" for="fileSertifikatToefl">Sertifikat TOEFL (PBI) (PDF):</label>
               </td>
         </tr>
      </table>
      <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
      </x-slot>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function(){
         $('#detailPendaftaran').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget)
            let modal = $(this)

            let rowId = button.data('row-id')
            let apiUrl = '/api/seminar_hasil/daftar/detail/' + rowId

            $.get(apiUrl, function (data) {
               modal.find('#mahasiswaNim').text(data.pendaftaranSemhas.mahasiswa.nim_nip_nidn)
               modal.find('#mahasiswaNama').text(data.pendaftaranSemhas.mahasiswa.name)

               modal.find('#judulSkripsi').val(data.pendaftaranSemhas.judul_skripsi)
               modal.find('#waktuSeminar').val(data.pendaftaranSemhas.waktu_seminar)

               let listDosen = ['dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calon_penguji1', 'calon_penguji2']
               for (var item of listDosen) {
                  if (item == 'dosenPembimbingAkademik') {
                     modal.find('#'+ item).html('<option value="'+ data.pendaftaranSemhas['dosen_pembimbing_akademik'].id +'" selected disabled hidden>'+ data.pendaftaranSemhas['dosen_pembimbing_akademik'].name +'</option>')
                  } else if (data.pendaftaranSemhas[item] != null) {
                     modal.find('#'+ item).html('<option value="'+ data.pendaftaranSemhas[item].id +'" selected disabled hidden>'+ data.pendaftaranSemhas[item].name +'</option>')
                  }
               }

               if (data.pendaftaranSemhas.calon_penguji_3_name) {
                  modal.find('#calon_penguji3').val(data.pendaftaranSemhas.calon_penguji_3_name)
               }

               modal.find('#fileTranskripNilai').html('Transkrip Nilai (PDF):')
               modal.find('#filePengesahanSkripsi').html('Lembar Pengesahan Skripsi (PDF):')
               modal.find('#filePernyataanKaryaSendiri').html('Surat Pernyataan Karya Sendiri (PDF):')
               modal.find('#fileNaskahSkripsi').html('Naskah Skripsi (PDF):')
               modal.find('#fileSertifikatToefl').html('Sertifikat TOEFL (PBI) (PDF):')
               modal.find('#fileSertifikatToafl').html('Sertifikat TOAFL (PBA) (PDF):')

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
                              modal.find('#'+ key).append('<br><a href="/api/seminar_hasil/daftar/berkas/'+ data.pendaftaranSemhas[value[j]] +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ data.pendaftaranSemhas[value[j]].replace(/^\d+_\d+_/, "") +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+ data.pendaftaranSemhas[value[j]].replace(/^\d+_\d+_/, "") +'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>')
                           }
                        }
                     } else {
                        modal.find('#'+ key).append('<br><a href="/api/seminar_hasil/daftar/berkas/'+ data.pendaftaranSemhas[value] +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ data.pendaftaranSemhas[value].replace(/^\d+_\d+_/, "") +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+ data.pendaftaranSemhas[value].replace(/^\d+_\d+_/, "") +'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>')
                     }
                  }
               });
            })
         });
      });
   </script>
@endpush
