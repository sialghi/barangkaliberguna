{{-- @section('plugins.KrajeeFileinput', true) --}}

{{-- Custom Modal --}}
<x-adminlte-modal id="editUser" title="Lihat Detail" theme="blue" size='lg'>
   <div class="modal-body">
      <form
         id="formEditUser"
         method="POST"
         enctype="multipart/form-data">
         @csrf
         @method('PUT')
         <table>
               <tr>
                  <th id="role">None</th>
               </tr>
               <tr>
                  <td>
                     <x-adminlte-input id="namaUser" name="namaUser" label="Nama" placeholder="Nama pengguna">
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                 <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                     </x-adminlte-input>
                  </td>
                  <td>
                     <x-adminlte-input id="nimNipNidnUser" name="nimNipNidnUser" type="number" label="NIM/NIP/NIDN" placeholder="NIM/NIP/NIDN pengguna">
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
                     <x-adminlte-input id="emailUser" name="emailUser" type="email" label="Email" placeholder="Email pengguna">
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                 <i class="fas fa-envelope"></i>
                              </div>
                           </x-slot>
                     </x-adminlte-input>
                  </td>
                  <td>
                     <x-adminlte-input id="noHp" name="noHp" type="number" label="Nomor Telepon" placeholder="Nomor Telepon">
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-success">
                                 <i class="fas fa-phone"></i>
                              </div>
                           </x-slot>
                     </x-adminlte-input>
                  </td>
               </tr>
               <tr id="roleUserRow1">
                </tr>
                <tr id="roleUserRow2">
                </tr>
               <tr>
                  <td id="ttdUser">
                     <img
                        {{-- src="{{ url('storage/images/ttd') }}/${data.ttd}" --}}
                        alt="Tanda Tangan Pengguna"
                        class="img-thumbnail"
                        style="width: 200px; height: 200px;"
                     >
                  </td>
                  <td>
                     <label for="ttdUser">Tanda Tangan Baru</label>
                     <x-adminlte-input-file name="ttdUser" placeholder="Klik untuk mengganti tanda tangan..."
                        disable-feedback onchange="displayFileName(this)" accept="image/*">
                        <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-primary">
                              <i class="fas fa-file-upload"></i>
                        </x-slot>
                     </x-adminlte-input-file>
                  </td>
               </tr>
         </table>
         <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
            <button type="submit" id="editButton" class="btn btn-success">Simpan</button>
         </x-slot>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   function displayFileName(input) {
      const fileName = input.files[0]?.name || 'Klik di sini...';
      input.parentNode.querySelector('.custom-file-label').innerText = fileName;
    }

   $(document).ready(function(){
      $('#editUser').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget);
         let modal = $(this);

         let rowId = button.data('row-id');
         let apiUrl = '/pages/user/' + rowId;

         let editUser = "{{ route('user.update', ['id' => ':id']) }}";

         // Make an AJAX request to fetch the data

         $('#editButton').on('click', function() {
            let form = $('#formEditUser');
            form.attr('action', editUser.replace(':id', rowId));
            form.submit();
         });

         $.get(apiUrl, function (data) {
            console.log(data)

            modal.find('#namaUser').val(data.data.name);
            modal.find('#nimNipNidnUser').val(data.data.nim_nip_nidn);
            modal.find('#emailUser').val(data.data.email);
            modal.find('#noHp').val(data.data.no_hp);

            modal.find('#roleUserRow1').html('');
            modal.find('#roleUserRow2').html('');

            for (let i = 0; i < data.data.pivot.length; i++) {
                let roleId = data.data.pivot[i].role.id;
                let roleUser = data.data.pivot[i].role.nama;

                let roleOptions = '';
                for (let i = 0; i < data.role.length; i++) {
                    roleOptions += `<option value="${data.role[i].id}">${data.role[i].nama}</option>`;
                }

                let prodiOptions = '';
                for (let i = 0; i < data.prodi.length; i++) {
                    prodiOptions += `<option value="${data.prodi[i].id}">${data.prodi[i].nama}</option>`;
                }

                let fakultasOptions = '';
                for (let i = 0; i < data.fakultas.length; i++) {
                    fakultasOptions += `<option value="${data.fakultas[i].id}">${data.fakultas[i].nama}</option>`;
                }

                let prodiId = data.data.pivot[i].program_studi.id;
                let prodiUser = data.data.pivot[i].program_studi.nama;

                if (roleUser == 'dekan' || roleUser == 'wadek_satu' || roleUser == 'wadek_dua' || roleUser == 'wadek_tiga' || roleUser == 'admin_dekanat') {
                    prodiUser = data.data.pivot[i].fakultas.nama;
                }

                let selectedOptions = (roleUser === 'dekan' || roleUser === 'wadek_satu' || roleUser === 'wadek_dua' || roleUser === 'wadek_tiga' || roleUser === 'admin_dekanat') ? fakultasOptions : prodiOptions;

                if (i == 0) {
                    modal.find('#roleUserRow1').html(`
                    <td>
                        <x-adminlte-select id="roleUser1" name="roleUser1" label="Role">
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-user"></i>
                            </div>
                            </x-slot>
                            <option value="`+ roleId +`" selected hidden>`+ roleUser +`</option>
                            ${roleOptions}
                        </x-adminlte-select>
                    </td>
                    <td>
                        <x-adminlte-select id="prodiUser1" name="prodiUser1" label="`+ roleUser +` Pada">
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-building"></i>
                            </div>
                            </x-slot>
                            <option value="`+ prodiId +`" selected hidden>`+ prodiUser +`</option>
                            ${selectedOptions}
                        </x-adminlte-select>
                    </td>
                    `);
                } else {
                    modal.find('#roleUserRow2').html(`
                    <td>
                        <x-adminlte-select id="roleUser2" name="roleUser2" label="Role">
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-user"></i>
                            </div>
                            </x-slot>
                            <option value="`+ roleId +`" selected hidden>`+ roleUser +`</option>
                            ${roleOptions}
                        </x-adminlte-select>
                    </td>
                    <td>
                        <x-adminlte-select id="prodiUser2" name="prodiUser2" label="`+ roleUser +` Pada">
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-building"></i>
                            </div>
                            </x-slot>
                            <option value="`+ prodiId +`" selected hidden>`+ prodiUser +`</option>
                            ${selectedOptions}
                        </x-adminlte-select>
                    </td>
                    `);
                }
               }

            const imageUrl = data.ttd.replace('public/', 'storage/');
            if (data.ttd) {
               modal.find('#ttdUser').html(`
                  <img
                     src="{{ asset('${imageUrl}') }}"
                     alt="Tanda Tangan ${data.name}"
                     class="img-thumbnail"
                     style="width: 200px; height: 200px;"
                  >
               `);
            }
         });
      });
});
</script>
@endpush
