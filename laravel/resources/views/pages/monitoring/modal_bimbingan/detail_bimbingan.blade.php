<x-adminlte-modal id="detailBimbingan" title="Lihat Detail Bimbingan" theme="blue" size='lg'>
   <div class="modal-body">
      <form enctype="multipart/form-data">
      @csrf
      <div class="flex pb-4 w-50">
            <h2>Data Diri</h2>
            <table>
               <tr>
                  <td>
                        <h5 class="font-weight-bold">Nama Mahasiswa</h5>
                  </td>
                  <td id="namaMahasiswa">None</td>
                  <input id="mahasiswaId" type="hidden" name="mahasiswaId" value="">
               </tr>
               <tr>
                  <td>
                        <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
                  </td>
                  <td id="nimMahasiswa">None</td>
               </tr>
            </table>
      </div>
      <table style="background-color: transparent;">
            <tr>
               <td colspan="6">
                  <tr>
                        <x-adminlte-textarea id="judulSkripsi" name="judulSkripsi" label="Judul Skripsi" placeholder="Masukkan judul skripsi..." disabled>
                           None
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-warning">
                                    <i class="fas fa-file-invoice"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-textarea>
                  </tr>
               </td>
            </tr>
            <tr>
               <td colspan="3">
                  <x-adminlte-input name="catatanBimbingan" label="Catatan Bimbingan" placeholder="Masukkan catatan bimbingan..."  value="{{ old('catatanBimbingan') }}" autocomplete="off" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td colspan="3">
                  <x-adminlte-select name="pembimbing" label="Dosen Pembimbing" disabled>
                        <option value="" selected disabled hidden>None</option>
                        {{-- @foreach ($namaDosen as $id => $nama)
                           <option value="{{ $id }}" {{ old('pembimbing') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach --}}
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-dark">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-select>
               </td>
            </tr>
            <tr>
               <td colspan="2">
                  @php
                        $configDate = ['format' => 'YYYY-MM-DD'];
                  @endphp
                  <x-adminlte-input-date name="tanggalBimbingan" :config="$configDate" placeholder="Pilih tanggal bimbingan.." label="Tanggal Bimbingan" autocomplete="off" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-primary">
                              <i class="fas fa-calendar-alt"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input-date>
               </td>
               <td colspan="2">
                  <x-adminlte-input id="sesiBimbingan" name="sesiBimbingan" label="Sesi Bimbingan" placeholder="Sesi Bimbingan Ke-" type="number" autocomplete="off" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-sort-numeric-up"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td colspan="2">
                  <x-adminlte-select name="jenisBimbingan" label="Jenis Bimbingan" disabled>
                  <option value="" selected disabled hidden>None</option>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-light">
                              <i class="fas fa-search-location"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-select>
               </td>
            </tr>
      </table>
   </form>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
      $('#detailBimbingan').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget);
         let modal = $(this);

         var rowId = button.data('row-id');
         var apiUrl = '/api/monitoring/bimbingan_skripsi/detail/' + rowId;

         $.get(apiUrl, function(data) {
            modal.find('#namaMahasiswa').text(data.bimbinganSkripsi.mahasiswa.name);
            modal.find('#mahasiswaId').val(data.bimbinganSkripsi.mahasiswa.id);
            modal.find('#nimMahasiswa').text(data.bimbinganSkripsi.mahasiswa.nim_nip_nidn);

            modal.find('#judulSkripsi').val(data.bimbinganSkripsi.judul_skripsi);
            if (data.bimbinganSkripsi.catatan) {
               modal.find('#catatanBimbingan').val(data.bimbinganSkripsi.catatan);
            }
            modal.find('#pembimbing').html('<option value="'+data.bimbinganSkripsi.pembimbing_id+'" selected>'+data.bimbinganSkripsi.pembimbing.name+'</option>');

            modal.find('#tanggalBimbingan').val(data.bimbinganSkripsi.tanggal);
            modal.find('#sesiBimbingan').val(data.bimbinganSkripsi.sesi);
            modal.find('#jenisBimbingan').html('<option value="'+data.bimbinganSkripsi.jenis+'" selected>'+data.bimbinganSkripsi.jenis+'</option>');
         });
      });
   });
</script>
@endpush
