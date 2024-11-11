@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Nilai Seminar Hasil</h1>
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
                                    <th><img class="w-100" src="/img/panduan/btnInputNilai.png"/></th>
                                    <td>Tombol untuk pindah ke halaman "Input Nilai Seminar Hasil".</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnPrint.png"/></th>
                                    <td>Tombol untuk mencetak daftar seminar hasil.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnCsv.png"/></th>
                                    <td>Tombol untuk mengekspor daftar seminar hasil kedalam format CSV.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnExcel.png"/></th>
                                    <td>Tombol untuk mengekspor daftar seminar hasil kedalam format Excel.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnPdf.png"/></th>
                                    <td>Tombol untuk mengekspor daftar seminar hasil kedalam format PDF.</td>
                                </tr>
                            </table>
                        </div>
                        <div class="mt-4">
                            <p>Panduan Tabel</p>
                            <table>
                                <tr>
                                    <th>Nomor</th>
                                    <td>Nomor urut input nilai.</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>Tanggal input nilai.</td>
                                </tr>
                                <tr>
                                    <th>Nama</th>
                                    <td>Nama mahasiswa yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td>NIM mahasiswa yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Judul Skripsi</th>
                                    <td>Judul skripsi mahasiswa yang diinput.</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Seminar</th>
                                    <td>Tanggal seminar mahasiswa yang diinput.</td>
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
            ['label' => 'Nama', 'width' => 15],
            ['label' => 'NIM', 'width' => 10],
            ['label' => 'Judul Skripsi', 'width' => 20],
            ['label' => 'Tanggal Seminar', 'width' => 10],
            ['label' => 'Rata-Rata', 'width' => 10],
            ['label' => 'Penilaian', 'no-export' => true, 'width' => 5],
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

    @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-adminlte-button label="Input Jadwal" theme="primary" icon="fas fa-user-edit" onclick="window.location.href = '{{ route('add.nilai.seminar.hasil') }}';"/>
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

    <x-adminlte-datatable id="nilaiSemhasTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
        @foreach($data as $row)
            <tr>
                <td>{{ $totalRows - $loop->index }}</td>
                <td>
                    @foreach($row->mahasiswa->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                </td>
                <td>
                    {{ $row->mahasiswa->name }}
                </td>
                <td>{{ $row->mahasiswa->nim_nip_nidn }}</td>
                <td class="text-truncate" style="max-width: 250px;" title="{{ $row->judul_skripsi }}">
                    {{ Str::limit($row->judul_skripsi, 50, '...') }}
                </td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal_seminar)->locale('id')->isoFormat('D MMMM Y') }}</td>
                <td>
                    @php
                        $values = [
                            $row->nilai_pembimbing_1,
                            $row->nilai_pembimbing_2,
                            $row->nilai_penguji_1,
                            $row->nilai_penguji_2
                        ];
                        $filteredValues = array_filter($values, function($value) {
                            return !is_null($value);
                        });
                        $sum = array_sum($filteredValues);
                        $count = count($filteredValues);
                        $average = $sum / 4;
                    @endphp
                    {{ $average }}
                </td>
                <td>
                    @if ($count != 4)
                        <span class="badge bg-danger text-sm">{{ $count }}/4</span>
                    @else
                        <span class="badge bg-success text-sm">Lengkap!</span>
                    @endif
                </td>
                <td>
                    {!!
                        '<button class="btn btn-xs btn-default bg-success text-white shadow rounded pt-1"
                            data-toggle="modal"
                            data-target="#lihatNilaiModal"
                            data-row-id="'. $row->id .'">
                            <i class="fa fa-lg fa-fw fa-eye" title="Lihat Nilai"></i>
                        </button>'
                    !!}
                    @if(
                        $user->id === $row->id_pembimbing_1 ||
                        $user->id === $row->id_pembimbing_2 ||
                        $user->id === $row->id_penguji_1 ||
                        $user->id === $row->id_penguji_2 ||
                        (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                        ($row->role === 'admin'))
                    )
                        {!!
                            '<button class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #FCAE1E"
                                data-toggle="modal"
                                data-target="#tambahNilaiModal"
                                data-row-id="'. $row->id .'">
                                <i class="fa fa-lg fa-fw fa-pen" title="Tambah Nilai"></i>
                            </button>'
                        !!}
                    @endif
                    @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi', 'dosen'], $userRole) &&
                        ($row->role === 'admin' || $row->role === 'dosen'))
                        @if ($row->nilai_pembimbing_1 && $row->nilai_pembimbing_2 && $row->nilai_penguji_1 && $row->nilai_penguji_2)
                            <a href="{{route('generate.docx.nilai.seminar.hasil', Crypt::encryptString($row->id))}}" class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #AC94F4" title="Download Nilai">
                                <i class="fa fa-lg fa-fw fa-download"></i>
                            </a>
                        @endif
                    @endif
                    @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                        ($row->role === 'admin'))
                        @if ($row->nilai_pembimbing_1 && $row->nilai_pembimbing_2 && $row->nilai_penguji_1 && $row->nilai_penguji_2)
                            <a href="{{route('send.email.nilai.seminar.hasil', Crypt::encryptString($row->id))}}" class="btn btn-xs btn-default text-white shadow rounded bg-primary pt-1" title="Kirim Email">
                                <i class="fas fa-lg fa-fw fa-paper-plane"></i>
                            </a>
                        @endif
                    @endif
                    @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole) &&
                        ($row->role === 'admin'))
                        {!!
                            '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded pt-1"
                            data-toggle="modal"
                            data-target="#hapusPenilaian"
                            data-row-id="'.$row->id.'">
                            <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                            </button>'
                        !!}
                    @endif
                </td>
            </tr>
        @endforeach
    </x-adminlte-datatable>

    @include('pages.semhas.modal_penilaian.detail_penilaian')
    @include('pages.semhas.modal_penilaian.tambah_penilaian')
    @include('pages.semhas.modal_penilaian.hapus_penilaian')

@stop

@push('js')
    <script>
        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#nilaiSemhasTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#nilaiSemhasTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }

        function handleProgramStudiChange() {
            let selectedValue = document.getElementById('programStudiSelect').value;
            // Perform the necessary action when the state changes
            if (selectedValue === 'Semua') {
                const dataTable = $('#nilaiSemhasTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#nilaiSemhasTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
