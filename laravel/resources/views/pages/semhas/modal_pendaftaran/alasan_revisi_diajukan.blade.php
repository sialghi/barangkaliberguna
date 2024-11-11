<x-adminlte-modal id="alasanRevisiDiajukan" title="Pesan Revisi Oleh Kaprodi/Sekprodi" theme="purple" size="lg" v-centered>
   <h5 id="isiAlasan"></h5>
   <x-slot name="footerSlot" class="modal-footer">
      <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
   </x-slot>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function(){
         $('#alasanRevisiDiajukan').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget);
            let modal = $(this);

            let rowId = button.data('row-id');
            let apiUrl = '/api/seminar_hasil/daftar/detail/' + rowId

            $.get(apiUrl, function (data) {
               if (data.pendaftaranSemhas.alasan == null || data.pendaftaranSemhas.alasan == "") {
                  modal.find('#isiAlasan').html("<i>Tidak ada alasan revisi</i>");
               } else {
                  modal.find('#isiAlasan').text(data.pendaftaranSemhas.alasan);
               }
            });
         });
      });
   </script>
@endpush
