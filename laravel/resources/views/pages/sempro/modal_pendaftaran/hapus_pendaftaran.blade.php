<x-adminlte-modal id="hapusPendaftaran" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormPendaftaran" class="d-flex justify-content-center">
            @csrf
            @method('DELETE')
            <button type="submit" id="deleteButton" class="btn btn-danger">Hapus</button>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
         $('#hapusPendaftaran').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let deleteDaftarSempro = "{{ route('delete.daftar.seminar.proposal', ['id' => ':id']) }}";

            $('#deleteButton').on('click', function() {
               let form = $('#deleteFormPendaftaran')
               form.attr('action', deleteDaftarSempro.replace(':id', rowId));
               form.submit();
            });
         });
   });
</script>
@endpush
