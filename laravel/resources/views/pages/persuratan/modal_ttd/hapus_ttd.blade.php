<x-adminlte-modal id="hapusTtd" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormTtd" class="d-flex justify-content-center">
            @csrf
            @method('DELETE')
            <div class="text-center">
               <h3>Yakin Ingin Menghapus?</h3>
               <button type="submit" id="deleteButton" class="btn btn-danger">Hapus</button>
            </div>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function() {
         $('#hapusTtd').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let deleteTtd = "{{ route('delete.ttd.kaprodi', ['id' => ':id']) }}";
            // let deleteTtd = "{{ url('/pages/persuratan/ttd_kaprodi/:id') }}";

            $('#deleteButton').on('click', function() {
               let form = $('#deleteFormTtd')
               form.attr('action', deleteTtd.replace(':id', rowId));
               form.submit();
            });
         });
      });
   </script>
@endpush
