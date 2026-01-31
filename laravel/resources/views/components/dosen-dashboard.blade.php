@props(['totalSuratPT', 'PTdiproses', 'PTditerima', 'PTditolak'])

<h3>Permohonan Surat Tugas oleh Anda (Dosen)</h3>
<div class="info_box">
    <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratPT }}" title="Total Surat"
        icon="fas fa-lg fa-inbox" />
    <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $PTdiproses }}" title="Sedang Diproses"
        icon="fas fa-lg fa-file" />
    <x-adminlte-info-box class="mr-3" theme="success" text="{{ $PTditerima }}" title="Diterima"
        icon="fas fa-lg fa-file-signature" />
    <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $PTditolak }}" title="Ditolak"
        icon="fas fa-lg fa-file-excel" />
</div>

<hr>

<div class="row mt-4">
    <div class="col-12">
        <h3>Statistik Mahasiswa Bimbingan</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="info-box bg-white">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total mahasiswa bimbingan</span>
                <!--  variable -->
                <span class="info-box-number">3</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sedang Berjalan (On-Going)</span>
                <!--  variable -->
                <span class="info-box-number">2</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai (Finished)</span>
                <!--  variable -->
                <span class="info-box-number">1</span>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">Rasio Status Mahasiswa</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-sm-8">
                        <canvas id="bimbinganChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                    <div class="col-sm-4">
                        <ul class="chart-legend clearfix list-unstyled">
                            <li><i class="fas fa-circle text-success"></i> Selesai (Finished)</li>
                            <li><i class="fas fa-circle text-warning"></i> Sedang Berjalan (On-Going)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 align-items-center mb-3">
    <div class="col">
        <h3>Monitoring Mahasiswa Bimbingan</h3>
    </div>
    <div class="col-auto">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary btn-filter" data-filter="all">Semua</button>
            <button type="button" class="btn btn-secondary btn-filter" data-filter="selesai">Selesai</button>
            <button type="button" class="btn btn-secondary btn-filter" data-filter="ongoing">Ongoing</button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">

        <x-table>
            <x-slot name="header">
                <th style="width: 20%">Nama Mahasiswa</th>
                <th style="width: 15%">NIM</th>
                <th style="width: 35%">Judul</th>
                <th style="width: 10%" class="text-center">Bimbingan</th>
                <th style="width: 20%" class="text-center">Status</th>
            </x-slot>

            <tr data-status="ongoing">
                <td>Klein Moretti</td>
                <td>11230910000097</td>
                <td>Prediksi Dampak Pada Benda Yang Jatuh Bebas Dengan Berat 80kg dan Panjang 175cm Dari
                    Ketinggian Yang Setara Dengan Atap GedungFST Menggunakan Random Forest</td>
                <td class="text-center">4</td>
                <td class="text-center">
                    <span class="badge badge-warning px-3 py-2"
                        style="font-size: 1rem; color: #d9534f; background-color: #f7e1c280;">Ongoing</span>
                </td>
            </tr>
            <tr data-status="ongoing">
                <td>Audrey Hall</td>
                <td>11230910000098</td>
                <td>testo</td>
                <td class="text-center">6</td>
                <td class="text-center">
                    <span class="badge badge-warning px-3 py-2"
                        style="font-size: 1rem; color: #d9534f; background-color: #f7e1c280;">Ongoing</span>
                </td>
            </tr>
            <tr data-status="selesai">
                <td>Alger Wilson</td>
                <td>11230910000099</td>
                <td>RANCANG BANGUN SISTEM KONTROL KOMPUTER DENGAN GERAKAN TANGAN MENGGUNAKAN FRAMEWORK
                    MEDIAPIPE</td>
                <td class="text-center">2</td>
                <td class="text-center">
                    <span class="badge badge-success px-3 py-2"
                        style="font-size: 1rem; background-color: #d4edda; color: #28a745;">Selesai</span>
                </td>
            </tr>

            <x-slot name="mobile">
                <div class="d-block d-md-none">
                    <x-student-card name="Klein Moretti" nim="11230910000097"
                        title="Prediksi Dampak Pada Benda Yang Jatuh Bebas Dengan Berat 80kg dan Panjang 175cm Dari Ketinggian Yang Setara Dengan Atap GedungFST Menggunakan Random Forest"
                        count="4" status="Ongoing" />
                    <x-student-card name="Audrey Hall" nim="11230910000098" title="testo" count="6" status="Ongoing" />
                    <x-student-card name="Alger Wilson" nim="11230910000099"
                        title="RANCANG BANGUN SISTEM KONTROL KOMPUTER DENGAN GERAKAN TANGAN MENGGUNAKAN FRAMEWORK MEDIAPIPE"
                        count="2" status="Selesai" />
                </div>
            </x-slot>
        </x-table>

    </div>
</div>