<x-adminlte-modal id="detailCatatanNilaiSempro" title="Catatan Seluruh Penguji" theme="blue" size='lg'>
   <div class="modal-body">
      <table>
            <tr>
               <td>
                  <h3>Komentar</h3>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanJudul" name="catatanJudul" label="Redaksi Judul" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanLatarBelakang" name="catatanLatarBelakang" label="Latar Belakang" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanIdentifikasiMasalah" name="catatanIdentifikasiMasalah" label="Identifikasi Masalah" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanPembatasanMasalah" name="catatanPembatasanMasalah" label="Pembatasan Masalah" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanPerumusanMasalah" name="catatanPerumusanMasalah" label="Perumusan Masalah" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanPenelitianTerdahulu" name="catatanPenelitianTerdahulu" label="Penelitian Terdahulu" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanMetodologiPenelitian" name="catatanMetodologiPenelitian" label="Metodologi Penelitian" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-textarea id="catatanReferensi" name="catatanReferensi" label="Referensi" placeholder="Tidak ada komentar.." disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-textarea>
               </td>
            </tr>
      </table>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
      $('#detailCatatanNilaiSempro').on('show.bs.modal', function(event) {
         var button = $(event.relatedTarget);
         let modal = $(this);

         var rowId = button.data('row-id');
         console.log(rowId)
         var apiUrl = '/api/seminar_proposal/penilaian/detail_catatan_all/' + rowId;
         console.log(apiUrl)
         // Make an AJAX request to fetch the data

         $.get(apiUrl, function(data) {
            var judul = ''
            var latarBelakang = ''
            var identifikasiMasalah = ''
            var pembatasanMasalah = ''
            var perumusanMasalah = ''
            var penelitianTerdahulu = ''
            var metodologiPenelitian = ''
            var referensi = ''

            for (let key in data) {
                  if (data[key].judul) {
                     judul += data[key].penguji.name + ':\n  ' + data[key].judul + '\n\n'
                  }
                  if (data[key].latar_belakang) {
                     latarBelakang += data[key].penguji.name + ':\n  ' + data[key].latar_belakang + '\n\n'
                  }
                  if (data[key].identifikasi_masalah) {
                     identifikasiMasalah += data[key].penguji.name + ':\n  ' + data[key].identifikasi_masalah + '\n\n'
                  }
                  if (data[key].pembatasan_masalah) {
                     pembatasanMasalah += data[key].penguji.name + ':\n  ' + data[key].pembatasan_masalah + '\n\n'
                  }
                  if (data[key].perumusan_masalah) {
                     perumusanMasalah += data[key].penguji.name + ':\n  ' + data[key].perumusan_masalah + '\n\n'
                  }
                  if (data[key].penelitian_terdahulu) {
                     penelitianTerdahulu += data[key].penguji.name + ':\n  ' + data[key].penelitian_terdahulu + '\n\n'
                  }
                  if (data[key].metodologi_penelitian) {
                     metodologiPenelitian += data[key].penguji.name + ':\n  ' + data[key].metodologi_penelitian + '\n\n'
                  }
                  if (data[key].referensi) {
                     referensi += data[key].penguji.name + ':\n  ' + data[key].referensi + '\n\n'
                  }
            }

            modal.find('#catatanJudul').val(judul)
            modal.find('#catatanLatarBelakang').val(latarBelakang)
            modal.find('#catatanIdentifikasiMasalah').val(identifikasiMasalah)
            modal.find('#catatanPembatasanMasalah').val(pembatasanMasalah)
            modal.find('#catatanPerumusanMasalah').val(perumusanMasalah)
            modal.find('#catatanPenelitianTerdahulu').val(penelitianTerdahulu)
            modal.find('#catatanMetodologiPenelitian').val(metodologiPenelitian)
            modal.find('#catatanReferensi').val(referensi)
         });
      });
});
</script>
@endpush
