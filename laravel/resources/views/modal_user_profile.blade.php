{{-- Custom Modal --}}
<x-adminlte-modal id="detailUser" title="Detail Pengguna" theme="blue" size='lg'>
   <div class="modal-body">
      <table>
            <tr>
               <th id="role">None</th>
            </tr>
            <tr>
               <td>
                  <x-adminlte-input id="namaUser" name="namaUser" label="Nama" placeholder="Nama pengguna" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <x-adminlte-input id="nimNipNidnUser" name="nimNipNidnUser" label="NIM/NIP/NIDN" placeholder="NIM/NIP/NIDN pengguna" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-id-card"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  <x-adminlte-input id="emailUser" name="emailUser" label="Email" placeholder="Email pengguna" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-envelope"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <x-adminlte-input id="noHp" name="noHp" label="Nomor Telepon" placeholder="Nomor Telepon" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-phone"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr id="roleTr">
               <td>
                  <x-adminlte-select id="roleUser" name="roleUser" label="Role" disabled>
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-dark">
                              <i class="fas fa-user"></i>
                        </div>
                     </x-slot>
                     <option value="">None</option>
                  </x-adminlte-select>
               </td>
               <td>
                  <br>
               </td>
            </tr>
            <tr id="ttdUser">
               <td>
                  <img
                     {{-- src="{{ url('storage/images/ttd') }}/${data.ttd}" --}}
                     alt="Tanda Tangan Pengguna"
                     class="img-thumbnail"
                     style="width: 200px; height: 200px;"
                  >
               </td>
               <td>
                  <br>
               </td>
            </tr>
      </table>
      <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
      </x-slot>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function(){
         $('#detailUser').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget);
            let modal = $(this);

            let rowId = button.data('row-id');
            let apiUrl = '/mahasiswa/' + rowId;

            $.get(apiUrl, function (data) {
               if (data.role == 'admin') {
                  modal.find('#role').html('Data Admin');
                  modal.find('#roleUser').html('<option value="'+ data.role +'" selected>'+ data.role +'</option>');
               } else if (data.role == 'dosen') {
                  modal.find('#role').html('Data Dosen');
                  modal.find('#roleUser').html('<option value="'+ data.role +'" selected>'+ data.role +'</option>');
               } else if (data.role == 'mahasiswa') {
                  modal.find('#role').html('Data Mahasiswa');
                  modal.find('#roleUser').html('<option value="'+ data.role +'" selected>'+ data.role +'</option>');
               } else {
                  modal.find('#role').html('Data Mahasiswa');
                  modal.find('#roleTr').hide();
                  modal.find('#roleUser').html('');
               }
               modal.find('#namaUser').val(data.name);
               modal.find('#nimNipNidnUser').val(data.nim_nip_nidn);
               modal.find('#emailUser').val(data.email);
               if (data.no_hp) {
                  modal.find('#noHp').val('+'+data.no_hp);
               }

               if (data.ttd) {
                  const imageUrlReplace = data.ttd.replace('public/images/ttd/', '');
                  window.imageRouteTemplate = "{{ route('image.show', ['filename' => ':filename']) }}";
                  let imageUrl = window.imageRouteTemplate.replace(':filename', imageUrlReplace);

                  modal.find('#ttdUser').html(`
                     <td class="">
                        <figure class="figure">
                           <figcaption style="font-weight: 700;">Tanda Tangan</figcaption>
                           <img src="${imageUrl}" class="figure-img img-thumbnail" style="width: 200px; height: 200px;" alt="Tanda tangan ${data.name}">
                        </figure>
                     </td>
                     <td>
                        <br>
                     </td>
                  `);
               } else {
                  const imageUrl = ''
                  modal.find('#ttdUser').html('');
               }
            });
         });
      });
   </script>
@endpush
