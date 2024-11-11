@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Pendaftaran MBKM</h1>
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
         }, 8000);
      </script>
   @endif

   @if(session('error'))
      <x-adminlte-alert id="error-alert" theme="danger" title="Error">
         {{ session('error') }}
      </x-adminlte-alert>

      <script>
         setTimeout(function() {
               document.getElementById('error-alert').style.display = 'none';
         }, 8000);
      </script>
   @endif

   @if ($errors->has('fileRekomendasi'))
      <x-adminlte-alert id="error-alert" theme="danger" title="Error">
         {{ $errors->first('fileRekomendasi') }}
      </x-adminlte-alert>
      <script>
         setTimeout(function() {
               document.getElementById('error-alert').style.display = 'none';
         }, 3000);
      </script>
   @endif

   @php
      $heads = [
         ['label' => 'No', 'width' => 3],
         ['label' => 'Prodi', 'width' => 10],
         ['label' => 'NIM/Nama', 'width' => 15],
         ['label' => 'Jenis', 'width' => 7],
         ['label' => 'Pembimbing', 'width' => 8],
         ['label' => 'Mitra', 'width' => 8],
         ['label' => 'SKS Dikonversi', 'width' => 7],
         ['label' => 'Status', 'width' => 7],
         ['label' => 'Aksi', 'no-export' => true, 'width' => 15],
      ];

      $config = [
         'order' => [[0, 'asc']],
         'language' => ['url' => '/json/datatables-id.json'],
         'columns' => [null, null, null, null, null, null, null, null, ['orderable' => false]],
      ];

      $totalRows = count($data);

      $uniqueProgramStudi = $userPivot->pluck('programStudi.nama')->unique();
   @endphp

@if (array_intersect(['mahasiswa'], $userRole))
        <x-adminlte-button label="Daftar MBKM" theme="primary" icon="fas fa-user-edit" onclick="window.location.href = '{{ route('add.daftar.mbkm') }}';"/>
        <br><br>
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
   <x-adminlte-datatable id="pendaftaranMbkmTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
      @foreach($data as $row)
         <tr>
            <td>{{ $totalRows - $loop->index }}</td>
            <td>
                @foreach($row->mahasiswa->programStudi as $prodi)
                    {{ $prodi->nama }}@if(!$loop->last),@endif
                @endforeach
            </td>
            <td>{{ $row->mahasiswa->nim_nip_nidn}}/<br>{{$row->mahasiswa->name }}</td>
            <td>{{ $row->jenis_mbkm }} </td>
            <td>{{ $row->pembimbing->name }}</td>
            <td>{{ $row->mitra }}</td>
            <td>{{ $row->jumlah_sks }}</td>
            <td class="text-md">
               @if ($row->status == 'Sedang Diproses')
                  <span class="badge" style="background-color: #FCAE1E">{{$row->status}}</span>
               @elseif ($row->status == 'Diterima')
                  <span class="badge bg-success">{{$row->status}}</span>
               @elseif ($row->status == 'Ditolak')
                  {!!
                     '<button class="badge btn-xs btn-default text-white bg-danger rounded shadow btnAlasan" title="Lihat Alasan"
                        data-toggle="modal"
                        data-target="#alasanPenolakan"
                        data-row-id= "'. $row->id .'">
                        <span>Ditolak</span><i class="fa fa-lg fa-fw fa-eye mt-1 ml-1"></i>
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
                  @if ($row->status == "Diterima")
                     <a href="{{ route('view.file.rekomendasi.daftar.mbkm', Crypt::encryptString($row->id)) }}" target="_blank" class="btn btn-xs btn-default text-white shadow bg-info pt-1" title="Lihat Surat Rekomendasi">
                        <i class="fas fa-lg fa-fw fa-file-pdf"></i>
                     </a>
                  @endif
                  @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                        ($row->role === 'admin'))
                     @if ($row->status == "Sedang Diproses")
                        {!!
                           '<button class="btn btn-xs btn-default bg-danger text-white shadow rounded pt-1"
                              data-toggle="modal"
                              data-target="#aksiPendaftaran"
                              data-row-id="'.$row->id.'">
                              <i class="fas fa-lg fa-fw fa-fist-raised" title="Aksi"></i>
                           </button>'
                        !!}
                     @endif
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

   @include('pages.mbkm.modal_pendaftaran.detail_pendaftaran')
   @include('pages.mbkm.modal_pendaftaran.hapus_pendaftaran')
   @include('pages.mbkm.modal_pendaftaran.aksi_pendaftaran')
   @include('pages.mbkm.modal_pendaftaran.alasan_penolakan')
@stop

@push('js')
    <script>
        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#pendaftaranMbkmTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#pendaftaranMbkmTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }

        function handleProgramStudiChange() {
            let selectedValue = document.getElementById('programStudiSelect').value;
            // Perform the necessary action when the state changes
            if (selectedValue === 'Semua') {
                const dataTable = $('#pendaftaranMbkmTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#pendaftaranMbkmTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
