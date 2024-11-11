{{-- Custom Modal --}}
<x-adminlte-modal id="detailPendaftaran" title="Lihat Detail" theme="blue" size='lg'>
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
            <tr class="mt-3">
               <th>Proposal</th>
            </tr>
            <tr>
               <td>
                  <x-adminlte-input id="proposalJudul" name="proposalJudul" label="Judul Proposal" placeholder="Judul Proposal" disabled>
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
                  <x-adminlte-input id="calonDospem1" name="calonDospem1" label="Calon Dosen Pembimbing 1" placeholder="Calon Dosen Pembimbing 1" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-dark">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
               <td>
                  <x-adminlte-input id="calonDospem2" name="calonDospem2" label="Calon Dosen Pembimbing 2" placeholder="Calon Dosen Pembimbing 2" disabled>
                        <x-slot name="prependSlot">
                           <div class="input-group-text bg-dark">
                              <i class="fas fa-user"></i>
                           </div>
                        </x-slot>
                  </x-adminlte-input>
               </td>
            </tr>
            <tr>
               <td>
                  <label id="fileTranskripNilai" for="fileTranskripNilai">Transkrip Nilai (PDF)</label>
               </td>
               <td>
                  <label id="fileProposalSkripsi" for="fileProposalSkripsi">Proposal Skripsi (PDF)</label>
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
      $('#detailPendaftaran').on('show.bs.modal', function (event) {
         let button = $(event.relatedTarget);
         let modal = $(this);

         let rowId = button.data('row-id');
         let apiUrl = '/api/seminar_proposal/daftar/detail/' + rowId;

         $.get(apiUrl, function (data) {
            modal.find('#mahasiswaNama').val(data.mahasiswa.name);
            modal.find('#mahasiswaNim').val(data.mahasiswa.nim_nip_nidn);

            modal.find('#proposalJudul').val(data.judul_proposal);

            modal.find('#calonDospem1').val(data.calon_dospem1.name);
            if (data.calon_dospem2) {
               modal.find('#calonDospem2').val(data.calon_dospem2.name);
            }

            let fileNameTranskripNilai = data.file_transkrip_nilai.replace(/^\d+_/, '');
            let fileNameProposalSkripsi = data.file_proposal_skripsi.replace(/^\d+_/, '');

            let fileTranskripNilaiHtml = 'Transkrip Nilai (PDF): <br><a href="/api/seminar_proposal/daftar/berkas/'+ data.file_transkrip_nilai +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ fileNameTranskripNilai +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+fileNameTranskripNilai+'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
            let fileProposalHtml = 'Proposal Skripsi (PDF): <br><a href="/api/seminar_proposal/daftar/berkas/'+ data.file_proposal_skripsi +'" target="_blank" class="btn btn-default px-1 d-flex align-items-center" title="'+ fileNameProposalSkripsi +'"><span style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">'+fileNameProposalSkripsi+'</span><i class="fas fa-lg fa-fw fa-file-pdf text-red"></i></a>';
            modal.find('#fileTranskripNilai').html(fileTranskripNilaiHtml);
            modal.find('#fileProposalSkripsi').html(fileProposalHtml);
         });
      });
});
</script>
@endpush
