{{-- Custom Modal --}}
<x-adminlte-modal id="detailUser" title="Lihat Detail" theme="blue" size='lg'>
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
            <tr id="roleUserRow1">
            </tr>
            <tr id="roleUserRow2">
            </tr>
            <tr>
               <td id="ttdUser">
                  <img
                     {{-- src="{{ url('storage/images/ttd') }}/${data.data.ttd}" --}}
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
            let apiUrl = '/pages/user/' + rowId;

            $.get(apiUrl, function (data) {
               modal.find('#namaUser').val(data.data.name);
               modal.find('#nimNipNidnUser').val(data.data.nim_nip_nidn);
               modal.find('#emailUser').val(data.data.email);
               modal.find('#noHp').val(data.data.no_hp);

               modal.find('#roleUserRow1').html('');
                modal.find('#roleUserRow2').html('');

               let roleUsers = {
                'dekan' : 'Dekan',
                'wadek_satu' : 'Wadek Satu',
                'wadek_dua' : 'Wadek Dua',
                'wadek_tiga' : 'Wadek Tiga',
                'admin_dekanat' : 'Admin Dekanat',
                'kaprodi' : 'Kaprodi',
                'sekprodi' : 'Sekprodi',
                'admin_prodi' : 'Admin Prodi',
                'dosen' : 'Dosen',
                'mahasiswa' : 'Mahasiswa'
            }

            for (let i = 0; i < data.data.pivot.length; i++) {
                let roleId = data.data.pivot[i].role.id;
                let roleUser = '';

                for (let role in roleUsers) {
                    if (role == data.data.pivot[i].role.nama) {
                        roleUser = roleUsers[role]; // Assign value to roleUser
                        break; // Exit the loop once a match is found
                    }
                }

                let prodiId = data.data.pivot[i].program_studi.id;
                let prodiUser = data.data.pivot[i].program_studi.nama;

                if (roleUser == 'Dekan' || roleUser == 'Wadek Satu' || roleUser == 'Wadek Dua' || roleUser == 'Wadek Tiga' || roleUser == 'Admin Dekanat') {
                    prodiUser = data.data.pivot[i].fakultas.nama;
                }

                if (i == 0) {
                    modal.find('#roleUserRow1').html(`
                    <td>
                        <x-adminlte-select id="roleUser`+ i+1 +`" name="roleUser`+ i+1 +`" label="Role" disabled>
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-user"></i>
                            </div>
                            </x-slot>
                            <option value="`+ roleId +`" selected hidden disabled>`+ roleUser +`</option>
                        </x-adminlte-select>
                    </td>
                    <td>
                        <x-adminlte-select id="prodiUser`+ i+1 +`" name="prodiUser`+ i+1 +`" label="`+ roleUser +` Pada" disabled>
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-building"></i>
                            </div>
                            </x-slot>
                            <option value="`+ prodiId +`" selected hidden disabled>`+ prodiUser +`</option>
                        </x-adminlte-select>
                    </td>
                    `);
                } else {
                    modal.find('#roleUserRow2').html(`
                    <td>
                        <x-adminlte-select id="roleUser`+ i+1 +`" name="roleUser`+ i+1 +`" label="Role" disabled>
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-user"></i>
                            </div>
                            </x-slot>
                            <option value="`+ roleId +`" selected hidden disabled>`+ roleUser +`</option>
                        </x-adminlte-select>
                    </td>
                    <td>
                        <x-adminlte-select id="prodiUser`+ i+1 +`" name="prodiUser`+ i+1 +`" label="`+ roleUser +` Pada" disabled>
                            <x-slot name="prependSlot">
                            <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-building"></i>
                            </div>
                            </x-slot>
                            <option value="`+ prodiId +`" selected hidden disabled>`+ prodiUser +`</option>
                        </x-adminlte-select>
                    </td>
                    `);
                }
               }
               // modal.find('#roleUser').html('<option value="'+ data.data.roles +'" selected>'+ data.data.roles +'</option>');

               if (data.data.ttd) {
                  const imageUrlReplace = data.data.ttd.replace('public/images/ttd/', '');
                  window.imageRouteTemplate = "{{ route('image.show', ['filename' => ':filename']) }}";
                  let imageUrl = window.imageRouteTemplate.replace(':filename', imageUrlReplace);

                  modal.find('#ttdUser').html(`
                     <img
                        src="${imageUrl}"
                        alt="Tanda Tangan ${data.data.name}"
                        class="img-thumbnail"
                        style="width: 200px; height: 200px;"
                     >
                  `);
               } else {
                  const imageUrl = ''
                  modal.find('#ttdUser').html('Tidak ada tanda tangan');
               }
            });
         });
   });
   </script>
@endpush
