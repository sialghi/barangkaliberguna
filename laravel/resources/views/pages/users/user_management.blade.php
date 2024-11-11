@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan Prodi Fakultas Sains dan Teknologi')

@section('css')
   <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
   <div class="d-flex flex-row">
      <h1>User Management</h1>
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

   <x-adminlte-button label="Tambah Pengguna" theme="primary" icon="fas fa-user-edit" onclick="window.location.href = '{{ route('user.add') }}';"/>
   <br><br>

   @php
      $heads = [
         ['label' => 'No', 'width' => 5],
         ['label' => 'Prodi', 'width' => 15],
         ['label' => 'Nama', 'width' => 15],
         ['label' => 'Email', 'width' => 8],
         ['label' => 'NIM/NIP/NIDN', 'width' => 10],
         ['label' => 'Role', 'width' => 8],
         ['label' => 'Status', 'width' => 7],
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
   <x-adminlte-datatable id="userTable" :heads="$heads" :config="$config" head-theme="dark" bordered hoverable beautify with-buttons>
      @foreach($data as $row)
         <tr>
            <td>{{ $totalRows - $loop->index }}</td>
            <td>
                @php
                    $containsDekan = false;
                @endphp

                @foreach($row->roles as $role)
                    @if(Str::contains(strtolower($role->nama), 'dekan'))
                        @php
                            $containsDekan = true;
                            break; // Exit the loop if 'dekan' is found
                        @endphp
                    @endif
                @endforeach

                @if($containsDekan)
                    {{ $row->fakultas->first()->nama }}
                @else
                    @foreach($row->programStudi as $prodi)
                        {{ $prodi->nama }}@if(!$loop->last),@endif
                    @endforeach
                @endif
            </td>
            <td>{{ $row->name }}</td>
            <td>{{ $row->email }}</td>
            <td>{{ $row->nim_nip_nidn }}</td>
            <td>
                @foreach($row->roles as $role)
                    {{ $role->nama }}@if(!$loop->last),@endif
                @endforeach
            </td>
            <td>
                @if ($row->deleted_at != null)
                    <span class="badge badge-dark">Dihapus</span>
                @elseif ($row->email_verified_at == null)
                    <span class="badge badge-danger">Belum Verifikasi</span>
                @else
                    <span class="badge badge-success">Terverifikasi</span>
                @endif
            </td>
            <td>
               <div class="flex justify-content-evenly">
                  {!!
                     '<button class="btn btn-xs btn-default bg-success text-white shadow rounded pt-1"
                     data-toggle="modal"
                     data-target="#detailUser"
                     data-row-id="'. $row->id .'">
                     <i class="fa fa-lg fa-fw fa-eye" title="Lihat Detail"></i>
                     </button>'
                  !!}
                    @if((array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) && $containsDekan == false)
                        {!!
                            '<button class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #FCAE1E"
                            data-toggle="modal"
                            data-target="#editUser"
                            data-row-id="'. $row->id .'">
                            <i class="far fa-edit fa-lg fa-fw" title="Edit Detail"></i>
                            </button>'
                        !!}
                     @elseif((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)))
                        {!!
                            '<button class="btn btn-xs btn-default text-white shadow rounded pt-1" style="background-color: #FCAE1E"
                            data-toggle="modal"
                            data-target="#editUser"
                            data-row-id="'. $row->id .'">
                            <i class="far fa-edit fa-lg fa-fw" title="Edit Detail"></i>
                            </button>'
                        !!}
                    @endif
                    @if((array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) && $containsDekan == false && $row->email_verified_at == null)
                        <form method="POST" action="{{ route('user.verify', ['id' => $row->id]) }}" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-xs btn-default bg-primary text-white shadow rounded" title="Verifikasi">
                                <i class="fas fa-lg fa-fw fa-user-check pt-1"></i>
                            </button>
                        </form>
                    @elseif((array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) && $containsDekan == false && $row->email_verified_at != null)
                    <form method="POST" action="{{ route('user.unverify', ['id' => $row->id]) }}" style="display: inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-xs btn-default bg-danger text-white shadow rounded" title="Hapus Verifikasi">
                            <i class="fas fa-lg fa-fw fa-user-times pt-1"></i>
                        </button>
                    </form>
                    @elseif((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)) && $row->email_verified_at == null)
                        <form method="POST" action="{{ route('user.verify', ['id' => $row->id]) }}" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-xs btn-default bg-primary text-white shadow rounded" title="Verifikasi">
                                <i class="fas fa-lg fa-fw fa-user-check pt-1"></i>
                            </button>
                        </form>
                    @elseif((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)) && $row->email_verified_at != null)
                        <form method="POST" action="{{ route('user.unverify', ['id' => $row->id]) }}" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-xs btn-default bg-danger text-white shadow rounded" title="Hapus Verifikasi">
                                <i class="fas fa-lg fa-fw fa-user-times pt-1"></i>
                            </button>
                        </form>
                    @endif
                  {{-- {!!
                     '<button class="btn btn-xs btn-default bg-purple text-white shadow rounded pt-1"
                     data-toggle="modal"
                     data-target="#updatePassUser"
                     data-row-id="'. $row->id .'">
                     <i class="fas fa-edit fa-lg fa-key" title="Ganti Password"></i>
                     </button>'
                  !!} --}}
                  @if ($row->deleted_at == null)
                    @if((array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) && $containsDekan == false)
                        {!!
                            '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded pt-1"
                            data-toggle="modal"
                            data-target="#hapusUser"
                            data-row-id="'.$row->id.'">
                            <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                            </button>'
                        !!}
                        @elseif((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)))
                            {!!
                                '<button class="btn btn-xs btn-default bg-secondary text-white shadow rounded pt-1"
                                data-toggle="modal"
                                data-target="#hapusUser"
                                data-row-id="'.$row->id.'">
                                <i class="fas fa-lg fa-fw fa-trash-alt" title="Hapus"></i>
                                </button>'
                            !!}
                        @endif
                    @elseif ($row->deleted_at != null)
                        @if((array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole)) && $containsDekan == false)
                            <form method="POST" action="{{ route('user.restore', ['id' => $row->id]) }}" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-xs btn-default bg-info text-white shadow rounded" title="Pulihkan">
                                    <i class="fas fa-lg fa-fw fa-trash-restore" title="Pulihkan"></i>
                                </button>
                            </form>
                        @elseif((array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole)))
                            <form method="POST" action="{{ route('user.restore', ['id' => $row->id]) }}" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-xs btn-default bg-info text-white shadow rounded" title="Pulihkan">
                                    <i class="fas fa-lg fa-fw fa-trash-restore" title="Pulihkan"></i>
                                </button>
                            </form>
                        @endif
                    @endif
               </div>
            </td>
         </tr>
      @endforeach
   </x-adminlte-datatable>

   @include('pages.users.modal.detail_user')
   @include('pages.users.modal.edit_user')
   @include('pages.users.modal.hapus_user')
@stop

@push('js')
    <script>
        function handleProgramStudiChangeDekanat() {
            // Perform the necessary action when the state changes
            let selectedValue = document.getElementById('programStudiSelectDekanat').value;

            if (selectedValue === 'Semua') {
                const dataTable = $('#userTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#userTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }

        function handleProgramStudiChange() {
            let selectedValue = document.getElementById('programStudiSelect').value;
            // Perform the necessary action when the state changes
            if (selectedValue === 'Semua') {
                const dataTable = $('#userTable').DataTable();
                dataTable.column(1).search('').draw(); // Filter by table column number 2 with all value
            } else {
                const dataTable = $('#userTable').DataTable();
                dataTable.column(1).search(selectedValue).draw(); // Filter by table column number 2 with all value
            }
        }
    </script>
@endpush
