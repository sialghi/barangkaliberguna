@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // --- 1. DOSEN CHART ---
            // PENTING: Data chart ini ($bimbinganSelesai, $bimbinganOngoing) 
            // harus sudah dihitung dengan logika Nilai Skripsi Lengkap di Controller.
            @if (in_array('dosen', $userRole))
                if ($('#bimbinganChart').length) {
                    var donutChartCanvas = $('#bimbinganChart').get(0).getContext('2d');
                    new Chart(donutChartCanvas, {
                        type: 'pie',
                        data: {
                            labels: ['Selesai (Finished)', 'Sedang Berjalan (On-Going)'],
                            datasets: [{
                                data: [{{ $bimbinganSelesai ?? 0 }}, {{ $bimbinganOngoing ?? 0 }}],
                                backgroundColor: ['#28a745', '#fd7e14'],
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false
                            },
                            cutout: '0%'
                        }
                    })
                }
            @endif

            $('#prodiFilterDekanat').on('change', function () {
                var selectedProdi = $(this).val();
                
                // Desktop Container
                var $tbody = $('#dekanat-dosen-accordion');
                var $lecturerRows = $tbody.find('tr[data-prodi]'); // Hanya baris dosen
                var $emptyMsg = $('#emptyProdiMsg');

                // Mobile Container
                var $mobileContainer = $('#mobile-dekanat-dosen-accordion');
                var $mobileCards = $mobileContainer.find('[data-prodi]'); // Card dosen mobile
                var $emptyMsgMobile = $('#emptyProdiMsgMobile');

                // --- LOGIC DESKTOP ---
                $lecturerRows.each(function() {
                    var $thisDosenRow = $(this);
                    var $detailRow = $thisDosenRow.next('tr');
                    
                    if (selectedProdi === 'all' || $thisDosenRow.data('prodi') === selectedProdi) {
                        $thisDosenRow.show();
                        $detailRow.css('display', ''); // Reset display
                    } else {
                        $thisDosenRow.hide();
                        $detailRow.hide();
                    }
                });

                // Cek apakah ada dosen yang visible di desktop
                var visibleCount = $lecturerRows.filter(':visible').length;
                if (visibleCount === 0 && selectedProdi !== 'all') {
                    $emptyMsg.show();
                } else {
                    $emptyMsg.hide();
                }
                
                // Update text total dosen
                if (selectedProdi === 'all') {
                    $('#totalDosenText').text('Total: ' + $lecturerRows.length + ' Dosen');
                } else {
                    $('#totalDosenText').text('Total: ' + visibleCount + ' Dosen (' + selectedProdi + ')');
                }

                // --- LOGIC MOBILE ---
                if ($mobileCards.length > 0) {
                    $mobileCards.each(function() {
                        var $card = $(this);
                        if (selectedProdi === 'all' || $card.data('prodi') === selectedProdi) {
                            $card.show();
                        } else {
                            $card.hide();
                        }
                    });

                    var visibleMobileCount = $mobileCards.filter(':visible').length;
                    if (visibleMobileCount === 0 && selectedProdi !== 'all') {
                        $emptyMsgMobile.show();
                    } else {
                        $emptyMsgMobile.hide();
                    }
                }
            });

            // --- 3. CHART DEKANAT ---
            @if (array_intersect(['dekan', 'wadek_satu', 'admin_dekanat'], $userRole))
                if ($('#dekanatChart').length) {
                    var ctx = $('#dekanatChart').get(0).getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json($chartLabels ?? []),
                            datasets: [{
                                label: 'Jumlah Mahasiswa Bimbingan',
                                data: @json($chartData ?? []),
                                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }
            @endif

            // --- 4. CHART PRODI ---
            @if (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
                if ($('#bimbinganChartProdi').length) {
                    var prodiChartCanvas = $('#bimbinganChartProdi').get(0).getContext('2d');
                    new Chart(prodiChartCanvas, {
                        type: 'pie',
                        data: {
                            labels: ['Selesai', 'On-Going'],
                            datasets: [{
                                data: [{{ $prodiSelesai ?? 0 }}, {{ $prodiOngoing ?? 0 }}],
                                backgroundColor: ['#28a745', '#fd7e14'],
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            legend: {
                                display: false
                            }
                        }
                    });
                }
            @endif

            // --- 4b. ANALISIS PERFORMA AKADEMIK (ADMIN_PRODI ONLY) ---
            @if (in_array('admin_prodi', $userRole))
                if ($('#apPerformaChart').length) {
                    var apPeriode = (new Date().getMonth() + 1) <= 6 ? 'jan-jun' : 'jul-des';
                    var apMetric = 'intensitas_bimbingan';
                    var apYear = parseInt($('#apSelectYear').val(), 10) || (new Date().getFullYear());
                    var apChart = null;

                    function apSetPeriodeButtons() {
                        var isJanJun = apPeriode === 'jan-jun';
                        $('#apBtnPeriodeJanJun')
                            .toggleClass('btn-secondary', isJanJun)
                            .toggleClass('btn-outline-secondary', !isJanJun);
                        $('#apBtnPeriodeJulDes')
                            .toggleClass('btn-primary', !isJanJun)
                            .toggleClass('btn-outline-primary', isJanJun);
                    }

                    function apSetMetricButtons() {
                        var isLama = apMetric === 'lama_skripsi';
                        $('#apBtnMetricLama')
                            .toggleClass('btn-primary', isLama)
                            .toggleClass('btn-outline-primary', !isLama);
                        $('#apBtnMetricIntensitas')
                            .toggleClass('btn-primary', !isLama)
                            .toggleClass('btn-outline-primary', isLama);
                    }

                    function apTitle() {
                        return apMetric === 'lama_skripsi'
                            ? 'Rata-rata Lama Skripsi (bulan)'
                            : 'Rata-rata Intensitas Bimbingan (per Mahasiswa Aktif)';
                    }

                    function apFetchAndRender() {
                        $.get(@json(route('analisis.performa-akademik.data')), {
                            year: apYear,
                            periode: apPeriode,
                            metric: apMetric
                        }).done(function (res) {
                            var ctx = document.getElementById('apPerformaChart').getContext('2d');
                            if (apChart) {
                                apChart.destroy();
                            }

                            var isBar = apMetric === 'intensitas_bimbingan';
                            var dataset = isBar ? {
                                label: apTitle(),
                                data: res.values || [],
                                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                                borderWidth: 1
                            } : {
                                label: apTitle(),
                                data: res.values || [],
                                borderColor: 'rgba(60, 141, 188, 0.9)',
                                backgroundColor: 'rgba(60, 141, 188, 0.15)',
                                borderWidth: 3,
                                fill: false,
                                tension: 0.35,
                                pointRadius: 5,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: 'rgba(60, 141, 188, 0.9)',
                                pointBorderWidth: 2
                            };

                            apChart = new Chart(ctx, {
                                type: isBar ? 'bar' : 'line',
                                data: {
                                    labels: res.labels || [],
                                    datasets: [dataset]
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    responsive: true,
                                    scales: {
                                        y: { beginAtZero: true }
                                    },
                                    plugins: {
                                        legend: { display: false }
                                    }
                                }
                            });
                        }).fail(function (xhr) {
                            console.error('Failed to fetch academic performance data', xhr);
                        });
                    }

                    $('#apBtnPeriodeJanJun, #apBtnPeriodeJulDes').on('click', function () {
                        apPeriode = $(this).data('periode');
                        apSetPeriodeButtons();
                        apFetchAndRender();
                    });

                    $('#apBtnMetricLama, #apBtnMetricIntensitas').on('click', function () {
                        apMetric = $(this).data('metric');
                        apSetMetricButtons();
                        apFetchAndRender();
                    });

                    $('#apSelectYear').on('change', function () {
                        apYear = parseInt($(this).val(), 10);
                        apFetchAndRender();
                    });

                    apSetPeriodeButtons();
                    apSetMetricButtons();
                    apFetchAndRender();
                }

                if ($('#apTepatWaktuChart').length) {
                    var apTepatWaktuChart = null;

                    function apFetchTepatWaktu() {
                        $.get(@json(route('analisis.tepat-waktu-smt8')))
                            .done(function (res) {
                                var tepat = parseInt(res.tepat_waktu || 0, 10);
                                var terlambat = parseInt(res.terlambat || 0, 10);

                                var ctx = document.getElementById('apTepatWaktuChart').getContext('2d');
                                if (apTepatWaktuChart) {
                                    apTepatWaktuChart.destroy();
                                }

                                apTepatWaktuChart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Tepat Waktu', 'Terlambat'],
                                        datasets: [{
                                            data: [tepat, terlambat],
                                            backgroundColor: ['#28a745', '#fd7e14'],
                                        }]
                                    },
                                    options: {
                                        maintainAspectRatio: false,
                                        responsive: true,
                                        plugins: {
                                            legend: { display: true, position: 'bottom' }
                                        },
                                        cutout: '60%'
                                    }
                                });
                            })
                            .fail(function (xhr) {
                                console.error('Failed to fetch tepat waktu smt8 data', xhr);
                            });
                    }

                    apFetchTepatWaktu();
                }

                if ($('#apJenisTAChart').length) {
                    var apJenisTAChart = null;
                    var apPalette = ['#007bff', '#28a745', '#fd7e14', '#17a2b8', '#ffc107', '#6f42c1', '#dc3545', '#6c757d'];

                    function apFetchJenisTA() {
                        $.get(@json(route('analisis.sebaran-jenis-ta')))
                            .done(function (res) {
                                var labels = res.labels || [];
                                var values = res.values || [];
                                var colors = labels.map(function (_, i) { return apPalette[i % apPalette.length]; });

                                var ctx = document.getElementById('apJenisTAChart').getContext('2d');
                                if (apJenisTAChart) {
                                    apJenisTAChart.destroy();
                                }

                                apJenisTAChart = new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: labels,
                                        datasets: [{
                                            data: values,
                                            backgroundColor: colors,
                                        }]
                                    },
                                    options: {
                                        maintainAspectRatio: false,
                                        responsive: true,
                                        plugins: {
                                            legend: { display: true, position: 'bottom' }
                                        }
                                    }
                                });
                            })
                            .fail(function (xhr) {
                                console.error('Failed to fetch sebaran jenis TA data', xhr);
                            });
                    }

                    apFetchJenisTA();
                }
            @endif

            // --- 5. LOGIKA FILTER STATUS (LOKAL CHILD) ---
            $(document).on('click', '.btn-filter-inner', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var filterType = $btn.data('filter');

                $btn.siblings().removeClass('btn-primary').addClass('btn-secondary');
                $btn.removeClass('btn-secondary').addClass('btn-primary');

                var $container = $btn.closest('.collapse');
                var $tbody = $container.find('table.inner-table tbody');
                var $studentRows = $tbody.find('tr[data-status]');

                if (filterType === 'all') {
                    $studentRows.show();
                } else {
                    $studentRows.hide();
                    $studentRows.filter(function() {
                        return $(this).data('status') === filterType;
                    }).show();
                }

                // Empty State Logic
                var visibleCount = $studentRows.filter(':visible').length;
                var $emptyMsgRow = $tbody.find('.dynamic-empty-msg');

                if (visibleCount === 0) {
                    if ($emptyMsgRow.length === 0) {
                        $tbody.append(
                            '<tr class="dynamic-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-3">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>'
                        );
                    } else {
                        $emptyMsgRow.show();
                    }
                } else {
                    if ($emptyMsgRow.length > 0) {
                        $emptyMsgRow.hide();
                    }
                }
            });

            // --- 6. LOGIKA FILTER DOSEN (SIMPLE TABLE) ---
            $(document).on('click', '.btn-filter', function() {
                var $btn = $(this);
                var filterType = $btn.data('filter');

                $btn.siblings().removeClass('btn-primary').addClass('btn-secondary');
                $btn.removeClass('btn-secondary').addClass('btn-primary');

                var $dashboardRow = $btn.closest('.row').next('.row');
                var $tbody = $dashboardRow.find('table tbody');
                var $rows = $tbody.find('tr[data-status]');

                if (filterType === 'all') {
                    $rows.show();
                } else {
                    $rows.hide();
                    $rows.filter('[data-status="' + filterType + '"]').show();
                }

                var visibleCount = $rows.filter(':visible').length;
                var $emptyRow = $tbody.find('.dosen-empty-msg');

                if (visibleCount === 0) {
                    if ($emptyRow.length === 0) {
                        $tbody.append(
                            '<tr class="dosen-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-4">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>'
                        );
                    } else {
                        $emptyRow.show();
                    }
                } else {
                    if ($emptyRow.length > 0) {
                        $emptyRow.hide();
                    }
                }

                // Mobile Filter Logic
                var $mobileContainer = $dashboardRow.find('.d-md-none');
                var $mobileCards = $mobileContainer.children('div[data-status]');
                if ($mobileCards.length > 0) {
                    if (filterType === 'all') {
                        $mobileCards.show();
                    } else {
                        $mobileCards.hide();
                        $mobileCards.filter('[data-status="' + filterType + '"]').show();
                    }
                    // Mobile Empty Msg
                    var visibleMobile = $mobileCards.filter(':visible').length;
                    var $emptyMobile = $mobileContainer.find('.mobile-empty-msg');
                    if (visibleMobile === 0) {
                        if ($emptyMobile.length === 0) {
                            $mobileContainer.append(
                                '<div class="text-center text-muted font-italic py-4 mobile-empty-msg">Tidak ada data.</div>'
                            );
                        } else {
                            $emptyMobile.show();
                        }
                    } else {
                        $emptyMobile.hide();
                    }
                }
            });
        });
    </script>
