<x-adminlte-modal id="tambahNilaiModal" title="Tambah Nilai" theme="blue" size='lg'>
   <div class="modal-body">
      <form id="tambahNilaiForm" method="POST">
      {{-- <form id="tambahNilaiForm"> --}}
            @csrf
            @method('PUT')
            <table>
               <tr>
                  <th>Pembimbing</th>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-input id="pembimbing_1" name="pembimbing_1" label="Dosen Pembimbing 1" placeholder="Dosen Pembimbing 1" disabled>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-input>
                  </td>
                  <td>
                        <x-adminlte-input id="nilai_pembimbing_1" name="nilai_pembimbing_1" label="Nilai Pembimbing 1" type="number"
                        :value="$row->nilai_pembimbing_1 ?? old('nilai_pembimbing_1')"
                        placeholder="Masukkan nilai"></x-adminlte-input>
                        {{-- <x-adminlte-input id="nilai_pembimbing_1" name="nilai_pembimbing_1" label="Nilai Pembimbing 1" placeholder="Nilai Pembimbing 1"></x-adminlte-input> --}}
                  </td>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-input id="pembimbing_2" name="pembimbing_2" label="Dosen Pembimbing 2" placeholder="Dosen Pembimbing 2" disabled>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-input>
                  </td>
                  <td>
                        <x-adminlte-input id="nilai_pembimbing_2" name="nilai_pembimbing_2" label="Nilai Pembimbing 2" type="number"
                        :value="$row->nilai_pembimbing_2 ?? old('nilai_pembimbing_2')"
                        placeholder="Masukkan nilai"></x-adminlte-input>
                        {{-- <x-adminlte-input id="nilai_pembimbing_2" name="nilai_pembimbing_2" label="Nilai Pembimbing 2" placeholder="Nilai Pembimbing 2"></x-adminlte-input> --}}
                  </td>
               </tr>
               <tr class="mt-3">
                  <th>Penguji</th>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-input id="penguji_1" name="penguji_1" label="Dosen Penguji 1" placeholder="Dosen Penguji 1" disabled>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-input>
                  </td>
                  <td>
                        <x-adminlte-input id="nilai_penguji_1" name="nilai_penguji_1" label="Nilai Penguji 1" type="number"
                        :value="$row->nilai_penguji_1 ?? old('nilai_penguji_1')"
                        placeholder="Masukkan nilai"></x-adminlte-input>
                        {{-- <x-adminlte-input id="nilai_penguji_1" name="nilai_penguji_1" label="Nilai Penguji 1" placeholder="Nilai Penguji 1"></x-adminlte-input> --}}
                  </td>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-input id="penguji_2" name="penguji_2" label="Dosen Penguji 2" placeholder="Dosen Penguji 2" disabled>
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-user"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-input>
                  </td>
                  <td>
                        <x-adminlte-input id="nilai_penguji_2" name="nilai_penguji_2" label="Nilai Penguji 2" type="number"
                        :value="$row->nilai_penguji_2 ?? old('nilai_penguji_2')"
                        placeholder="Masukkan nilai"></x-adminlte-input>
                        {{-- <x-adminlte-input id="nilai_penguji_2" name="nilai_penguji_2" label="Nilai Penguji 2" placeholder="Nilai Penguji 2"></x-adminlte-input> --}}
                  </td>
               </tr>
            </table>
            <div class="modal-footer">
               <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
               <button type="submit" class="btn btn-success">Simpan</button>
            </div>
            <x-slot name="footerSlot"></x-slot>
      </form>
   </div>
</x-adminlte-modal>


@push('js')
   <script>
      $(document).ready(function(){
         $('#tambahNilaiModal').on('show.bs.modal', function (event) {
            let loggedInDosenId = "{{ auth()->user()->id }}";
            let simpanNilaiSkripsiRoute = "{{ route('simpan.penilaian.nilai.sidang.skripsi', ['id' => ':id']) }}";

            let button = $(event.relatedTarget)
            let modal = $(this)

            let rowId = button.data('row-id');
            let apiUrl = '/api/sidang_skripsi/penilaian/detail/' + rowId;

            $.get(apiUrl, function (data) {
               modal.find('#pembimbing_1').val(data.penilaianSkripsi.pembimbing1.name)
               modal.find('#pembimbing_2').val(data.penilaianSkripsi.pembimbing2.name)
               modal.find('#penguji_1').val(data.penilaianSkripsi.penguji1.name)
               modal.find('#penguji_2').val(data.penilaianSkripsi.penguji2.name)

               modal.find('#nilai_pembimbing_1').val(data.penilaianSkripsi.nilai_pembimbing_1)
               modal.find('#nilai_pembimbing_2').val(data.penilaianSkripsi.nilai_pembimbing_2)
               modal.find('#nilai_penguji_1').val(data.penilaianSkripsi.nilai_penguji_1)
               modal.find('#nilai_penguji_2').val(data.penilaianSkripsi.nilai_penguji_2)

               let form = $('#tambahNilaiForm')
               form.attr('action', simpanNilaiSkripsiRoute.replace(':id', rowId));

               const allowedRoles = ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'];
               if (data.userRole.every(role => !allowedRoles.includes(role))) {
                  modal.find('#nilai_pembimbing_1').prop('disabled', loggedInDosenId != data.penilaianSkripsi.id_pembimbing_1);
                  modal.find('#nilai_pembimbing_2').prop('disabled', loggedInDosenId != data.penilaianSkripsi.id_pembimbing_2);
                  modal.find('#nilai_penguji_1').prop('disabled', loggedInDosenId != data.penilaianSkripsi.id_penguji_1);
                  modal.find('#nilai_penguji_2').prop('disabled', loggedInDosenId != data.penilaianSkripsi.id_penguji_2);
               }
            })
         });
      });
   </script>
@endpush
