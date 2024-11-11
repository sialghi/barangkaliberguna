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
                  <x-adminlte-input-date id="waktuUjian" name="waktuUjian" :config="$configTanggal" label="Waktu Ujian" placeholder="Pilih waktu ujian..." value="None" autocomplete="off" disabled>
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
         <tr>
            <td colspan="2">
                  {{-- File Transkrip Nilai --}}
                  <label id="fileTranskrip">Transkrip Nilai (PDF):</label>
            </td>
            <td >
                  {{-- File Sertifikat TOEFL --}}
                  <label id="fileNaskahSkripsi">Naskah Skripsi (PDF):</label>
            </td>
         </tr>
         <tr>
            <td colspan="2">
                  {{-- File Pernyataan Karya Sendiri --}}
                  <label id="filePersetujuanPengujiSemhas">Persetujuan Penguji Seminar Hasil (PDF):</label>
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
         let apiUrl = '/api/sidang_skripsi/daftar/detail/' + rowId

         $.get(apiUrl, function (data) {
            modal.find('#mahasiswaNim').text(data.pendaftaranSkripsi.mahasiswa.nim_nip_nidn)
            modal.find('#mahasiswaNama').text(data.pendaftaranSkripsi.mahasiswa.name)

            modal.find('#judulSkripsi').val(data.pendaftaranSkripsi.judul_skripsi)
            modal.find('#waktuUjian').val(data.pendaftaranSkripsi.waktu_ujian)

            let listDosen = ['dosenPembimbingAkademik', 'pembimbing1', 'pembimbing2', 'calon_penguji1', 'calon_penguji2']
            for (var item of listDosen) {
               if (item == 'dosenPembimbingAkademik') {
                  modal.find('#'+ item).html('<option value="'+ data.pendaftaranSkripsi['dosen_pembimbing_akademik'].id +'" selected disabled hidden>'+ data.pendaftaranSkripsi['dosen_pembimbing_akademik'].name +'</option>')
               } else if (data.pendaftaranSkripsi[item] != null) {
                  modal.find('#'+ item).html('<option value="'+ data.pendaftaranSkripsi[item].id +'" selected disabled hidden>'+ data.pendaftaranSkripsi[item].name +'</option>')
               }
            }

            if (data.pendaftaranSkripsi.calon_penguji_3_name) {
               modal.find('#calon_penguji3').val(data.pendaftaranSkripsi.calon_penguji_3_name)
            }


            let fileTranskripNilaiHtml = 'Transkrip Nilai (PDF): <br><a href="/api/sidang_skripsi/daftar/berkas/'+ data.pendaftaranSkripsi.file_transkrip_nilai +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ data.pendaftaranSkripsi.file_transkrip_nilai.replace(/^\d+_\d+_/, "") +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+ data.pendaftaranSkripsi.file_transkrip_nilai.replace(/^\d+_\d+_/, "") +'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
            let fileNashkahSkripsiHtml = 'Naskah Skripsi (PDF): <br><a href="/api/sidang_skripsi/daftar/berkas/'+ data.pendaftaranSkripsi.file_naskah_skripsi +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ data.pendaftaranSkripsi.file_naskah_skripsi.replace(/^\d+_\d+_/, "") +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+ data.pendaftaranSkripsi.file_naskah_skripsi.replace(/^\d+_\d+_/, "") +'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
            let filePersetujuanPengujiSemhasHtml = 'Persetujuan Penguji Seminar Hasil (PDF): <br><a href="/api/sidang_skripsi/daftar/berkas/'+ data.pendaftaranSkripsi.file_persetujuan_penguji_semhas +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ data.pendaftaranSkripsi.file_persetujuan_penguji_semhas.replace(/^\d+_\d+_/, "") +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+ data.pendaftaranSkripsi.file_persetujuan_penguji_semhas.replace(/^\d+_\d+_/, "") +'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';

            modal.find('#fileTranskrip').html(fileNashkahSkripsiHtml)
            modal.find('#fileNaskahSkripsi').html(fileTranskripNilaiHtml)
            modal.find('#filePersetujuanPengujiSemhas').html(filePersetujuanPengujiSemhasHtml)
         })
      });
   });
</script>
@endpush