@stop

@section('content_header')
    <h5 class="font-weight-light">Selamat Datang di</h5>
    <div class="d-flex flex-row align-items-center">
        <div>
            <h1>Sistem Informasi Layanan</h1>
            <h1>Fakultas Sains dan Teknologi</h1>
        </div>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"
            style="cursor: pointer;"></i>
    </div>
    <hr>

    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Panduan Halaman</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Panduan penggunaan statistik surat dan status bimbingan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')

    {{-- 1. INFO BOX SURAT --}}
    @if (array_intersect(
            ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'],
            $userRole))
        <h3>TTD Kaprodi</h3>
        <div class="info_box">
            <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat"
                icon="fas fa-lg fa-inbox" />
            <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD"
                icon="fas fa-lg fa-file" />
            <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD"
                icon="fas fa-lg fa-file-signature" />
            <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak"
                icon="fas fa-lg fa-file-excel" />
        </div>
        <hr>
        <h3>Permohonan Surat Tugas</h3>
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
    @endif

    {{-- 2. DEKANAT DASHBOARD --}}
    @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
        <x-dekanat-dashboard :monitoringDekanat="$monitoringDekanat" />
    @elseif (array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
        <x-kaprodi-dashboard :totalDosen="$totalDosen" :totalMhs="$totalMhs" :prodiOngoing="$prodiOngoing" :prodiSelesai="$prodiSelesai" :monitoringDosen="$monitoringDosen" :showAnalisis="in_array('admin_prodi', $userRole)" :showExport="in_array('admin_prodi', $userRole)" />


        {{-- 4. DOSEN DASHBOARD --}}
    @elseif (in_array('dosen', $userRole))
        {{-- Kita harus kirim variabel yang berisi status yang benar ke component --}}
        {{-- Karena component x-dosen-dashboard menerima $bimbingan apa adanya, --}}
        {{-- kita perlu memodifikasi collection $bimbingan di sini sebelum dikirim (opsional) --}}
        {{-- atau biarkan logic status di dalam component/view child seperti di bawah --}}

        <x-dosen-dashboard :totalSuratPT="$totalSuratPT" :PTdiproses="$PTdiproses" :PTditerima="$PTditerima" :PTditolak="$PTditolak" :bimbingan="$bimbingan"
            :totalBimbingan="$totalBimbingan" :bimbinganOngoing="$bimbinganOngoing" :bimbinganSelesai="$bimbinganSelesai" />


        {{-- 5. MAHASISWA DASHBOARD --}}
    @elseif (in_array('mahasiswa', $userRole))
        <h3>Statistik Surat Anda</h3>
        <div class="info_box">
            <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat"
                icon="fas fa-lg fa-inbox" />
            <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD"
                icon="fas fa-lg fa-file" />
            <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD"
                icon="fas fa-lg fa-file-signature" />
            <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak"
                icon="fas fa-lg fa-file-excel" />
        </div>
        <hr>

        <h3>Batas Waktu Tugas Akhir</h3>
        <div class="row">
            {{-- ================= LOGIC WARNA SEMPRO ================= --}}
            @php
                $semproClass = 'bg-secondary'; // Default Abu-abu (Kosong)
                $semproText = 'text-white';

                if ($dataSemhas) {
                    // Jika sudah ada Semhas, berarti Sempro "Selesai/Lewat" -> Hijau
                    $semproClass = 'bg-success';
                } elseif ($dataSempro) {
                    // Jika ada Sempro tapi belum Semhas -> Kuning
                    $semproClass = 'bg-warning';
                    $semproText = 'text-dark';
                }
            @endphp

            {{-- CARD SEMPRO --}}
            <div class="col-md-6 col-sm-12">
                <div class="card {{ $semproClass }} {{ $semproText }} shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i> Seminar Proposal</h3>
                    </div>
                    <div class="card-body">
                        @if ($dataSempro)
                            {{-- Judul TA --}}
                            <h5 class="font-weight-bold">{{ $dataSempro->judul_proposal }}</h5>
                            <hr style="border-top: 1px solid rgba(0,0,0,0.1);">

                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><small>Periode</small></p>
                                    <h6>{{ $dataSempro->periodeSempro->periode }}</h6>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><small>Tanggal Seminar Proposal</small></p>
                                    <h6>{{ \Carbon\Carbon::parse($dataSempro->periodeSempro->tanggal)->translatedFormat('d F Y') }}
                                    </h6>
                                </div>
                            </div>
                            {{-- Countdown Box --}}
                            @if (!$dataSemhas)
                                {{-- Asumsi: jika $dataSemhas null, berarti belum lulus sempro/lanjut semhas --}}
                                <div class="mt-3 p-2 rounded" style="background: rgba(0,0,0,0.1)">
                                    <p class="mb-0 text-center font-weight-bold">
                                        <i class="fas fa-hourglass-half mr-1"></i> Sisa Waktu Pengerjaan:
                                        <br>
                                        {{-- Masukkan variabel deadlineSempro ke sini --}}
                                        <span class="countdown-timer"
                                            data-start="{{ $dataSempro->periodeSempro->tanggal }}" {{-- Waktu Mulai (Seminar) --}}
                                            data-deadline="{{ $deadlineSempro ? $deadlineSempro->toIso8601String() : '' }}">
                                            {{-- Waktu Habis (Deadline) --}}
                                            Memuat...
                                        </span>
                                    </p>
                                    {{-- Opsional: Tampilkan Tanggal Deadline agar user tahu --}}
                                    <p class="text-center mb-0 mt-1" style="font-size: 0.8rem; color: #666;">
                                        (Anda diharuskan telah melaksanakan Seminar Hasil paling lambat:
                                        {{ $deadlineSempro ? $deadlineSempro->translatedFormat('d F Y') : '-' }})
                                    </p>
                                </div>
                            @else
                                <div class="mt-3 text-center">
                                    <span class="badge badge-success p-2"><i class="fas fa-check mr-1"></i> Lulus
                                        Sempro</span>
                                </div>
                            @endif
                        @else
                            {{-- Tampilan Jika Kosong --}}
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3" style="opacity: 0.5"></i>
                                <h5>Belum ada jadwal Sempro</h5>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================= LOGIC WARNA SEMHAS ================= --}}
            @php
                $semhasClass = 'bg-secondary'; // Default Abu-abu
                $semhasText = 'text-white';

                if ($dataSidang) {
                    // Jika sudah Sidang, berarti Semhas "Selesai" -> Hijau
                    $semhasClass = 'bg-success';
                } elseif ($dataSemhas) {
                    // Jika ada Semhas tapi belum Sidang -> Kuning
                    $semhasClass = 'bg-warning';
                    $semhasText = 'text-dark';
                }
            @endphp

            {{-- CARD SEMHAS --}}
            <div class="col-md-6 col-sm-12">
                <div class="card {{ $semhasClass }} {{ $semhasText }} shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i> Seminar Hasil</h3>
                    </div>
                    <div class="card-body">
                        @if ($dataSemhas)
                            {{-- Judul Skripsi --}}
                            <h5 class="font-weight-bold">{{ $dataSemhas->judul_skripsi }}</h5>
                            <hr style="border-top: 1px solid rgba(0,0,0,0.1);">

                            <div class="row">
                                <div class="col-12">
                                    <p class="mb-1"><small>Tanggal Pelaksanaan</small></p>
                                    {{-- Tampilkan Waktu Seminar Asli --}}
                                    <h6>{{ \Carbon\Carbon::parse($dataSemhas->waktu_seminar)->translatedFormat('d F Y - H:i') }}
                                        WIB</h6>
                                </div>
                            </div>

                            {{-- Countdown Box --}}
                            @if (!$dataSidang)
                                <div class="mt-3 p-2 rounded" style="background: rgba(0,0,0,0.1)">
                                    <p class="mb-0 text-center font-weight-bold">
                                        <i class="fas fa-hourglass-half mr-1"></i> Sisa Waktu Pengerjaan:
                                        <br>

                                        {{-- PENTING: Gunakan variable $deadlineSemhas hasil hitungan Controller --}}
                                        <span class="countdown-timer" data-start="{{ $dataSemhas->waktu_seminar }}"
                                            {{-- Waktu Mulai (Seminar) --}}
                                            data-deadline="{{ $deadlineSemhas ? $deadlineSemhas->toIso8601String() : '' }}">
                                            {{-- Waktu Habis (Deadline) --}}
                                            Memuat...
                                        </span>
                                    </p>
                                    {{-- Info Tanggal Deadline --}}
                                    <p class="text-center mb-0 mt-1" style="font-size: 0.8rem; color: #666;">
                                        (Anda diharuskan telah melaksanakan Sidang Akhir paling lambat:
                                        {{ $deadlineSemhas ? $deadlineSemhas->translatedFormat('d F Y') : '-' }})
                                    </p>
                                </div>
                            @else
                                <div class="mt-3 text-center">
                                    <span class="badge badge-success p-2"><i class="fas fa-check mr-1"></i> Lulus
                                        Semhas</span>
                                </div>
                            @endif
                        @else
                            {{-- Tampilan Jika Kosong --}}
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3" style="opacity: 0.5"></i>
                                <h5>Belum ada jadwal Semhas</h5>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <hr>
    @endif

    <div class="mt-4">
        <p> Butuh bantuan? </p>
        <a href="https://chat.whatsapp.com/B87uLWeQEFVECsL54S6go5" target="_blank" class="text-success">
            <i class="fab fa-whatsapp"></i> Hubungi kami via WhatsApp
        </a>
    </div>
@stop
