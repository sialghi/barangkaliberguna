@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Permohonan Surat Tugas</h1>
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
                <div class="modal-body">
                    <div id="panduanSection">
                        <div>
                            <p>Panduan Tombol</p>
                            <table>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnBuatSurat.png"/></th>
                                    <td>Tombol untuk pindah ke halaman "Pengajuan Surat".</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnPrint.png"/></th>
                                    <td>Tombol untuk mencetak daftar surat.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnCsv.png"/></th>
                                    <td>Tombol untuk mengekspor daftar surat kedalam format CSV.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnExcel.png"/></th>
                                    <td>Tombol untuk mengekspor daftar surat kedalam format Excel.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnPdf.png"/></th>
                                    <td>Tombol untuk mengekspor daftar surat kedalam format PDF.</td>
                                </tr>
                            </table>
                        </div>
                        <div class="mt-4">
                            <p>Panduan Tabel</p>
                            <table>
                                <tr>
                                    <th>Nomor</th>
                                    <td>Nomor urut surat yang sudah diunggah.</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>Tanggal surat diunggah.</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td>Nama dosen yang mengunggah dokumen.</td>
                                </tr>
                                <tr>
                                    <th>NIP/NIDN</th>
                                    <td>NIP/NIDN dosen yang mengunggah dokumen.</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi Surat</th>
                                    <td>Deskripsi dokumen yang diunggah.</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>Status dokumen yang diunggah.</td>
                                </tr>
                                <tr>
                                    <th>Tanggal di TTD</th>
                                    <td>Tanggal surat ketika di TTD.</td>
                                </tr>
                                <tr>
                                    <th>Aksi</th>
                                    <td>Beberapa aksi yang bisa dilakukan.</td>
                                </tr>
                            </table>
                        </div>
                    </div>
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

    @if ($errors->has('uploadFileBaru'))
        <x-adminlte-alert id="error-alert" theme="danger" title="Error">
            {{ $errors->first('uploadFileBaru') }}
        </x-adminlte-alert>
        <script>
            setTimeout(function() {
                document.getElementById('error-alert').style.display = 'none';
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
            ['label' => 'Tanggal Input', 'width' => 10],
            ['label' => 'Nama', 'width' => 15],
            ['label' => 'NIP/NIDN', 'width' => 10],
            ['label' => 'Deskripsi Surat', 'width' => 25],
            ['label' => 'Status', 'width' => 13],
            ['label' => 'Aksi', 'no-export' => true, 'width' => 20],
        ];

        $config = [
            'order' => [[0, 'asc']],
            'language' => ['url' => '/json/datatables-id.json'],
            'columns' => [null, null, null, null, null, null, null, ['orderable' => false]],
        ];

        $totalRows = count($data);
        $counter = 0;

        $uniqueProgramStudi = $userPivot->pluck('programStudi.nama')->unique();
    @endphp

    @if(array_intersect(['kaprodi', 'sekprodi', 'admin_prodi', 'dosen'], $userRole))
        <x-adminlte-button label="Buat Surat" theme="primary" icon="fas fa-file-upload" onclick="window.location.href = '{{ route('add.surat.tugas') }}'"/>
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
    <x-adminlte-datatable id="letterTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
        @foreach($data as $row)
            <tr>
                <td>{{ $totalRows - $loop->index }}</td>
                <td>
                    @foreach($row->dosen->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                </td>
                <td>{{ \Carbon\Carbon::parse($row->created_at)->locale('id')->isoFormat('D MMMM Y HH:mm') }}</td>
                <td>{{ $row->dosen->name }}</td>
                <td>{{ $row->dosen->nim_nip_nidn }}</td>
                <td class="text-truncate" style="max-width: 250px;" title="{{ $row->deskripsi_surat }}">
                    {{ Str::limit($row->deskripsi_surat, 50, '...') }}
                </td>
                <td>
                    @if ($row->status == 'Sedang Diproses')
                        <span class="badge" style="background-color: #FCAE1E">{{$row->status}}</span>
                    @elseif ($row->status == 'Diterima')
                        <span class="badge bg-success">
                            {{$row->status}}
                        </span>
                        <span class="badge bg-success">
                            {{\Carbon\Carbon::parse($row->updated_at)->locale('id')->isoFormat('D MMMM Y HH:mm')}}
                        </span>
                    @elseif ($row->status == 'Ditolak')
                        {!!
                            '<button class="btn btn-xs btn-default text-white bg-danger rounded shadow btnAlasan" title="View"
                                data-toggle="modal" data-target="#alasanPenolakan" data-reason= "'. $row->alasan_penolakan .'" >
                                <span>Ditolak</span><i class="fa fa-lg fa-fw fa-eye mx-1"></i>
                            </button>'
                        !!}
                    @endif
                </td>
                <td>
                    <a href="{{route('download.file.input.surat.tugas', Crypt::encryptString($row->id))}}" target="_blank" class="btn btn-xs btn-default bg-info text-white shadow rounded pt-1" title="Lihat Surat Masukan">
                        <i class="fas fa-lg fa-fw fa-file-pdf"></i>
                    </a>
                    @if ($row->file_2)
                        <a href="{{route('download.file.output.surat.tugas', Crypt::encryptString($row->id))}}" target="_blank" class="btn btn-xs btn-default bg-danger text-white shadow rounded pt-1" title="Lihat Surat Keluaran">
                            <i class="fas fa-lg fa-fw fa-file-pdf"></i>
                        </a>
                    @endif
                @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                    $row->role === 'admin')
                    <button class="btn btn-xs btn-default text-white shadow rounded bg-primary pt-1" title="Unggah" data-toggle="modal" data-target="#uploadFileModal"
                        @if ($row->status === "Diterima" || $row->status ==="Ditolak")
                            hidden disabled
                        @endif
                        data-id={{$row->id}}>
                        <i class="fa fa-lg fa-fw fa-upload"></i>
                    </button>
                    <button class="btn btn-xs btn-default text-white bg-danger shadow buttonTolak pt-1" title="Tolak" data-toggle="modal" data-target="#tolakLaporan"
                        @if ($row->status === "Diterima" || $row->status ==="Ditolak")
                            hidden disabled
                        @endif
                        data-id={{$row->id}}>
                        <i class="fa fa-lg fa-fw fa-ban"></i>
                    </button>
                    {!!
                        '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded"
                        data-toggle="modal"
                        data-target="#hapusPermohonanTugas"
                        data-row-id="'.$row->id.'">
                        <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                        </button>'
                    !!}
                @endif
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

    @include('pages.persuratan.modal_permohonan_tugas.alasan_penolakan')
    @include('pages.persuratan.modal_permohonan_tugas.tolak_laporan')
    @include('pages.persuratan.modal_permohonan_tugas.upload_file')
    @include('pages.persuratan.modal_permohonan_tugas.hapus_permohonan_tugas')

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
                const dataTable = $('#letterTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#letterTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
        function handleProgramStudiChange() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelect').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#letterTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#letterTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
