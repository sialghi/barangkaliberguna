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

{{-- Monitoring Dosen Section (Dekanat) --}}
<div class="row mt-4 mb-3">
    <div class="col-md-12">
        <h2>Monitoring Dosen (Dekanat)</h2>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <select class="form-control" id="prodiFilterDekanat">
                <option value="all">Semua Jurusan</option>
                <option value="Sistem Informasi">Sistem Informasi</option>
                <option value="Teknik Informatika">Teknik Informatika</option>
                <option value="Teknik Pertambangan">Teknik Pertambangan</option>
                <option value="Fisika">Fisika</option>
                <option value="Biologi">Biologi</option>
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
                {{-- Dosen 1 --}}
                <x-dosen-row id="dek-dosen1" name="Prof. Ahmad, M.T" prodi="Sistem Informasi" count="9" ongoing="6"
                    finished="3" parent="#dekanat-dosen-accordion" data-prodi="Sistem Informasi">
                    <x-student-row name="Klein Moretti" nim="11230910000097" title="Prediksi Dampak Pada Benda..."
                        count="2" status="Ongoing" />
                </x-dosen-row>

                {{-- Dosen 2 --}}
                <x-dosen-row id="dek-dosen2" name="Budi Sukario, M.Sc" prodi="Teknik Informatika" count="5" ongoing="5"
                    finished="0" parent="#dekanat-dosen-accordion" data-prodi="Teknik Informatika">
                    <x-student-row name="Student A" nim="11230910000001" title="Lorem ipsum..." count="5"
                        status="Ongoing" />
                </x-dosen-row>

                {{-- Dosen 3 --}}
                <x-dosen-row id="dek-dosen3" name="Azik Eaggers" prodi="Teknik Pertambangan" count="10" ongoing="6"
                    finished="4" parent="#dekanat-dosen-accordion" data-prodi="Teknik Pertambangan">
                    <x-student-row name="Student B" nim="11230910000002" title="Mining Data..." count="3"
                        status="Ongoing" />
                </x-dosen-row>

                {{-- Dosen 4 --}}
                <x-dosen-row id="dek-dosen4" name="Dunn Smith" prodi="Fisika" count="8" ongoing="6" finished="2"
                    parent="#dekanat-dosen-accordion" data-prodi="Fisika">
                    <x-student-row name="Student C" nim="11230910000003" title="Quantum Physics..." count="1"
                        status="Ongoing" />
                </x-dosen-row>
            </tbody>

            {{-- Desktop Pagination for Dekanat Table --}}
            <x-slot name="footer">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted text-sm">Halaman 1 dari 5 (20 Dosen)</span>
                    <div>
                        <button class="btn btn-sm btn-light border mr-1"><i class="fas fa-chevron-left text-xs"></i>
                            Sebelumnya</button>
                        <button class="btn btn-sm btn-white border font-weight-bold">Selanjutnya <i
                                class="fas fa-chevron-right text-xs"></i></button>
                    </div>
                </div>
            </x-slot>

            <x-slot name="mobile">
                <div class="d-block d-md-none pb-3" id="mobile-dekanat-dosen-accordion">
                    {{-- Dosen 1 --}}
                    <x-dosen-mobile-card id="dek-mobile-dosen1" name="Prof. Ahmad, M.T" prodi="Sistem Informasi"
                        count="9" ongoing="6" finished="3" parent="#mobile-dekanat-dosen-accordion"
                        data-prodi="Sistem Informasi">
                        <x-student-card name="Klein Moretti" nim="11230910000097" title="Prediksi Dampak Pada Benda..."
                            count="2" status="Ongoing" />
                    </x-dosen-mobile-card>

                    {{-- Dosen 2 --}}
                    <x-dosen-mobile-card id="dek-mobile-dosen2" name="Budi Sukario, M.Sc" prodi="Teknik Informatika"
                        count="5" ongoing="5" finished="0" parent="#mobile-dekanat-dosen-accordion"
                        data-prodi="Teknik Informatika">
                        <x-student-card name="Student A" nim="11230910000001" title="Lorem ipsum..." count="5"
                            status="Ongoing" />
                    </x-dosen-mobile-card>

                    {{-- Dosen 3 --}}
                    <x-dosen-mobile-card id="dek-mobile-dosen3" name="Azik Eaggers" prodi="Teknik Pertambangan"
                        count="10" ongoing="6" finished="4" parent="#mobile-dekanat-dosen-accordion"
                        data-prodi="Teknik Pertambangan">
                        <x-student-card name="Student B" nim="11230910000002" title="Mining Data..." count="3"
                            status="Ongoing" />
                    </x-dosen-mobile-card>

                    {{-- Dosen 4 --}}
                    <x-dosen-mobile-card id="dek-mobile-dosen4" name="Dunn Smith" prodi="Fisika" count="8" ongoing="6"
                        finished="2" parent="#mobile-dekanat-dosen-accordion" data-prodi="Fisika">
                        <x-student-card name="Student C" nim="11230910000003" title="Quantum Physics..." count="1"
                            status="Ongoing" />
                    </x-dosen-mobile-card>

                    {{-- Mobile Pagination --}}
                    <div class="d-flex flex-column align-items-center mt-3">
                        <span class="text-muted text-sm mb-2">Halaman 1 dari 5 (20 Dosen)</span>
                        <div>
                            <button class="btn btn-sm btn-light border mr-1"><i class="fas fa-chevron-left text-xs"></i>
                                Sebelumnya</button>
                            <button class="btn btn-sm btn-white border font-weight-bold">Selanjutnya <i
                                    class="fas fa-chevron-right text-xs"></i></button>
                        </div>
                    </div>
                </div>
            </x-slot>
        </x-table>
    </div>
</div>