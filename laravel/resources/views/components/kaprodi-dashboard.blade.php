@props(['totalDosen', 'totalMhs', 'prodiOngoing', 'prodiSelesai', 'monitoringDosen'])

<hr>
<div class="row mt-4">
    <div class="col-12">
        <h3>Statistik Bimbingan</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <x-adminlte-info-box theme="white" text="{{ $totalDosen }}" title="Total Dosen" icon="fas fa-chalkboard-teacher text-dark" />
        <x-adminlte-info-box theme="white" text="{{ $totalMhs }}" title="Total Mahasiswa Bimbingan" icon="fas fa-users text-info" />
        <x-adminlte-info-box theme="white" text="{{ $prodiOngoing }}" title="Sedang Berjalan (On-Going)" icon="fas fa-spinner text-warning" />
        <x-adminlte-info-box theme="white" text="{{ $prodiSelesai }}" title="Selesai (Finished)" icon="fas fa-check-circle text-success" />
    </div>

    <div class="col-md-7">
        <x-chart-card title="Bimbingan per Kategori" id="bimbinganChartProdi">
            <ul class="list-unstyled">
                <li><i class="fas fa-circle text-success"></i> Selesai ({{ $prodiSelesai }})</li>
                <li><i class="fas fa-circle text-warning"></i> On-Going ({{ $prodiOngoing }})</li>
            </ul>
        </x-chart-card>
    </div>
</div>

<div class="row mt-4 mb-3">
    <div class="col-12">
        <h3>Monitoring Dosen</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <x-table>
            <x-slot name="header">
                <th style="width: 30%">Nama Dosen</th>
                <th style="width: 20%">Mahasiswa</th>
                <th style="width: 25%" class="text-center">On-Going</th>
                <th style="width: 25%" class="text-center">Finished</th>
            </x-slot>

            <tbody id="desktop-dosen-accordion">
                @foreach($monitoringDosen as $dsn)
                <x-dosen-row :id="'dsn'.$dsn->id" :name="$dsn->nama" prodi="Aktif" :count="$dsn->total_mhs" :ongoing="$dsn->ongoing" :finished="$dsn->finished" parent="#desktop-dosen-accordion">
                    @foreach($dsn->students as $mhs)
                    <x-student-row :name="$mhs->nama_mahasiswa" :nim="$mhs->nim_mahasiswa" :title="$mhs->judul_skripsi" :count="$mhs->sesi" :status="is_null($mhs->id_nilai_sempro) ? 'Ongoing' : 'Selesai'" />
                    @endforeach
                </x-dosen-row>
                @endforeach
            </tbody>

            <x-slot name="mobile">
                <div class="d-block d-md-none pb-3" id="mobile-dosen-accordion">
                    @foreach($monitoringDosen as $dsn)
                    <x-dosen-mobile-card :id="'mob'.$dsn->id" :name="$dsn->nama" prodi="Aktif" :count="$dsn->total_mhs" :ongoing="$dsn->ongoing" :finished="$dsn->finished" parent="#mobile-dosen-accordion">
                        @foreach($dsn->students as $mhs)
                        <x-student-card :name="$mhs->nama_mahasiswa" :nim="$mhs->nim_mahasiswa" :title="$mhs->judul_skripsi" :count="$mhs->sesi" :status="is_null($mhs->id_nilai_sempro) ? 'Ongoing' : 'Selesai'" />
                        @endforeach
                    </x-dosen-mobile-card>
                    @endforeach
                </div>
            </x-slot>
        </x-table>
    </div>
</div>