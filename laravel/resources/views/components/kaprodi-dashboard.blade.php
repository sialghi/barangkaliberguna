@props(['totalDosen', 'totalMhs', 'prodiOngoing', 'prodiSelesai', 'monitoringDosen'])

<hr>
<div class="row mt-4">
    <div class="col-12">
        <h3>Statistik Bimbingan</h3>
    </div>
</div>

{{-- BAGIAN INFO BOX & CHART (Tetap Sama Sesuai Desain) --}}
<div class="row">
    <div class="col-md-5">
        <div class="info-box bg-white">
            <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Dosen</span>
                <span class="info-box-number">{{ $totalDosen }}</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Mahasiswa Bimbingan</span>
                <span class="info-box-number">{{ $totalMhs }}</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sedang Berjalan (On-Going)</span>
                <span class="info-box-number">{{ $prodiOngoing }}</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai (Finished)</span>
                <span class="info-box-number">{{ $prodiSelesai }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <x-chart-card title="Bimbingan per Kategori" id="bimbinganChartProdi">
            <ul class="list-unstyled">
                <li><i class="fas fa-circle text-success"></i> Selesai ({{ $prodiSelesai }})</li>
                <li><i class="fas fa-circle text-warning"></i> Sedang Berjalan ({{ $prodiOngoing }})</li>
            </ul>
        </x-chart-card>
    </div>
</div>

{{-- MONITORING DOSEN SECTION --}}
<div class="row mt-4 mb-3">
    <div class="col-12">
        <h3>Monitoring Dosen</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <x-table>
            <x-slot name="header">
                <th style="width: 25%">Nama Dosen</th>
                <th style="width: 25%">Prodi</th>
                <th style="width: 20%">Mahasiswa</th>
                <th style="width: 15%" class="text-center">On-Going</th>
                <th style="width: 15%" class="text-center">Finished</th>
            </x-slot>

            {{-- DESKTOP BODY --}}
            <tbody id="desktop-dosen-accordion">
                @foreach($monitoringDosen as $dsn)
                    {{-- Panggil Component Child (Dosen Row) --}}
                    {{-- Component ini sudah punya tombol filter lokal di dalamnya --}}
                    <x-dosen-row 
                        :id="'dsn'.$dsn->id" 
                        :name="$dsn->nama" 
                        prodi="Teknik Informatika" 
                        :count="$dsn->total_mhs" 
                        :ongoing="$dsn->ongoing" 
                        :finished="$dsn->finished" 
                        parent="#desktop-dosen-accordion"
                    >
                        @if($dsn->students->count() > 0)
                            @foreach($dsn->students as $mhs)
                                @php 
                                    // Logika Status
                                    $isSelesai = $mhs->is_finished == 1;
                                    $statusSlug = $isSelesai ? 'selesai' : 'ongoing';
                                @endphp
                                
                                {{-- PERBAIKAN: Kirim data-status untuk filter JS --}}
                                <x-student-row 
                                    :name="$mhs->nama_mahasiswa" 
                                    :nim="$mhs->nim_mahasiswa" 
                                    :title="$mhs->judul_skripsi" 
                                    :count="$mhs->sesi" 
                                    :status="$isSelesai ? 'Selesai' : 'Ongoing'"
                                    :data-status="$statusSlug"
                                />
                            @endforeach
                        @else
                            {{-- Pesan Kosong --}}
                            <tr>
                                <td colspan="5" class="text-center text-muted italic">Tidak ada data bimbingan.</td>
                            </tr>
                        @endif
                    </x-dosen-row>
                @endforeach
            </tbody>

            {{-- MOBILE BODY --}}
            <x-slot name="mobile">
                <div class="d-block d-md-none pb-3" id="mobile-dosen-accordion">
                    @foreach($monitoringDosen as $dsn)
                        <x-dosen-mobile-card 
                            :id="'mob'.$dsn->id" 
                            :name="$dsn->nama" 
                            prodi="Teknik Informatika" 
                            :count="$dsn->total_mhs" 
                            :ongoing="$dsn->ongoing" 
                            :finished="$dsn->finished" 
                            parent="#mobile-dosen-accordion"
                        >
                            @foreach($dsn->students as $mhs)
                                @php 
                                    $isSelesai = $mhs->is_finished == 1;
                                    $statusSlug = $isSelesai ? 'selesai' : 'ongoing';
                                @endphp
                                
                                {{-- Card Mobile dengan Status --}}
                                <x-student-card 
                                    :name="$mhs->nama_mahasiswa" 
                                    :nim="$mhs->nim_mahasiswa" 
                                    :title="$mhs->judul_skripsi" 
                                    :count="$mhs->sesi" 
                                    :status="$isSelesai ? 'Selesai' : 'Ongoing'"
                                    :data-status="$statusSlug"
                                />
                            @endforeach
                        </x-dosen-mobile-card>
                    @endforeach

                    {{-- Mobile Pagination (UI Saja) --}}
                    <div class="d-flex flex-column align-items-center mt-3">
                        <span class="text-muted text-sm mb-2">Halaman 1 dari 5 ({{ $monitoringDosen->count() }} Dosen)</span>
                        <div>
                            <button class="btn btn-sm btn-light border mr-1"><i class="fas fa-chevron-left text-xs"></i> Sebelumnya</button>
                            <button class="btn btn-sm btn-white border font-weight-bold">Selanjutnya <i class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                </div>
            </x-slot>

            {{-- Desktop Pagination UI --}}
            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted text-sm">Halaman 1 dari 5 ({{ $monitoringDosen->count() }} Dosen)</span>
                    <div>
                        <button class="btn btn-sm btn-light border mr-1"><i class="fas fa-chevron-left text-xs"></i> Sebelumnya</button>
                        <button class="btn btn-sm btn-white border font-weight-bold">Selanjutnya <i class="fas fa-chevron-right text-xs"></i></button>
                    </div>
                </div>
            </x-slot>
        </x-table>
    </div>
</div>