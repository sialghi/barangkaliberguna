@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
<link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        //Dosen Chart
        @if(array_intersect(['dosen'], $userRole))
            if ($('#bimbinganChart').length) {
                var donutChartCanvas = $('#bimbinganChart').get(0).getContext('2d');
                var donutData = {
                    labels: ['Selesai (Finished)', 'Sedang Berjalan (On-Going)'],
                    datasets: [{
                        data: [34, 66],
                        backgroundColor: ['#28a745', '#fd7e14'],
                    }]
                }
                var donutOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: false },
                    cutout: '0%',
                }
                new Chart(donutChartCanvas, {
                    type: 'pie',
                    data: donutData,
                    options: donutOptions
                })
            }
        @endif

        //Prodi Chart
        @if(array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
            if ($('#bimbinganChartProdi').length) {
                var prodiChartCanvas = $('#bimbinganChartProdi').get(0).getContext('2d');
                var prodiData = {
                    labels: ['Selesai (Finished)', 'Sedang Berjalan (On-Going)'],
                    datasets: [{
                        data: [12, 43], // Based on mock data: 12 Finished, 43 Ongoing
                        backgroundColor: ['#28a745', '#fd7e14'],
                    }]
                }
                var prodiOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: { display: false },
                    cutout: '0%',
                }
                new Chart(prodiChartCanvas, {
                    type: 'pie',
                    data: prodiData,
                    options: prodiOptions
                })
            }
        @endif

        // Dekanat Bar Chart
        @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
            if ($('#dekanatChart').length) {
                var dekanatChartCanvas = $('#dekanatChart').get(0).getContext('2d');
                var dekanatData = {
                    labels: ['Teknik Pertambangan', 'Teknik Informatika', 'Agribisnis', 'Biologi', 'Sistem Informasi', 'Matematika', 'Fisika', 'Kimia'],
                    datasets: [{
                        label: 'Jumlah Mahasiswa',
                        data: [120, 150, 100, 80, 130, 90, 60, 70],
                        backgroundColor: 'rgba(60, 141, 188, 0.9)',
                        borderColor: 'rgba(60, 141, 188, 0.8)',
                        borderWidth: 1
                    }]
                }
                var dekanatOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 45, minRotation: 45 }
                        },
                        y: {
                            grid: { color: '#f4f4f4', borderDash: [2, 2] },
                            beginAtZero: true,
                            max: 200,
                            ticks: { stepSize: 40 }
                        }
                    }
                }

                new Chart(dekanatChartCanvas, {
                    type: 'bar',
                    data: dekanatData,
                    options: dekanatOptions
                })
            }
        @endif
    });

    $(document).ready(function () {
        @if(array_intersect(['dosen'], $userRole))
            $('.btn-filter').on('click', function () {
                var filter = $(this).data('filter');

                $('.btn-filter').removeClass('btn-primary').addClass('btn-secondary');
                $(this).removeClass('btn-secondary').addClass('btn-primary');

                if (filter == 'all') {
                    $('table.projects tbody tr').show();
                    $('.mobile-card').show();
                } else {
                    $('table.projects tbody tr').hide();
                    $('table.projects tbody tr[data-status="' + filter + '"]').show();

                    $('.mobile-card').hide();
                    $('.mobile-card[data-status="' + filter + '"]').show();
                }
            });
        @endif
    });

    $(document).ready(function () {
        $('.btn-filter-inner').on('click', function (e) {
            e.stopPropagation();
            var filter = $(this).data('filter');
            var target = $(this).data('target');

            $(this).siblings().removeClass('btn-primary').addClass('btn-secondary');
            $(this).removeClass('btn-secondary').addClass('btn-primary');

            if (filter == 'all') {
                $(target).find('[data-status]').show();
            } else {
                $(target).find('[data-status]').hide();
                $(target).find('[data-status="' + filter + '"]').show();
            }
        });

        $('.collapse').on('click', function (e) {
            e.stopPropagation();
        });

        //Dekanat Prodi Filter
        @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
            $('#prodiFilterDekanat').on('change', function () {
                var value = $(this).val();

                var rows = $('#dekanat-dosen-accordion > tr.accordion-toggle');
                var mobileCards = $('#mobile-dekanat-dosen-accordion .mobile-card');

                if (value === 'all') {
                    // Show everything
                    rows.show();
                    $('#dekanat-dosen-accordion > tr').show();
                    mobileCards.show();
                } else {
                    // Filter Desktop
                    rows.each(function () {
                        var rowProdi = $(this).attr('data-prodi');
                        var detailRow = $(this).next('tr');

                        if (rowProdi === value) {
                            $(this).show();
                            detailRow.show();
                        } else {
                            $(this).hide();
                            detailRow.hide();
                        }
                    });

                    // Filter Mobile
                    mobileCards.each(function () {
                        var cardProdi = $(this).attr('data-prodi');
                        if (cardProdi === value) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });
        @endif
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
                    <img id="imgPanduan" src="/img/panduan/totalSurat.png" />
                    <p>"Total Surat" adalah seluruh surat yang dimiliki oleh pengguna baik yang sudah di tanda tangan
                        (TTD), belum di TTD, dan surat yang ditolak.</p>
                </div>
                <div id="panduanSection" class="my-4">
                    <img id="imgPanduan" src="/img/panduan/belumTTD.png" />
                    <p>"Belum di TTD atau Sedang Diproses" adalah seluruh surat yang sudah diunggah oleh pengguna tetapi
                        belum mendapatkan tanda tangan Ketua Prodi TI.</p>
                </div>
                <div id="panduanSection">
                    <img id="imgPanduan" src="/img/panduan/sudahTTD.png" />
                    <p>"Sudah di TTD" adalah seluruh surat yang sudah diunggah oleh pengguna dan sudah mendapatkan tanda
                        tangan Ketua Prodi TI.</p>
                </div>
                <div id="panduanSection" class="mt-4">
                    <img id="imgPanduan" src="/img/panduan/suratDitolak.png" />
                    <p>"Surat Ditolak atau Ditolak" adalah seluruh surat yang sudah diunggah oleh pengguna namun ditolak
                        karena ketidaksesuaian format ataupun isi surat.</p>
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
@if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
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

@if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
    <x-dekanat-dashboard />
@endif

@if(array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
    <x-kaprodi-dashboard />
@endif

@if(array_intersect(['dosen'], $userRole))
    <x-dosen-dashboard :totalSuratPT="$totalSuratPT" :PTdiproses="$PTdiproses" :PTditerima="$PTditerima"
        :PTditolak="$PTditolak" />
@endif

@if(array_intersect(['mahasiswa'], $userRole))
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
@endif

<p> Butuh bantuan? </p>
<a href="https://chat.whatsapp.com/B87uLWeQEFVECsL54S6go5" target="_blank">
    <p style="color: #4FCE5D">
        <i class="fab fa-whatsapp"></i> Hubungi kami via WhatsApp
    </p>
</a>
@stop

@section('css')
<link rel="stylesheet" href="/css/styles.css">
@stop