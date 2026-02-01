@props([
    'totalSuratPT', 
    'PTdiproses', 
    'PTditerima', 
    'PTditolak', 
    'bimbingan', 
    'totalBimbingan', 
    'bimbinganOngoing', 
    'bimbinganSelesai'
])

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
                <span class="info-box-number">{{ $totalBimbingan }}</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sedang Berjalan (On-Going)</span>
                <span class="info-box-number">{{ $bimbinganOngoing }}</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai (Finished)</span>
                <span class="info-box-number">{{ $bimbinganSelesai }}</span>
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
                            <li><i class="fas fa-circle text-success"></i> Selesai ({{ $bimbinganSelesai }})</li>
                            <li><i class="fas fa-circle text-warning"></i> On-Going ({{ $bimbinganOngoing }})</li>
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
                <th style="width: 10%" class="text-center">Sesi</th>
                <th style="width: 20%" class="text-center">Status</th>
            </x-slot>

            @foreach($bimbingan as $item)
                @php 
                    $isSelesai = !is_null($item->id_nilai_sempro);
                    $statusSlug = $isSelesai ? 'selesai' : 'ongoing';
                @endphp
                <tr data-status="{{ $statusSlug }}">
                    <td>{{ $item->nama_mahasiswa }}</td>
                    <td>{{ $item->nim }}</td>
                    <td>{{ $item->judul_skripsi }}</td>
                    <td class="text-center">{{ $item->jumlah_bimbingan }}</td>
                    <td class="text-center">
                        @if($isSelesai)
                            <span class="badge badge-success px-3 py-2"
                                style="font-size: 0.9rem; background-color: #d4edda; color: #28a745;">Selesai</span>
                        @else
                            <span class="badge badge-warning px-3 py-2"
                                style="font-size: 0.9rem; color: #856404; background-color: #fff3cd;">Ongoing</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            <x-slot name="mobile">
                <div class="d-block d-md-none">
                    @foreach($bimbingan as $item)
                        @php 
                            $isSelesai = !is_null($item->id_nilai_sempro);
                            $statusSlug = $isSelesai ? 'selesai' : 'ongoing';
                        @endphp
                        
                        {{-- TAMBAHKAN PEMBUNGKUS DIV DENGAN DATA-STATUS --}}
                        <div data-status="{{ $statusSlug }}">
                            <x-student-card 
                                :name="$item->nama_mahasiswa" 
                                :nim="$item->nim"
                                :title="$item->judul_skripsi" 
                                :count="$item->jumlah_bimbingan" 
                                :status="$isSelesai ? 'Selesai' : 'Ongoing'" 
                            />
                        </div>
                        
                    @endforeach
                </div>
            </x-slot>
        </x-table>
    </div>
</div>