@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row">
      <h1>Statistik Dosen</h1>
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
         ['label' => 'Nama', 'width' => 10],
         ['label' => 'NIP/NIDN', 'width' => 10],
         ['label' => 'Mahasiswa Bimbingan', 'width' => 10],
         ['label' => 'Ujian Sempro', 'width' => 10],
         ['label' => 'Ujian Semhas', 'width' => 10],
         ['label' => 'Ujian Sidang', 'width' => 10],
      ];

      $config = [
         'order' => [[0, 'asc']],
         'language' => ['url' => '/json/datatables-id.json'],
         'columns' => [null, null, null, null, null, null, null],
      ];

      // $totalRows = count($leaderboard);

   @endphp

   <x-adminlte-datatable id="jadwalSkripsiTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
      @foreach($leaderboard as $row)
         <tr>
            <td>{{ $loop->index + 1 }}</td>
            <td>{{ $row['user']->name }}</td>
            <td>{{ $row['user']->nim_nip_nidn }}</td>
            <td>{{ $row['mahasiswaBimbingan'] }}</td>
            <td>{{ $row['pengujiSempro'] }}</td>
            <td>{{ $row['pengujiSemhas'] }}</td>
            <td>{{ $row['pengujiSidang'] }}</td>
         </tr>
      @endforeach
   </x-adminlte-datatable>
@stop

