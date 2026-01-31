<hr>
<div class="row mt-4">
    <div class="col-12">
        <h3>Statistik Bimbingan (Prodi TI)</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="info-box bg-white">
            <span class="info-box-icon bg-dark elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Dosen</span>
                <span class="info-box-number">24</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Mahasiswa Bimbingan</span>
                <span class="info-box-number">55</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-spinner"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sedang Berjalan (On-Going)</span>
                <span class="info-box-number">43</span>
            </div>
        </div>
        <div class="info-box bg-white">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai (Finished)</span>
                <span class="info-box-number">12</span>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <x-chart-card title="Bimbingan per Kategori" id="bimbinganChartProdi">
            <ul class="list-unstyled">
                <li><i class="fas fa-circle text-success"></i> Selesai (Finished)</li>
                <li><i class="fas fa-circle text-warning"></i> Sedang Berjalan (On-Going)</li>
            </ul>
        </x-chart-card>
    </div>
</div>

{{-- Monitoring Dosen Section --}}
<div class="row mt-4 mb-3">
    <div class="col-12">
        <h3>Monitoring Dosen (Prodi TI)</h3>
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

            <tbody id="desktop-dosen-accordion">
                <x-dosen-row id="dosen1" name="Prof. Ahmad, M.T" prodi="Teknik Informatika" count="9" ongoing="6"
                    finished="3" parent="#desktop-dosen-accordion">
                    <x-student-row name="Klein Moretti" nim="11230910000097" title="Prediksi Dampak Pada Benda..."
                        count="2" status="Ongoing" />
                    <x-student-row name="Klein Moretti 2" nim="11230910000097" title="Prediksi Dampak Pada Benda..."
                        count="2" status="Ongoing" />
                    <x-student-row name="Another Student" nim="11230910000099" title="Some Finished Title..." count="4"
                        status="Selesai" />
                </x-dosen-row>

                <x-dosen-row id="dosen2" name="Budi Sukario, M.Sc" prodi="Teknik Informatika" count="5" ongoing="5"
                    finished="0" parent="#desktop-dosen-accordion">
                    <x-student-row name="Student A" nim="11230910000001"
                        title="Lorem, ipsum dolor sit amet consectetur adipisicing elit. Omnis consequuntur quos est, velit perspiciatis ratione ipsum reprehenderit odit illum facere id eius quam nostrum labore doloremque animi placeat. Reiciendis, mollitia. Tempora, soluta? Nobis eius, non veniam neque quas itaque voluptatibus, soluta id delectus temporibus tempora. Eum inventore distinctio enim sapiente!"
                        count="5" status="Ongoing" />
                </x-dosen-row>

                <x-dosen-row id="dosen3" name="Azik Eaggers" prodi="Teknik Informatika" count="10" ongoing="6"
                    finished="4" parent="#desktop-dosen-accordion">
                    <x-student-row name="Student B" nim="11230910000002" title="Judul Skripsi B..." count="3"
                        status="Ongoing" />
                </x-dosen-row>

                <x-dosen-row id="dosen4" name="Dunn Smith" prodi="Teknik Informatika" count="8" ongoing="6" finished="2"
                    parent="#desktop-dosen-accordion">
                    <x-student-row name="Student C" nim="11230910000003" title="Judul Skripsi C..." count="1"
                        status="Ongoing" />
                </x-dosen-row>

                <x-dosen-row id="dosen5" name="Daly Simone" prodi="Teknik Informatika" count="5" ongoing="2"
                    finished="3" parent="#desktop-dosen-accordion">
                    <x-student-row name="Student D" nim="11230910000004" title="Judul Skripsi D..." count="8"
                        status="Selesai" />
                </x-dosen-row>

            </tbody>
            <x-slot name="mobile">
                <div class="d-block d-md-none pb-3" id="mobile-dosen-accordion">
                    <x-dosen-mobile-card id="mobile-dosen1" name="Prof. Ahmad, M.T" prodi="Teknik Informatika" count="9"
                        ongoing="6" finished="3" parent="#mobile-dosen-accordion">
                        <x-student-card name="Klein Moretti" nim="11230910000097" title="Prediksi Dampak Pada Benda..."
                            count="2" status="Ongoing" />
                        <x-student-card name="Klein Moretti 2" nim="11230910000097"
                            title="Prediksi Dampak Pada Benda..." count="2" status="Ongoing" />
                        <x-student-card name="Another Student" nim="11230910000099" title="Some Finished Title..."
                            count="4" status="Selesai" />
                    </x-dosen-mobile-card>

                    <x-dosen-mobile-card id="mobile-dosen2" name="Budi Sukario, M.Sc" prodi="Teknik Informatika"
                        count="5" ongoing="5" finished="0" parent="#mobile-dosen-accordion">
                        <x-student-card name="Lorem, ipsum dolor sit amet consectetur" nim="11230910000001"
                            title="Lorem, ipsum dolor sit amet consectetur adipisicing elit. Omnis consequuntur
                                                                                    quos est, velit perspiciatis ratione ipsum reprehenderit odit illum facere id
                                                                                    eius quam nostrum labore doloremque animi placeat. Reiciendis, mollitia.
                                                                                    Tempora, soluta? Nobis eius, non veniam neque quas itaque voluptatibus, soluta
                                                                                    id delectus temporibus tempora. Eum inventore distinctio enim sapiente!" count="5" status="Ongoing" />
                    </x-dosen-mobile-card>

                    <x-dosen-mobile-card id="mobile-dosen3" name="Azik Eaggers" prodi="Teknik Informatika" count="10"
                        ongoing="6" finished="4" parent="#mobile-dosen-accordion">
                        <x-student-card name="Student B" nim="11230910000002" title="Judul Skripsi B..." count="3"
                            status="Ongoing" />
                    </x-dosen-mobile-card>

                    <x-dosen-mobile-card id="mobile-dosen4" name="Dunn Smith" prodi="Teknik Informatika" count="8"
                        ongoing="6" finished="2" parent="#mobile-dosen-accordion">
                        <x-student-card name="Student C" nim="11230910000003" title="Judul Skripsi C..." count="1"
                            status="Ongoing" />
                    </x-dosen-mobile-card>

                    <x-dosen-mobile-card id="mobile-dosen5" name="Daly Simone" prodi="Teknik Informatika" count="5"
                        ongoing="2" finished="3" parent="#mobile-dosen-accordion">
                        <x-student-card name="Student D" nim="11230910000004" title="Judul Skripsi D..." count="8"
                            status="Selesai" />
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

            {{-- Desktop Pagination --}}
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
        </x-table>
    </div>
</div>