<x-adminlte-modal id="hapusNilai" title="Hapus Data" theme="red" size='lg'>
   <div class="modal-body">
      <form method="POST" id="deleteFormPenilaian" class="d-flex justify-content-center" enctype="multipart/form-data">
            @csrf
            @method('DELETE')
            {{-- <iframe id="pdfViewer" style="width:100%;height:500px;border:none;"></iframe> --}}
            {{-- <h4 id="pdfViewer">test failed</h4> --}}
            {{-- <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button> --}}
            <button type="submit" id="deleteButton" class="btn btn-danger">Hapus</button>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
      $('#hapusNilai').on('show.bs.modal', function(event) {
         let button = $(event.relatedTarget);
         let rowId = button.data('row-id');

         let deleteDaftarSempro = "{{ route('delete.nilai.seminar.proposal', ['id' => ':id']) }}";

         $('#deleteButton').on('click', function() {
            // event.preventDefault();

            let form = $('#deleteFormPenilaian')
            form.attr('action', deleteDaftarSempro.replace(':id', rowId));
            form.submit();
         });

         // window.location.href = url
      });
   });
</script>
@endpush
