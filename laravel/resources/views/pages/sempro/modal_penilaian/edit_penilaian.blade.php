<x-adminlte-modal id="editNilai" title="Edit Nilai Seminar Proposal" theme="blue" size='lg'>
   <div class="modal-body">
      <form id="editFormNilai" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="d-flex">
               <table style="background-color: transparent;">
                  <tr>
                        <td colspan="2">
                           <h2>Mahasiswa</h2>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              NIM/NIP/NIDN
                           </h5>
                        </td>
                        <td id="nimMahasiswa">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Nama Mahasiswa
                           </h5>
                        </td>
                        <td id="namaMahasiswa">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Nomor Telepon
                           </h5>
                        </td>
                        <td id="teleponMahasiswa">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td colspan="2">
                           <h2>Proposal Skripsi</h2>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Judul Proposal
                           </h5>
                        </td>
                        <td id="judulProposal">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Periode
                           </h5>
                        </td>
                        <td id="periodeProposal">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Calon Dosen Pembimbing 1
                           </h5>
                        </td>
                        <td id="calonDosenPembimbing1">
                           None
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <h5 class="font-weight-bold">
                              Calon Dosen Pembimbing 2
                           </h5>
                        </td>
                        <td id="calonDosenPembimbing2">
                           None
                        </td>
                  </tr>
               </table>
               <table class="ml-4">
                  <tr>
                        <td>
                           <h2>Data Penguji</h2>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <x-adminlte-select id="dosenPenguji1" name="dosenPenguji1" label="Dosen Penguji 1">
                              <option value="" selected disabled hidden>Pilih Dosen Penguji 1</option>
                              <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-primary">
                                       <i class="fas fa-user"></i>
                                    </div>
                              </x-slot>
                           </x-adminlte-select>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <x-adminlte-select id="dosenPenguji2" name="dosenPenguji2" label="Dosen Penguji 2">
                              <option value="" selected disabled hidden>Pilih Dosen Penguji 2</option>
                              <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-primary">
                                       <i class="fas fa-user"></i>
                                    </div>
                              </x-slot>
                           </x-adminlte-select>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <x-adminlte-select id="dosenPenguji3" name="dosenPenguji3" label="Dosen Penguji 3">
                              <option value="" selected disabled hidden>Pilih Dosen Penguji 3</option>
                              <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-primary">
                                       <i class="fas fa-user"></i>
                                    </div>
                              </x-slot>
                           </x-adminlte-select>
                        </td>
                  </tr>
                  <tr>
                        <td>
                           <x-adminlte-select id="dosenPenguji4" name="dosenPenguji4" label="Dosen Penguji 4">
                              <option value="" selected disabled hidden>Pilih Dosen Penguji 4</option>
                              <x-slot name="prependSlot">
                                    <div class="input-group-text bg-gradient-primary">
                                       <i class="fas fa-user"></i>
                                    </div>
                              </x-slot>
                           </x-adminlte-select>
                        </td>
                  </tr>
                  <tr>
                  <td>
                     <h2>Data Pembimbing</h2>
                  </td>
               </tr>
               <tr>
                  <td>
                     <x-adminlte-select name="dosenPembimbing1" label="Dosen Pembimbing 1">
                           <option value="" selected disabled hidden>Pilih Dosen Pembimbing 1</option>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-primary">
                                 <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                     </x-adminlte-select>
                  </td>
               </tr>
               <tr>
                  <td>
                     <x-adminlte-select name="dosenPembimbing2" label="Dosen Pembimbing 2">
                           <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-primary">
                                 <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                     </x-adminlte-select>
                  </td>
               </tr>
               </table>
            </div>
            <x-slot name="footerSlot" class="modal-footer">
               <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
               <button type="submit" id="editButton" class="btn btn-success">Simpan</button>
            </x-slot>
      </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
      $('#editNilai').on('show.bs.modal', function (event) {
         var button = $(event.relatedTarget);
         let modal = $(this);

         var rowId = button.data('row-id');
         var apiUrl = '/api/seminar_proposal/penilaian/detail/' + rowId;
         let editNilaiSempro = "{{ route('update.nilai.seminar.proposal', ['id' => ':id']) }}";

         // Make an AJAX request to fetch the data

         $('#editButton').on('click', function() {
            let form = $('#editFormNilai');
            form.attr('action', editNilaiSempro.replace(':id', rowId));
            form.submit();
         });


         $.get(apiUrl, function(data) {
            // Update the content of the table cells with the fetched data
            console.log(data)
            modal.find('#nimMahasiswa').text(data.nilaiSempro.mahasiswa.nim_nip_nidn)
            modal.find('#namaMahasiswa').text(data.nilaiSempro.mahasiswa.name)
            if (data.nilaiSempro.mahasiswa.no_hp) {
               modal.find('#teleponMahasiswa').text(data.nilaiSempro.mahasiswa.no_hp)
            }


            if (data.pendaftaranSempro) {
               modal.find('#judulProposal').text(data.pendaftaranSempro.judul_proposal)
               modal.find('#periodeProposal').text(data.pendaftaranSempro.periode_sempro.periode)

               modal.find('#calonDosenPembimbing1').text(data.pendaftaranSempro.calon_dospem1.name)
               if (data.pendaftaranSempro.calon_dospem_2_id) {
                  modal.find('#calonDosenPembimbing2').text(data.pendaftaranSempro.calon_dospem2.name)
               }
            }

            // Update the value of the select options
            for (let i = 1; i < 5; i++) {
               if (data.nilaiSempro['penguji' + i]) {
                  modal.find('#dosenPenguji' + i).html('<option value="'+ data.nilaiSempro['penguji' + i].id +'" selected hidden>'+ data.nilaiSempro['penguji' + i].name +'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old("dosenPenguji'+[i]+'") == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                //   modal.find('#dosenPenguji' + i).html('<option value="'+ data.nilaiSempro['penguji' + i].id +'" selected hidden>'+ data.nilaiSempro['penguji' + i].name +'</option>@foreach ($namaDosen as $id => $nama)<option value="{{ $id }}" {{ old("dosenPenguji'+[i]+'") == $id ? 'selected' : '' }}>{{ $nama }}</option>@endforeach')

               } else {
                  modal.find('#dosenPenguji' + i).html('<option value="" selected disabled hidden>None</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old("dosenPenguji'+[i]+'") == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
               }
            }

            for (let i = 1; i < 3; i++) {
                  if (data.nilaiSempro['pembimbing' + i]) {
                     modal.find('#dosenPembimbing' + i).html('<option value="'+ data.nilaiSempro['pembimbing' + i].id +'" selected hidden>'+ data.nilaiSempro['pembimbing' + i].name +'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old("dosenPembimbing'+[i]+'") == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                  } else {
                     modal.find('#dosenPembimbing' + i).html('<option value="" selected disabled hidden>None</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old("dosenPembimbing'+[i]+'") == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
                  }
               }
         });
      });
   });
</script>
@endpush
