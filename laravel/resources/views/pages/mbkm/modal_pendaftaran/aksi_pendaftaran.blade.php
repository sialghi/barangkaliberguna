<x-adminlte-modal id="aksiPendaftaran" title="Ambil Aksi" theme="blue" size='lg'>
   <div class="modal-body">
         <form method="POST" id="aksiFormPendaftaran" class="d-flex flex-column justify-content-center" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex justify-content-center">
               <button type="button" id="aksiTerimaButton" class="btn btn-success">Terima</button>
               <button type="button" id="aksiTolakButton" class="btn btn-danger mx-3">Tolak</button>
            </div>
            <div class="d-flex flex-column align-items-center mt-3">
               <div id="inputAlasan" style="display: none;">
                  <label id="alasan" for="alasan" class="text-left w-100"></label>
                  <input type="text" name="alasan" class="form-control" placeholder="Alasan">
               </div>
               <div id="inputFile" style="display: none;" class="">
                  <x-adminlte-input-file id="fileRekomendasi" name="fileRekomendasi" label="Unggah Surat Rekomendasi (PDF)" placeholder="Klik di sini..." disable-feedback onchange="displayFileName(this)" accept=".pdf"/>
               </div>
               <button type="submit" id="submitAlasan" style="max-width: 350px; min-width: 250px; display: none;">Submit</button>
            </div>
         </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   function displayFileName(input) {
      const fileName = input.files[0]?.name || 'Klik di sini...';
      input.parentNode.querySelector('.custom-file-label').innerText = fileName;
   }
   $(document).ready(function() {
      $('#aksiPendaftaran').on('show.bs.modal', function(event) {
         let button = $(event.relatedTarget);
         let rowId = button.data('row-id');
         let form = $('#aksiFormPendaftaran')

         $('#aksiTerimaButton').on('click', function() {
            let approve = "{{ route('approve.daftar.mbkm', ['id' => ':id']) }}";
            form.attr('action', approve.replace(':id', rowId));
            $('#inputAlasan').attr('class', 'hidden');
            $('#inputFile').attr('class', 'd-flex flex-column align-items-center mt-3');
            $('#submitAlasan').text('Terima').attr('class', 'd-flex flex-column align-items-center btn btn-primary mt-2');
         });

         $('#aksiTolakButton').on('click', function() {
            let reject = "{{ route('reject.daftar.mbkm', ['id' => ':id']) }}";
            form.attr('action', reject.replace(':id', rowId));
            $('#inputAlasan').attr('class', 'd-flex flex-column align-items-center mt-3');
            $('#inputFile').attr('class', 'hidden');
            $('#alasan').text('Alasan Penolakan');
            $('#submitAlasan').text('Tolak').attr('class', 'd-flex flex-column align-items-center btn btn-danger mt-2');
         });
      });
   });
</script>
@endpush
