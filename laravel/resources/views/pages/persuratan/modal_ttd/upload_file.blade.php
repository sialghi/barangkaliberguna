<x-adminlte-modal id="uploadFileModal" title="Unggah Surat" theme="primary" v-centered>
   <form method="POST" action="{{ route('new.file.ttd.kaprodi') }}" id="upload-modal" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" id="letterUploadId">
      <x-adminlte-input-file id="uploadFileBaru" name="uploadFileBaru" label="Unggah Surat (PDF)" placeholder="Klik di sini..." disable-feedback onchange="displayFileName(this)" accept=".pdf" />
      <x-slot name="footerSlot" class="modal-footer">
            <x-adminlte-button type="button" name="batalUpload" label="Batal" data-dismiss="modal" theme="light"/>
            <x-adminlte-button type="submit" form="upload-modal" name="uploadFileNew" label="Unggah" theme="success"/>
      </x-slot>
   </form>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
      $('#uploadFileModal').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget) // Button that triggered the modal
         let id = button.data('id') // Extract info from data-* attributes
         let modal = $(this)
         modal.find('#letterUploadId').val(id);
      });
   });
</script>
@endpush
