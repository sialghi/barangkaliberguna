<x-adminlte-modal id="updatePassUser" title="Ganti Password" theme="blue" size='lg'>
   <div class="modal-body">
      <form method="POST" id="formUpdatePassUser" class="d-flex justify-content-center">
            @csrf
            @method('PUT')
            <div>
               <label for="passwordUser">Password <span class="text-red">*</span></label>
               <x-adminlte-input id="passwordUser" name="passwordUser" type="password" placeholder="Input password..">
                  <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-dark text-white">
                        <i class="fas fa-key"></i>
                     </div>
                  </x-slot>
               </x-adminlte-input>
            </div>
            <x-slot name="footerSlot" class="modal-footer">
               <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
               <button id="editPassButton" class="btn btn-success">Simpan</button>
            </x-slot>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function() {
         $('#updatePassUser').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let rowId = button.data('row-id');

            let updatePass = "{{ route('user.update.password', ['id' => ':id']) }}";

            $('#editPassButton').on('click', function() {
               let form = $('#formUpdatePassUser')
               form.attr('action', updatePass.replace(':id', rowId));
               form.submit();
            });

            // Add keypress event listener to the form
            $('#formUpdatePassUser').on('keypress', function(event) {
               // Check if the Enter key (key code 13) was pressed
               if (event.which === 13) {
                  event.preventDefault(); // Prevent the default form submission
                  $('#editPassButton').click(); // Trigger the click event on the button
               }
            });
         });
      });
   </script>
@endpush