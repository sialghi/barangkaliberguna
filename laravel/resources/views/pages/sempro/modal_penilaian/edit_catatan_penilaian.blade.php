<x-adminlte-modal id="catatanNilaiSemproPenguji" title="Catatan Penguji" theme="blue" size='lg'>
   <div class="modal-body">
      <form method="POST" id="aksiFormCatatanPenguji" class="d-flex justify-content-center" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <table>
               <tr>
                  <td>
                        <h3>Komentar</h3>
                  </td>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-textarea id="catatanJudul" name="catatanJudul" label="Redaksi Judul" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanLatarBelakang" name="catatanLatarBelakang" label="Latar Belakang" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanIdentifikasiMasalah" name="catatanIdentifikasiMasalah" label="Identifikasi Masalah" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanPembatasanMasalah" name="catatanPembatasanMasalah" label="Pembatasan Masalah" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanPerumusanMasalah" name="catatanPerumusanMasalah" label="Perumusan Masalah" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanPenelitianTerdahulu" name="catatanPenelitianTerdahulu" label="Penelitian Terdahulu" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanMetodologiPenelitian" name="catatanMetodologiPenelitian" label="Metodologi Penelitian" placeholder="Masukkan komentar..">
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
                        <x-adminlte-textarea id="catatanReferensi" name="catatanReferensi" label="Referensi" placeholder="Masukkan komentar..">
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-pen"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-textarea>
                  </td>
               </tr>
            </table>
            <x-slot name="footerSlot" class="modal-footer">
               <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
               <button type="submit" id="editCatatanButton" class="btn btn-success">Simpan</button>
            </x-slot>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
      $('#catatanNilaiSemproPenguji').on('show.bs.modal', function(event) {
         var button = $(event.relatedTarget);
         let modal = $(this);

         var rowId = button.data('row-id');
         var apiUrl = '/api/seminar_proposal/penilaian/detail_catatan/' + rowId;
         let editCatatanNilaiSempro = "{{ url('/pages/seminar_proposal/penilaian/edit_catatan/:id') }}";

         // Make an AJAX request to fetch the data
         $('#editCatatanButton').on('click', function() {
            let form = $('#aksiFormCatatanPenguji');
            form.attr('action', editCatatanNilaiSempro.replace(':id', rowId));
            form.submit();
         });

         $.get(apiUrl, function(data) {
            // Update the content of the table cells with the fetched data
            modal.find('#catatanJudul').val(data.judul)
            modal.find('#catatanLatarBelakang').val(data.latar_belakang)
            modal.find('#catatanIdentifikasiMasalah').val(data.identifikasi_masalah)
            modal.find('#catatanPembatasanMasalah').val(data.pembatasan_masalah)
            modal.find('#catatanPerumusanMasalah').val(data.perumusan_masalah)
            modal.find('#catatanPenelitianTerdahulu').val(data.penelitian_terdahulu)
            modal.find('#catatanMetodologiPenelitian').val(data.metodologi_penelitian)
            modal.find('#catatanReferensi').val(data.referensi)
         });
      });
   });
</script>
@endpush
