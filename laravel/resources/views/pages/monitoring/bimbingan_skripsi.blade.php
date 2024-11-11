@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Bimbingan Skripsi</h1>
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

    @if(array_intersect(['mahasiswa'], $userRole))
        @if ($dataCount < 1)
            <div>
                <span class="badge bg-danger text-sm mb-1 py-1"><i class="fas fa-exclamation-circle"></i> Anda belum memenuhi syarat untuk menginput bimbingan skripsi</span>
                <br>
                <span class="badge bg-warning text-sm mb-1 py-1"><i class="fas fa-exclamation-circle"></i> Penilaian Seminar Proposal {{$dataCount}}/1 </span>
            </div>
            <x-adminlte-button label="Tambah Bimbingan" theme="primary" icon="fas fa-user-edit" disabled/>
            <br><br>
        @else
            <x-adminlte-button label="Tambah Bimbingan" theme="primary" icon="fas fa-pen-square" onclick="window.location.href = '{{ route('add.monitoring.bimbingan.skripsi') }}';"/>
            <br><br>
        @endif
    @else
        <x-adminlte-button label="Tambah Bimbingan" theme="primary" icon="fas fa-pen-square" onclick="window.location.href = '{{ route('add.monitoring.bimbingan.skripsi') }}';"/>
        <br><br>
    @endif

    @php
        $heads = [
            ['label' => 'No', 'width' => 3],
            ['label' => 'Prodi', 'width' => 10],
            ['label' => 'Tanggal Bimbingan', 'width' => 8],
            ['label' => 'NIM/Nama', 'width' => 10],
            ['label' => 'Catatan', 'width' => 20],
            ['label' => 'Judul Skripsi', 'width' => 17],
            ['label' => 'Pembimbing', 'width' => 12],
            ['label' => 'Sesi', 'width' => 7],
            ['label' => 'Jenis', 'width' => 7],
            ['label' => 'Aksi', 'no-export' => true, 'width' => 20],
        ];

        $config = [
            'order' => [[0, 'asc']],
            'language' => ['url' => '/json/datatables-id.json'],
            'columns' => [null, null, null, null, null, null, null, null, null, ['orderable' => false]],
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
    @elseif (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-adminlte-select name="selBasic" label="Program Studi" id="programStudiSelect" onchange="handleProgramStudiChange()">
            <option selected>Semua</option>
            @foreach ($uniqueProgramStudi as $programStudi)
                <option>{{ $programStudi }}</option>
            @endforeach
        </x-adminlte-select>
    @endif
    <x-adminlte-datatable id="bimbinganSkripsiTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
        @foreach($data as $row)
            <tr>
                <td>{{ $totalRows - $loop->index }}</td>
                <td>
                    @foreach($row->mahasiswa->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                </td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</td>
                <td>{{ $row->mahasiswa->nim_nip_nidn}}/<br>{{$row->mahasiswa->name }}</td>
                <td class="text-truncate" style="max-width: 250px;" title="{{ $row->catatan }}">
                    {{ Str::limit($row->catatan, 50, '...') }}
                </td>
                <td>{{ $row->judul_skripsi }}</td>
                <td>{{ $row->pembimbing->name }}</td>
                <td>{{ $row->sesi }}</td>
                <td>{{ $row->jenis }}</td>
                <td>
                    <div class="flex justify-content-evenly">
                        {!!
                            '<button class="btn btn-xs btn-default bg-success text-white shadow rounded"
                            data-toggle="modal"
                            data-target="#detailBimbingan"
                            data-row-id="'. $row->id .'">
                            <i class="fa fa-lg fa-fw fa-eye" title="Lihat Detail"></i>
                            </button>'
                        !!}
                        {!!
                            '<button class="btn btn-xs btn-default text-white shadow rounded" style="background-color: #FCAE1E"
                            data-toggle="modal"
                            data-target="#editBimbingan"
                            data-row-id="'.$row->id.'">
                            <i class="far fa-edit fa-lg fa-fw" title="Edit Detail"></i>
                            </button>'
                        !!}
                        {!!
                            '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded"
                            data-toggle="modal"
                            data-target="#hapusBimbingan"
                            data-row-id="'.$row->id.'">
                            <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                            </button>'
                        !!}
                    </div>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

    @include('pages.monitoring.modal_bimbingan.detail_bimbingan')
    @include('pages.monitoring.modal_bimbingan.edit_bimbingan')
    @include('pages.monitoring.modal_bimbingan.hapus_bimbingan')

@stop

@push('js')
    <script>
        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#bimbinganSkripsiTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#bimbinganSkripsiTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }

        function handleProgramStudiChange() {
            let selectedValue = document.getElementById('programStudiSelect').value;
            // Perform the necessary action when the state changes
            if (selectedValue === 'Semua') {
                const dataTable = $('#bimbinganSkripsiTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#bimbinganSkripsiTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
