<x-adminlte-modal id="hapusBimbingan" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormBimbingan" class="d-flex justify-content-center">
            @csrf
            @method('DELETE')
            <button type="submit" id="deleteButton" class="btn btn-danger">Hapus</button>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function() {
         $('#hapusBimbingan').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let deleteBimbinganSkripsi = "{{ route('delete.monitoring.bimbingan.skripsi', ['id' => ':id']) }}";

            $('#deleteButton').on('click', function() {
               let form = $('#deleteFormBimbingan')
               form.attr('action', deleteBimbinganSkripsi.replace(':id', rowId));
               form.submit();
            });
         });
      });
   </script>
@endpush
