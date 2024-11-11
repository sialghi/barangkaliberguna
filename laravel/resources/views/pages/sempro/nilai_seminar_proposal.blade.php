@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Nilai Seminar Proposal</h1>
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
            ['label' => 'Prodi', 'width' => 5],
            ['label' => 'Periode', 'width' => 8],
            ['label' => 'Nama', 'width' => 15],
            ['label' => 'NIM', 'width' => 10],
            ['label' => 'Judul Proposal', 'width' => 25],
            ['label' => 'Ketua Penguji', 'width' => 10],
            ['label' => 'Status', 'width' => 7],
            ['label' => 'Aksi', 'no-export' => true, 'width' => 20],
        ];

        $config = [
            'order' => [[0, 'asc']],
            'language' => ['url' => '/json/datatables-id.json'],
            'columns' => [null, null, null, null, null, null, null, ['orderable' => false], ['orderable' => false]],
        ];

        $totalRows = count($data);

        $uniqueProgramStudi = $userPivot->pluck('programStudi.nama')->unique();
    @endphp

    @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-adminlte-button label="Input Nilai" theme="primary" icon="fas fa-user-edit" onclick="window.location.href = '{{ route('add.nilai.seminar.proposal') }}';"/>
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
    <x-adminlte-datatable id="nilaiSemproTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
        @foreach($data as $row)
            <tr>
                <td>{{ $totalRows - $loop->index }}</td>
                <td>
                    @foreach($row->mahasiswa->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                </td>
                <td>{{ $row->periodeSempro->periode }}</td>
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
                <td class="text-truncate" style="max-width: 250px;" title="{{ $row->judul_proposal }}">
                    {{ Str::limit($row->judul_proposal, 50, '...') }}
                </td>
                @if ($row->penguji1)
                    <td>{{ $row->penguji1->name }}</td>
                @else
                    <td>None</td>
                @endif
                <td class="text-md">
                    @if (
                        $row->id_penguji_1 === null ||
                        $row->id_penguji_2 === null ||
                        $row->id_penguji_3 === null ||
                        $row->id_penguji_4 === null ||
                        $row->id_pembimbing_1 === null ||
                        $row->id_pembimbing_2 === null
                    )
                        <span class="badge" style="background-color: #FCAE1E">Data Belum Lengkap&nbsp;<i class="fas fa-exclamation text-black fs-1"></i></span>
                    @endif
                    @if ($row->status == 'Sedang Diproses')
                        <span class="badge" style="background-color: #FCAE1E">{{ $row->status }}</span>
                    @elseif ($row->status == 'Diterima')
                        <span class="badge bg-success"> Selesai </span>
                    @elseif ($row->status == 'Ditolak')
                        <span class="badge bg-danger">{{ $row->status }}</span>
                    @elseif ($row->status == 'Revisi')
                        <span class="badge bg-primary">{{ $row->status }}</span>
                    @endif
                </td>
                <td>
                    <div class="flex justify-content-evenly">
                        {!!
                            '<button class="btn btn-xs btn-default bg-success text-white shadow rounded pt-1"
                            data-toggle="modal"
                            data-target="#detailNilai"
                            data-row-id="'. $row->id .'">
                            <i class="fa fa-lg fa-fw fa-eye" title="Lihat Detail"></i>
                            </button>'
                        !!}
                        @if (
                            (optional($row->penguji1)->id === auth()->user()->id ||
                            array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                            $row->role === 'admin') &&
                            ($row->status === 'Revisi' || $row->status === 'Sedang Diproses'))
                            {!!
                                '<button class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #FCAE1E"
                                data-toggle="modal"
                                data-target="#editNilai"
                                data-row-id="'. $row->id .'">
                                <i class="far fa-edit fa-lg fa-fw" title="Edit Detail"></i>
                                </button>'
                            !!}
                        @endif
                        @if (
                            optional($row->penguji1)->id === auth()->user()->id ||
                            optional($row->penguji2)->id === auth()->user()->id ||
                            optional($row->penguji3)->id === auth()->user()->id ||
                            optional($row->penguji4)->id === auth()->user()->id
                            )
                            {!!
                                '<button class="btn btn-xs btn-default text-muted shadow rounded bg-muted pt-1"
                                style="border: 1px solid #6c757d"
                                data-toggle="modal"
                                data-target="#catatanNilaiSemproPenguji"
                                data-row-id="'. $row->id .'">
                                <i class="fas fa-comment-dots fa-lg" title="Edit Catatan"></i>
                                </button>'
                            !!}
                        @endif
                        {!!
                            '<button class="btn btn-xs btn-default text-white shadow rounded bg-primary pt-1"
                            data-toggle="modal"
                            data-target="#detailCatatanNilaiSempro"
                            data-row-id="'. $row->id .'">
                            <i class="fas fa-comments fa-lg" title="Catatan Penguji"></i>
                            </button>'
                        !!}
                        @if((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi', 'dosen'], $userRole)) &&
                            $row->penguji_1_id !== null &&
                            $row->penguji_2_id !== null &&
                            $row->penguji_3_id !== null &&
                            $row->penguji_4_id !== null &&
                            $row->status === 'Diterima')
                            <a href="{{route('generate.docx.seminar.proposal', Crypt::encryptString($row->id))}}" class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #AC94F4" title="Download Nilai">
                                <i class="fa fa-lg fa-fw fa-download"></i>
                            </a>
                        @endif
                        @if (optional($row->penguji1)->id === auth()->user()->id ||
                            (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                            $row->role === 'admin'))
                            {!!
                                '<button class="btn btn-xs btn-default bg-danger text-white shadow rounded pt-1"
                                data-toggle="modal"
                                data-target="#aksiNilai"
                                data-row-id="'.$row->id.'">
                                <i class="fas fa-lg fa-fw fa-fist-raised" title="Aksi"></i>
                                </button>'
                            !!}
                            @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                                $row->role === 'admin')
                            {!!
                                '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded pt-1"
                                data-toggle="modal"
                                data-target="#hapusNilai"
                                data-row-id="'.$row->id.'">
                                <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                                </button>'
                            !!}
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

    @include('pages.sempro.modal_penilaian.detail_penilaian')
    @include('pages.sempro.modal_penilaian.detail_catatan_penilaian')
    @include('pages.sempro.modal_penilaian.edit_penilaian')
    @include('pages.sempro.modal_penilaian.edit_catatan_penilaian')
    @include('pages.sempro.modal_penilaian.aksi_penilaian')
    @include('pages.sempro.modal_penilaian.hapus_penilaian')
    @include('modal_user_profile')
@stop

@push('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function handleProgramStudiChangeDekanat() {
        // Perform the necessary action when the state changes
        let selectedValue = document.getElementById('programStudiSelectDekanat').value;

        if (selectedValue === 'Semua') {
            const dataTable = $('#nilaiSemproTable').DataTable();
            dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
        } else {
            const dataTable = $('#nilaiSemproTable').DataTable();
            dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
        }
    }
    function handleProgramStudiChange() {
        // Perform the necessary action when the state changes
        let selectedValue = document.getElementById('programStudiSelect').value;

        if (selectedValue === 'Semua') {
            const dataTable = $('#nilaiSemproTable').DataTable();
            dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
        } else {
            const dataTable = $('#nilaiSemproTable').DataTable();
            dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
        }
    }
</script>
@endpush
