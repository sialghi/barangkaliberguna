<x-adminlte-modal id="aksiNilai" title="Ambil Aksi" theme="blue" size='lg'>
   <div class="modal-body">
      <form method="POST" id="aksiFormPenilaian" class="d-flex justify-content-center" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            {{-- <iframe id="pdfViewer" style="width:100%;height:500px;border:none;"></iframe> --}}
            {{-- <h4 id="pdfViewer">test failed</h4> --}}
            {{-- <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button> --}}
            <button type="submit" id="aksiSelesaiButton" class="btn btn-success">Selesai</button>
            <button type="submit" id="aksiTolakButton" class="btn btn-danger mx-3">Tolak</button>
            <button type="submit" id="aksiRevisiButton" class="btn btn-primary">Revisi</button>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
      $('#aksiNilai').on('show.bs.modal', function(event) {
         let button = $(event.relatedTarget);
         let rowId = button.data('row-id');
         let form = $('#aksiFormPenilaian')

         $('#aksiSelesaiButton').on('click', function() {
            // event.preventDefault();
            let approve = "{{ route('approve.nilai.seminar.proposal', ['id' => ':id']) }}";
            form.attr('action', approve.replace(':id', rowId));
            // console.log(form.attr('action'))
            form.submit();
         });

         $('#aksiTolakButton').on('click', function() {
            event.preventDefault();
            let reject = "{{ route('reject.nilai.seminar.proposal', ['id' => ':id']) }}";
            form.attr('action', reject.replace(':id', rowId));
            form.submit();
         });

         $('#aksiRevisiButton').on('click', function() {
            event.preventDefault();
            let revise = "{{ route('revise.nilai.seminar.proposal', ['id' => ':id']) }}";
            form.attr('action', revise.replace(':id', rowId));
            form.submit();
         });
         // window.location.href = url
      });
});
</script>
@endpush
