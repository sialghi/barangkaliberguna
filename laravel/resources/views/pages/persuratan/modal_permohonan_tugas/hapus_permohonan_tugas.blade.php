<x-adminlte-modal id="hapusPermohonanTugas" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormPermohonanTugas" class="d-flex justify-content-center">
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
         $('#hapusPermohonanTugas').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let deletePermohonanTugas = "{{ route('delete.surat.tugas', ['id' => ':id']) }}";

            $('#deleteButton').on('click', function() {
               let form = $('#deleteFormPermohonanTugas')
               form.attr('action', deletePermohonanTugas.replace(':id', rowId));
               form.submit();
            });
         });
      });
   </script>
@endpush
