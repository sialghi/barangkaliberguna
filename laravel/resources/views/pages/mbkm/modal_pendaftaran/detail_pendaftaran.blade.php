<x-adminlte-modal id="detailPendaftaran" title="Detail Pendaftaran" theme="blue" size='lg'>
   <div class="modal-body">
      <form action="{{ route('store.daftar.mbkm') }}" method="POST" enctype="multipart/form-data">
         <div class="flex pb-4">
            <h2>Data Diri</h2>
            <table>
                  <tr>
                     {{-- NIM Mahasiswa --}}
                     <td>
                        <h5 class="font-weight-bold">NIM/NIP/NIDN</h5>
                     </td>
                     <td id="mahasiswaNim">None</td>
                  </tr>
                  <tr>
                     {{-- Nama Mahasiswa --}}
                     <td>
                        <h5 class="font-weight-bold">Nama Mahasiswa</h5>
                     </td>
                     <td id="mahasiswaNama">None</td>
                  </tr>
            </table>
         </div>

         @csrf
         <table style="background-color: transparent;">
            <tr>
                  <td>
                     {{-- Jenis MBKM --}}
                     <label for="jenisMbkm">Jenis MBKM <span class="text-red">*</span></label>
                     <x-adminlte-select name="jenisMbkm" id="jenisMbkm" disabled>
                        <option value="" selected disabled hidden>Pilih Jenis MBKM</option>
                        <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-purple">
                                 <i class="fas fa-list"></i>
                              </div>
                        </x-slot>
                     </x-adminlte-select>
                  </td>
                  <td>
                     {{-- Dosen Pembimbing --}}
                     <label for="dosenPembimbing">Dosen Pembimbing <span class="text-red">*</span></label>
                     <x-adminlte-select name="dosenPembimbing" id="dosenPembimbing" disabled>
                     <option value="" selected disabled hidden>Pilih Dosen Pembimbing</option>
                        @foreach ($namaDosen as $id => $nama)
                              <option value="{{ $id }}" {{ old('dosenPembimbing') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                        <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-dark">
                                 <i class="fas fa-user"></i>
                              </div>
                        </x-slot>
                     </x-adminlte-select>
                  </td>
            </tr>
            <tr>
               <td>
                  {{-- Mitra --}}
                  <label for="mitra">Mitra <span class="text-red">*</span></label>
                  <x-adminlte-input name="mitra" id="mitra" placeholder="Masukkan mitra..."  value="{{ old('mitra') }}" autocomplete="off">
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                        </div>
                     </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  {{-- Learning Path --}}
                  <label for="learningPath">Learning Path</label>
                  <x-adminlte-input name="learningPath" id="learningPath" placeholder="Masukkan learning path..." autocomplete="off" disabled>
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                        </div>
                     </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  {{-- Mata Kuliah Dikonversi --}}
                  <label for="mkKonversi">MK Dikonversi <span class="text-red">*</span></label>
                  <x-adminlte-textarea name="mkKonversi" id="mkKonversi" placeholder="Masukkan mata kuliah yang akan dikonversi..." autocomplete="off" disabled>
                     {{ old('mkKonversi') }}
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-pen"></i>
                        </div>
                     </x-slot>
                  </x-adminlte-textarea>
                  <small class="text-muted">Pisah mata kuliah menggunakan koma ' , ', dengan maksimal 191 karakter</small>
               </td>
               <td>
                  {{-- NIM --}}
                  <label for="jumlahSks">Total SKS <span class="text-red">*</span></label>
                  <x-adminlte-input name="jumlahSks" id="jumlahSks" placeholder="Masukkan total sks yang dikonversi" autocomplete="off" disabled>
                     <x-slot name="prependSlot">
                        <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-address-card"></i>
                        </div>
                     </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  {{-- File Pernyataan Kesanggupan/Komitmen --}}
                  <label for="fileKomitmen" id="fileKomitmenText">File Pernyataan Kesanggupan/Komitmen (PDF) <span class="text-red">*</span></label>
                  <x-adminlte-input-file name="fileKomitmen" id="fileKomitmen" placeholder="Klik untuk upload file..."
                     disable-feedback onchange="displayFileName(this)" accept=".pdf" disabled>
                     <x-slot name="prependSlot">
                     <div class="input-group-text bg-gradient-primary">
                           <i class="fas fa-file-upload"></i>
                     </x-slot>
                  </x-adminlte-input-file>
               </td>
            </tr>
         </table>
      </form>
      <x-slot name="footerSlot" class="modal-footer">
            <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
      </x-slot>
   </div>
</x-adminlte-modal>

@push('js')
   <script>
      $(document).ready(function(){
         $('#detailPendaftaran').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget)
            let modal = $(this);

            let rowId = button.data('row-id')
            let apiUrl = '/api/mbkm/daftar/detail/' + rowId

            $.get(apiUrl, function (data) {
               console.log(data)
               modal.find('#mahasiswaNim').text(data.pendaftaranMbkm.mahasiswa.nim_nip_nidn)
               modal.find('#mahasiswaNama').text(data.pendaftaranMbkm.mahasiswa.name)

               modal.find('#jenisMbkm').html('<option value="'+ data.pendaftaranMbkm.jenis_mbkm +'" selected disabled hidden>'+ data.pendaftaranMbkm.jenis_mbkm +'</option>')
               modal.find('#dosenPembimbing').html('<option value="'+ data.pendaftaranMbkm.pembimbing.id +'" selected disabled hidden>'+ data.pendaftaranMbkm.pembimbing.name +'</option>')

               modal.find('#mitra').val(data.pendaftaranMbkm.mitra)
               modal.find('#learningPath').val(data.pendaftaranMbkm.learning_path)

               modal.find('#mkKonversi').val(data.pendaftaranMbkm.mk_konversi)
               modal.find('#jumlahSks').val(data.pendaftaranMbkm.jumlah_sks)
               modal.find('#fileKomitmenText').html('File Pernyataan Kesanggupan/Komitmen (PDF) <span class="text-red">*</span>')
               modal.find('#fileKomitmenText').append('<br>Current File:<br><a href="/api/mbkm/daftar/berkas/'+ data.pendaftaranMbkm.file_pernyataan_komitmen +'" target="_blank" style="display: inline-block; width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + data.pendaftaranMbkm.file_pernyataan_komitmen + '</a>')
            })
         });
      });
   </script>
@endpush
