@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Jadwal Seminar Hasil</h1>
      <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
   </div>
   <hr>
   <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Panduan Halaman</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
               </div>
         </div>
      </div>
   </div>
@stop

@section('content')

   @if(session('message'))
      <x-adminlte-alert id="success-alert" theme="success" title="Success">
         {{ session('message') }}
      </x-adminlte-alert>
      <script>
         setTimeout(function() {
               document.getElementById('success-alert').style.display = 'none';
         }, 3000);
      </script>
   @endif

   @if(session('error'))
      <x-adminlte-alert id="error-alert" theme="danger" title="Error">
         {{ session('error') }}
      </x-adminlte-alert>

      <script>
         setTimeout(function() {
               document.getElementById('error-alert').style.display = 'none';
         }, 3000);
      </script>
   @endif

   @php
      $heads = [
         ['label' => 'No', 'width' => 5],
         ['label' => 'Prodi', 'width' => 10],
         ['label' => 'Tanggal', 'width' => 10],
         ['label' => 'Jam', 'width' => 10],
         ['label' => 'Nama', 'width' => 15],
         ['label' => 'NIM', 'width' => 10],
         ['label' => 'Judul Skripsi', 'width' => 30],
         ['label' => 'Pembimbing', 'width' => 10],
         ['label' => 'Penguji', 'width' => 10],
         ['label' => 'Ruangan', 'width' => 10],
         ['label' => 'Link Webinar', 'width' => 10],
      ];

      $config = [
         'order' => [[0, 'asc']],
         'language' => ['url' => '/json/datatables-id.json'],
         'columns' => [null, null, null, null, null, null, null, null, null, null, null],
      ];

      $totalRows = count($data);

      $uniqueProgramStudi = $userPivot->pluck('programStudi.nama')->unique();
   @endphp

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
    @endif
    @if (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-adminlte-select name="selBasic" label="Program Studi" id="programStudiSelect" onchange="handleProgramStudiChange()">
            <option selected>Semua</option>
            @foreach ($uniqueProgramStudi as $programStudi)
                <option>{{ $programStudi }}</option>
            @endforeach
        </x-adminlte-select>
    @endif
   <x-adminlte-datatable id="jadwalSemhasTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
      @foreach($data as $row)
         <tr>
            <td>{{ $totalRows - $loop->index }}</td>
            <td>
                @foreach($row->mahasiswa->programStudi as $prodi)
                    {{ $prodi->nama }}@if(!$loop->last),@endif
                @endforeach
            </td>
            <td>{{ \Carbon\Carbon::parse($row->tanggal_seminar)->locale('id')->isoFormat('D MMMM Y') }}</td>
            <td>{{ $row->jam_seminar }}</td>
            <td>{{ $row->mahasiswa->name }}</td>
            <td>{{ $row->mahasiswa->name }}</td>
            <td>{{ $row->judul_skripsi }}</td>
            <td>
               1. {{ $row->pembimbing1->name }}
               <br>
               2. {{ $row->pembimbing2->name }}
            </td>
            <td>
               1. {{ $row->penguji1->name }}
               <br>
               2. {{ $row->penguji2->name }}
            </td>
            <td>
               @if($row->link_seminar && $row->ruangan_seminar == null)
                  Online
               @elseif($row->link_seminar == null && $row->ruangan_seminar)
                  {{ $row->ruangan_seminar }}
               @elseif($row->link_seminar && $row->ruangan_seminar)
                  Online & {{ $row->ruangan_seminar }}
               @endif
            </td>
            <td><a href="{{ $row->link_seminar }}" target="_blank">{{ $row->link_seminar }}</a></td>
         </tr>
      @endforeach
   </x-adminlte-datatable>
@stop

@push('js')
    <script>
        function displayFileName(input) {
            const fileName = input.files[0]?.name || 'Klik di sini...';
            input.parentNode.querySelector('.custom-file-label').innerText = fileName;
        }

        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#jadwalSemhasTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#jadwalSemhasTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
        function handleProgramStudiChange() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelect').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#jadwalSemhasTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#jadwalSemhasTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
