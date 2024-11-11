<x-adminlte-modal id="lihatNilaiModal" title="Lihat Nilai" theme="blue" size='lg'>
   <div class="modal-body">
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
                  <x-adminlte-input id="nilai_pembimbing_1" name="nilai_pembimbing_1" label="Nilai Pembimbing 1" placeholder="Nilai Pembimbing 1" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-sort-numeric-up"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
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
                  <x-adminlte-input id="nilai_pembimbing_2" name="nilai_pembimbing_2" label="Nilai Pembimbing 2" placeholder="Nilai Pembimbing 2" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-sort-numeric-up"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
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
                  <x-adminlte-input id="nilai_penguji_1" name="nilai_penguji_1" label="Nilai Penguji 1" placeholder="Nilai Penguji 1" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-sort-numeric-up"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
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
                  <x-adminlte-input id="nilai_penguji_2" name="nilai_penguji_2" label="Nilai Penguji 2" placeholder="Nilai Penguji 2" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-sort-numeric-up"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  <h5 class="font-weight-bold">
                     Rata-rata
                  </h5>
               </td>
               <td id="nilaiAverage">

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
         $('#lihatNilaiModal').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget)
            let modal = $(this)

            let rowId = button.data('row-id')
            let apiUrl = '/api/sidang_skripsi/penilaian/detail/' + rowId

            $.get(apiUrl, function (data) {
                console.log(data)
               modal.find('#pembimbing_1').val(data.penilaianSkripsi.pembimbing1.name)
               modal.find('#pembimbing_2').val(data.penilaianSkripsi.pembimbing2.name)
               modal.find('#penguji_1').val(data.penilaianSkripsi.penguji1.name)
               modal.find('#penguji_2').val(data.penilaianSkripsi.penguji2.name)

               modal.find('#nilai_pembimbing_1').val(data.penilaianSkripsi.nilai_pembimbing_1)
               modal.find('#nilai_pembimbing_2').val(data.penilaianSkripsi.nilai_pembimbing_2)
               modal.find('#nilai_penguji_1').val(data.penilaianSkripsi.nilai_penguji_1)
               modal.find('#nilai_penguji_2').val(data.penilaianSkripsi.nilai_penguji_2)

               modal.find('#nilaiAverage').html(
               (
                  parseFloat(data.penilaianSkripsi.nilai_pembimbing_1) +
                  parseFloat(data.penilaianSkripsi.nilai_pembimbing_2) +
                  parseFloat(data.penilaianSkripsi.nilai_penguji_1) +
                  parseFloat(data.penilaianSkripsi.nilai_penguji_2)
               ) / 4
            ).toFixed(2);
            })
         });
      });
   </script>
@endpush
