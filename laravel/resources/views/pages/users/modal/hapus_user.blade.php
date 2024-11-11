<x-adminlte-modal id="hapusUser" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormUser" class="d-flex justify-content-center">
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
         $('#hapusUser').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let deleteUser = "{{ route('user.delete', ['id' => ':id']) }}";

            $('#deleteButton').on('click', function() {
               let form = $('#deleteFormUser')
               form.attr('action', deleteUser.replace(':id', rowId));
               form.submit();
            });
         });
      });
   </script>
@endpush