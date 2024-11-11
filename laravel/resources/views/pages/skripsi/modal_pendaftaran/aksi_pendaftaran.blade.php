<x-adminlte-modal id="aksiPendaftaran" title="Ambil Aksi" theme="blue" size='lg'>
   <div class="modal-body">
         <form method="POST" id="aksiFormPendaftaran" class="d-flex flex-column justify-content-center">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center">
               <button type="button" id="aksiTerimaButton" class="btn btn-success">Terima</button>
               <button type="button" id="aksiTolakButton" class="btn btn-danger mx-3">Tolak</button>
               <button type="button" id="aksiRevisiButton" class="btn btn-primary">Revisi</button>
            </div>
            <div id="inputAlasan" style="display: none;" class="">
               <label id="alasan" for="alasan" class="text-left w-100"></label>
               <input type="text" name="alasan" class="form-control" placeholder="Alasan">
               <button type="submit" id="submitAlasan" style="max-width: 350px; min-width: 250px;">Submit</button>
            </div>
         </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function() {
         $('#aksiPendaftaran').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');
            let form = $('#aksiFormPendaftaran')

            $('#aksiTerimaButton').on('click', function() {
               let approve = "{{ route('approve.daftar.sidang.skripsi', ['id' => ':id']) }}";
               form.attr('action', approve.replace(':id', rowId));
               form.submit();
            });

            $('#aksiTolakButton').on('click', function() {
               let reject = "{{ route('reject.daftar.sidang.skripsi', ['id' => ':id']) }}";
               form.attr('action', reject.replace(':id', rowId));
               $('#inputAlasan').attr('class', 'd-flex flex-column align-items-center mt-3');
               $('#alasan').text('Alasan Penolakan');
               $('#submitAlasan').text('Tolak').attr('class', 'btn btn-danger mt-2');
            });

            $('#aksiRevisiButton').on('click', function() {
               let revise = "{{ route('revise.daftar.sidang.skripsi', ['id' => ':id']) }}";
               form.attr('action', revise.replace(':id', rowId));
               $('#inputAlasan').attr('class', 'd-flex flex-column align-items-center mt-3');
               $('#alasan').text('Alasan Revisi');
               $('#submitAlasan').text('Revisi').attr('class', 'btn btn-primary mt-2');
            });
         });
   });
</script>
@endpush
