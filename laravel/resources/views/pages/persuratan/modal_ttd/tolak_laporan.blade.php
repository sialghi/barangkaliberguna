<x-adminlte-modal id="tolakLaporan" title="Tolak Surat" theme="red" v-centered>
   <form method="POST" action="{{ route('reject.ttd.kaprodi') }}" id="modal-details">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" id="letterRejectId">
      <x-adminlte-textarea id="taAlasan" name="taAlasan" label="Alasan Penolakan" rows=4 label-class="text-dark" placeholder="Deskripsi Alasan" style="resize:None"/>
      <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
            <x-adminlte-button type="submit" form="modal-details" name="penolakanSurat" label="Tolak" theme="danger"/>
      </x-slot>
   </form>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
      $('#tolakLaporan').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget) // Button that triggered the modal
         let id = button.data('id') // Extract info from data-* attributes
         let modal = $(this)
         modal.find('#letterRejectId').val(id);
      });
   });
</script>
@endpush
