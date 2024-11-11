@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Daftar Sidang Skripsi</h1>
      <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
   </div>
   <hr>
@stop

@section('content')

   @if(session('message'))
      <x-adminlte-alert id="success-alert" theme="success" title="Success">
         {{ session('message') }}
      </x-adminlte-alert>
      <script>
         setTimeout(function() {
               document.getElementById('success-alert').style.display = 'none';
         }, 6000);
      </script>
   @endif

   @if(session('error'))
      <x-adminlte-alert id="error-alert" theme="danger" title="Error">
         {{ session('error') }}
      </x-adminlte-alert>

      <script>
         setTimeout(function() {
               document.getElementById('error-alert').style.display = 'none';
         }, 10000);
      </script>
   @endif

   @php
      $heads = [
         ['label' => 'No', 'width' => 5],
         ['label' => 'Prodi', 'width' => 10],
         ['label' => 'Waktu Ujian', 'width' => 10],
         ['label' => 'Nama', 'width' => 15],
         ['label' => 'NIM', 'width' => 10],
         ['label' => 'Judul Skripsi', 'width' => 30],
         ['label' => 'Status', 'width' => 10],
         ['label' => 'Aksi', 'no-export' => true, 'width' => 20],
      ];

      $config = [
         'order' => [[0, 'asc']],
         'language' => ['url' => '/json/datatables-id.json'],
         'columns' => [null, null, null, null, null, null, null, ['orderable' => false]],
      ];

      $totalRows = count($data);

      $uniqueProgramStudi = $userPivot->pluck('programStudi.nama')->unique();

   @endphp

    @if (array_intersect(['mahasiswa'], $userRole))
      @if ($semhasCount < 1)
         <div>
            <span class="badge bg-danger text-sm mb-1 py-1"><i class="fas fa-exclamation-circle"></i> Anda belum memenuhi syarat untuk mendaftar sidang skripsi</span>
            <br>
            <span class="badge bg-warning text-sm mb-1 py-1"><i class="fas fa-exclamation-circle"></i> Harus telah selesai melaksanakan seminar hasil dan telah mendapatkan penilaian oleh seluruh dosen yang terlibat, status "Selesai" {{$semhasCount}}/1 </span>
         </div>
         <x-adminlte-button label="Daftar Sidang Skripsi" theme="primary" icon="fas fa-user-edit" disabled/>
         <br><br>
      @else
         <x-adminlte-button label="Daftar Sidang Skripsi" theme="primary" icon="fas fa-user-edit" onclick="window.location.href = '{{ route('add.daftar.sidang.skripsi') }}';"/>
         <br><br>
      @endif
   @endif

   @if (array_intersect(['mahasiswa'], $userRole) && $hasRevise === 'Revisi')
      <div class="alert alert-warning">
         <strong>Notice:</strong> Salah satu pendaftaran anda memiliki balasan untuk direvisi. Mohon edit pendaftaran tersebut.
      </div>
      @elseif (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) && $hasRevise === 'Revisi Diajukan')
      <div class="alert alert-warning">
         <strong>Notice:</strong> Salah satu pendaftar telah mengajukan revisi pendaftaran.
      </div>
   @endif

   @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
        <x-adminlte-select name="selBasic" label="Program Studi" id="programStudiSelectDekanat" onchange="handleProgramStudiChangeDekanat()">
            <option selected>Semua</option>
            @foreach ($userPivot as $pivot)
                @if (in_array($pivot->role->nama, ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat']))
                    @foreach ($pivot->fakultas->programStudi as $prodi)
                        <option>{{ $prodi->nama }}</option>
                    @endforeach
                @endif
            @endforeach
        </x-adminlte-select>
    @elseif (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-adminlte-select name="selBasic" label="Program Studi" id="programStudiSelect" onchange="handleProgramStudiChange()">
            <option selected>Semua</option>
            @foreach ($uniqueProgramStudi as $programStudi)
                <option>{{ $programStudi }}</option>
            @endforeach
        </x-adminlte-select>
    @endif
   <x-adminlte-datatable id="daftarSkripsiTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
      @foreach($data as $row)
         <tr>
               <td>{{ $totalRows - $loop->index }}</td>
               <td>
                    @foreach($row->mahasiswa->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                </td>
               <td>{{ \Carbon\Carbon::parse($row->waktu_ujian)->locale('id')->isoFormat('D MMMM Y') }}</td>
               <td>
                  {{ $row->mahasiswa->name }}
                  @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi', 'dosen'], $userRole) &&
                        ($row->role === 'admin' || $row->role === 'dosen'))
                     {!!
                           '<button class="btn pl-1"
                           data-toggle="modal"
                           data-target="#detailUser"
                           data-row-id="'. $row->mahasiswa->id .'">
                           <i class="fas fa-md fa-fw fa-id-card text-purple" title="Lihat Profile"></i>
                           </button>'
                     !!}
                  @endif
               </td>
               <td>{{ $row->mahasiswa->nim_nip_nidn }}</td>
               <td class="text-truncate" style="max-width: 250px;" title="{{ $row->judul_skripsi }}">
                {{ Str::limit($row->judul_skripsi, 50, '...') }}
            </td>
               <td class="text-md">
                  @if ($row->status == 'Sedang Diproses')
                     <span class="badge" style="background-color: #FCAE1E">{{ $row->status }}</span>
                  @elseif ($row->status == 'Diterima')
                     <span class="badge bg-success">Disetujui</span>
                  @elseif ($row->status == 'Ditolak')
                     {!!
                        '<button class="badge btn-xs btn-default text-white bg-danger rounded shadow btnAlasan" title="Lihat Alasan"
                           data-toggle="modal"
                           data-target="#alasanPenolakan"
                           data-row-id= "'. $row->id .'">
                           <span>Ditolak</span><i class="fa fa-lg fa-fw fa-eye mt-1 ml-1"></i>
                        </button>'
                     !!}
                  @elseif ($row->status == 'Revisi')
                     {!!
                        '<button class="badge btn-xs btn-default text-white bg-primary rounded shadow btnAlasan" title="Lihat Alasan"
                           data-toggle="modal"
                           data-target="#alasanRevisi"
                           data-row-id= "'. $row->id .'">
                           <span>Revisi</span><i class="fa fa-lg fa-fw fa-eye mt-1 ml-1"></i>
                        </button>'
                     !!}
                  @elseif ($row->status == "Revisi Diajukan")
                     {!!
                     '<button class="badge btn-xs btn-default text-white bg-purple rounded shadow btnAlasan" title="Lihat Alasan"
                        data-toggle="modal"
                        data-target="#alasanRevisiDiajukan"
                        data-row-id= "'. $row->id .'">
                        <span>Revisi Diajukan</span><i class="fa fa-lg fa-fw fa-eye mt-1 ml-1"></i>
                     </button>'
                     !!}
                  @endif
               </td>
               <td>
                  <div class="flex justify-content-evenly">
                     {!!
                        '<button class="btn btn-xs btn-default bg-success text-white shadow rounded pt-1"
                        data-toggle="modal"
                        data-target="#detailPendaftaran"
                        data-row-id="'. $row->id .'">
                        <i class="fa fa-lg fa-fw fa-eye" title="Lihat Detail"></i>
                        </button>'
                     !!}
                     @if ((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) && $row->role === 'admin' ) ||
                     (array_intersect(['mahasiswa'], $userRole) && $row->role === 'mahasiswa') &&
                     $row->status === 'Revisi' )
                        {!!
                           '<a href="' . route('edit.daftar.sidang.skripsi', ['id' => $row->id]) . '"
                              class="btn btn-xs btn-default text-white shadow rounded"
                              style="background-color: #FCAE1E"
                              title="Edit Detail">
                              <i class="far fa-edit fa-lg fa-fw pt-1"></i>
                           </a>'
                        !!}
                     @endif
                     @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                        ($row->role === 'admin'))
                        {!!
                           '<button class="btn btn-xs btn-default bg-danger text-white shadow rounded pt-1"
                           data-toggle="modal"
                           data-target="#aksiPendaftaran"
                           data-row-id="'.$row->id.'">
                           <i class="fas fa-lg fa-fw fa-fist-raised" title="Aksi"></i>
                           </button>'
                        !!}
                        {!!
                           '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded pt-1"
                           data-toggle="modal"
                           data-target="#hapusPendaftaran"
                           data-row-id="'.$row->id.'">
                           <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                           </button>'
                        !!}
                     @endif
                  </div>
               </td>
         </tr>
      @endforeach
   </x-adminlte-datatable>

   @include('pages.skripsi.modal_pendaftaran.detail_pendaftaran')
   @include('pages.skripsi.modal_pendaftaran.hapus_pendaftaran')
   @include('pages.skripsi.modal_pendaftaran.aksi_pendaftaran')
   @include('pages.skripsi.modal_pendaftaran.alasan_penolakan')
   @include('pages.skripsi.modal_pendaftaran.alasan_revisi')
   @include('pages.skripsi.modal_pendaftaran.alasan_revisi_diajukan')
   @include('modal_user_profile')

@stop

@push('js')
    <script>
        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#daftarSkripsiTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#daftarSkripsiTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }

        function handleProgramStudiChange() {
            let selectedValue = document.getElementById('programStudiSelect').value;
            // Perform the necessary action when the state changes
            if (selectedValue === 'Semua') {
                const dataTable = $('#daftarSkripsiTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#daftarSkripsiTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
