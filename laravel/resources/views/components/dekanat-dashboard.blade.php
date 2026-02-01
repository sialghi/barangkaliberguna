@props(['monitoringDekanat'])

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0 bg-white">
                <h3 class="card-title font-weight-bold" style="font-size: 1.75rem;">Statistik Bimbingan</h3>
            </div>
            <div class="card-body">
                <div style="width: 100%; overflow-x: auto;">
                    <div style="min-width: 800px; height: 400px;">
                        <canvas id="dekanatChart" style="height: 100%; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 mb-3">
    <div class="col-md-12">
        <h2>Monitoring Dosen (Dekanat)</h2>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <select class="form-control" id="prodiFilterDekanat">
                <option value="all">Semua Jurusan</option>
                @foreach($monitoringDekanat->pluck('prodi')->unique() as $prodiName)
                    <option value="{{ $prodiName }}">{{ $prodiName }}</option>
                @endforeach
            </select>
        </div>
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

            <tbody id="dekanat-dosen-accordion">
                @foreach($monitoringDekanat as $dsn)
                    <x-dosen-row 
                        :id="'dek-dsn-' . $dsn->id" 
                        :name="$dsn->nama" 
                        :prodi="$dsn->prodi" 
                        :count="$dsn->total_mhs" 
                        :ongoing="$dsn->ongoing"
                        :finished="$dsn->finished" 
                        parent="#dekanat-dosen-accordion" 
                        :data-prodi="$dsn->prodi"
                    >
                        @if($dsn->students->count() > 0)
                            @foreach($dsn->students as $mhs)
                                <x-student-row 
                                    :name="$mhs->name" 
                                    :nim="$mhs->nim_nip_nidn" 
                                    :title="$mhs->judul_skripsi" 
                                    :count="$mhs->sesi"
                                    :status="is_null($mhs->id_nilai_sempro) ? 'Ongoing' : 'Selesai'" 
                                />
                            @endforeach
                        @else
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data bimbingan.</td></tr>
                        @endif
                    </x-dosen-row>
                @endforeach
            </tbody>

            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted text-sm" id="totalDosenText">Total: {{ $monitoringDekanat->count() }} Dosen</span>
                    {{-- Navigasi Pagination tetap di sini --}}
                </div>
            </x-slot>

            {{-- Mobile Version --}}
            <x-slot name="mobile">
                <div class="d-block d-md-none pb-3" id="mobile-dekanat-dosen-accordion">
                    @foreach($monitoringDekanat as $dsn)
                        <x-dosen-mobile-card 
                            :id="'dek-mob-' . $dsn->id" 
                            :name="$dsn->nama" 
                            :prodi="$dsn->prodi"
                            :count="$dsn->total_mhs" 
                            :ongoing="$dsn->ongoing" 
                            :finished="$dsn->finished" 
                            parent="#mobile-dekanat-dosen-accordion"
                            :data-prodi="$dsn->prodi"
                        >
                            @foreach($dsn->students as $mhs)
                                <x-student-card 
                                    :name="$mhs->name" 
                                    :nim="$mhs->nim_nip_nidn" 
                                    :title="$mhs->judul_skripsi" 
                                    :count="$mhs->sesi" 
                                    :status="is_null($mhs->id_nilai_sempro) ? 'Ongoing' : 'Selesai'" 
                                />
                            @endforeach
                        </x-dosen-mobile-card>
                    @endforeach
                </div>
            </x-slot>
        </x-table>
    </div>
</div>