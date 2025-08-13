<x-adminlte-modal id="editPendaftaran" title="Edit Detail" theme="blue" size='lg'>
   <div class="modal-body">
      <table>
            <tr>
               <th>Mahasiswa</th>
            </tr>
            <tr>
               <td>
                  <x-adminlte-input id="mahasiswaNama" name="mahasiswaNama" label="Nama Mahasiswa" placeholder="Nama Mahasiswa" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-info">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <x-adminlte-input id="mahasiswaNim" name="mahasiswaNim" label="NIM Mahasiswa" placeholder="NIM Mahasiswa" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-success">
                              <i class="fas fa-id-card"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <form id="editFormPendaftaran" method="POST" enctype="multipart/form-data">
               @csrf
               @method('PUT')
               <tr class="mt-3">
                  <th colspan="2">Proposal</th>
               </tr>
               <tr>
               <td colspan="2">
                        <x-adminlte-select name="kategoriTugasAkhir" label="Kategori Tugas Akhir">
                        @foreach ($kategoriTa as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategoriTugasAkhir') == $kat->id ? 'selected' : '' }}>{{ $kat->kode }} - {{ $kat->nama }}</option>
                            @endforeach
                            <option value="" selected disabled hidden>Kategori Tugas Akhir</option>
                        </x-adminlte-select>
                  </td>
        </tr>
               <tr>
                  <td colspan="2">
                        {{-- <input type="hidden" name="_method" value="PATCH"> --}}
                        <x-adminlte-textarea id="proposalJudul" name="proposalJudul" label="Judul Proposal" placeholder="Judul Proposal">
                           <x-slot name="prependSlot">
                              <div class="input-group-text bg-gradient-info">
                                    <i class="fas fa-pen"></i>
                              </div>
                           </x-slot>
                        </x-adminlte-textarea>
                  </td>
               </tr>
               <tr>
                  <td>
                        <x-adminlte-select name="calonDospem1" label="Calon Dosen Pembimbing 1">
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('calonDospem1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
                            <x-slot name="prependSlot">
                                <div class="input-group-text bg-gradient-dark">
                                    <i class="fas fa-user"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-select>
                  </td>
                  <td>
                        <x-adminlte-select name="calonDospem2" label="Calon Dosen Pembimbing 2">
                            @foreach ($namaDosen as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('calonDospem2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>
                            @endforeach
                            <option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>
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
                        <label id="fileTranskripNilai" for="fileTranskripNilai">Transkrip Nilai (PDF)</label>
                        <x-adminlte-input-file name="fileTranskripNilai" placeholder="Klik di sini untuk mengubah file..."
                        disable-feedback onchange="displayFileName(this)" accept=".pdf">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-primary">
                              <i class="fas fa-file-upload"></i>
                           </x-slot>
                        </x-adminlte-input-file>
                  </td>
                  <td>
                        <label id="fileProposalSkripsi" for="fileProposalSkripsi">Proposal Skripsi (PDF)</label>
                        <x-adminlte-input-file name="fileProposalSkripsi" placeholder="Klik di sini untuk mengubah file..."
                        disable-feedback onchange="displayFileName(this)" accept=".pdf">
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-gradient-primary">
                              <i class="fas fa-file-upload"></i>
                           </x-slot>
                        </x-adminlte-input-file>
                  </td>
               </tr>
               <x-slot name="footerSlot" class="modal-footer">
                  <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
                  <button type="submit" id="editButton" class="btn btn-success">Simpan</button>
               </x-slot>
            </form>
      </table>
   </div>
</x-adminlte-modal>

@push('js')
<script>
   $(document).ready(function(){
         $('#editPendaftaran').on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget)
            let modal = $(this);

            let rowId = button.data('row-id');
            let apiUrl = '/api/seminar_proposal/daftar/detail/' + rowId
            let editDaftarSempro = "{{ route('update.daftar.seminar.proposal', ['id' => ':id']) }}";

            $.get(apiUrl, function (data) {
               modal.find('#mahasiswaNama').val(data.mahasiswa.name);
               modal.find('#mahasiswaNim').val(data.mahasiswa.nim_nip_nidn);

               modal.find('#proposalJudul').val(data.judul_proposal);

               modal.find('#calonDospem1').html('<option value=""'+data.calon_dospem1.id+'"" selected disabled hidden>'+data.calon_dospem1.name+'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('calonDospem1') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
               if (data.calon_dospem2 == null) {
                  modal.find('#calonDospem2').html('<option value="" selected disabled hidden>Pilih Dosen Pembimbing 2</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('calonDospem2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
               } else {
                  modal.find('#calonDospem2').html('<option value=""'+data.calon_dospem2.id+'"" selected disabled hidden>'+data.calon_dospem2.name+'</option>@foreach ($namaDosen as $dosen)<option value="{{ $dosen->id }}" {{ old('calonDospem2') == $dosen->id ? 'selected' : '' }}>{{ $dosen->name }}</option>@endforeach')
               }

               modal.find('#kategoriTugasAkhir').html('<option value=""'+data.kategori_ta.id+'"" selected disabled hidden>'+data.kategori_ta.kode+' - '+data.kategori_ta.nama+'</option>@foreach ($kategoriTa as $kat)<option value="{{ $kat->id }}" {{ old('kategoriTugasAkhir') == $kat->id ? 'selected' : '' }}>{{ $kat->kode }} - {{ $kat->nama }}</option>@endforeach')

               let fileNameTranskripNilai = data.file_transkrip_nilai.replace(/^\d+_/, '');
                let fileNameProposalSkripsi = data.file_proposal_skripsi.replace(/^\d+_/, '');

               let fileTranskripNilaiHtml = 'Transkrip Nilai (PDF) <br><span class="text-xs">File sekarang:</span><br><a href="/api/seminar_proposal/daftar/berkas/'+ data.file_transkrip_nilai +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ fileNameTranskripNilai +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+fileNameTranskripNilai+'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
               let fileProposalHtml = 'Proposal Tugas Akhir (PDF) <br><span class="text-xs">File sekarang:</span><br><a href="/api/seminar_proposal/daftar/berkas/'+ data.file_proposal +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ fileNameProposalSkripsi +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+fileNameProposalSkripsi+'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
               modal.find('#fileTranskripNilai').html(fileTranskripNilaiHtml);
               modal.find('#fileProposalSkripsi').html(fileProposalHtml);
            });

            $('#editButton').on('click', function() {
               let form = $('#editFormPendaftaran')
               form.attr('action', editDaftarSempro.replace(':id', rowId));
               form.submit();
            });
         });
   });

    function displayFileName(input) {
        const fileName = input.files[0]?.name || 'Klik di sini...';
        input.parentNode.querySelector('.custom-file-label').innerText = fileName;
    }
</script>
@endpush
