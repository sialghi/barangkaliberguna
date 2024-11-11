<x-adminlte-modal id="alasanPenolakan" title="Alasan Penolakan" theme="red" v-centered>
   <h5 id="isiAlasanPenolakan"></h5>
   <x-slot name="footerSlot" class="modal-footer">
      <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
   </x-slot>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
      $('#alasanPenolakan').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget) // Button that triggered the modal
         let reason = button.data('reason') // Extract info from data-* attributes
         let modal = $(this)
         modal.find('#isiAlasanPenolakan').text(reason)
      });
   });
</script>
@endpush