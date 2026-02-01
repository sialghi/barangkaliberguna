@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
<link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        // --- 1. DOSEN CHART (Dinamis dari Database) ---
        @if(in_array('dosen', $userRole))
            if ($('#bimbinganChart').length) {
                var donutChartCanvas = $('#bimbinganChart').get(0).getContext('2d');
                var donutData = {
                    labels: ['Selesai (Finished)', 'Sedang Berjalan (On-Going)'],
                    datasets: [{
                        // Mengambil data dari variabel bimbinganSelesai dan bimbinganOngoing
                        data: [{{ $bimbinganSelesai ?? 0 }}, {{ $bimbinganOngoing ?? 0 }}],
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

        $('#prodiFilterDekanat').on('change', function () {
            var selectedProdi = $(this).val();
            var $tbody = $('#dekanat-dosen-accordion');

            if (selectedProdi === 'all') {
                $tbody.find('tr').show();
            } else {
                $tbody.find('tr').hide(); // Sembunyikan semua baris
                
                // Cari baris dosen yang cocok
                $tbody.find('tr[data-prodi="' + selectedProdi + '"]').each(function() {
                    $(this).show(); // Tampilkan baris dosen
                    // Tampilkan juga baris detail (collapse container) tepat di bawahnya
                    $(this).next('tr').addClass('d-none'); // Default detail tertutup tapi 'ada'
                    $(this).next('tr').css('display', ''); // Reset display dari .hide() sebelumnya
                });
            }

            // Update Total Text
            var count = $tbody.find('tr[data-prodi]:visible').length;
            $('#totalDosenText').text('Total: ' + count + ' Dosen');
        });

        // --- 2. Dekanat Bar Chart (Fix Skala 1 Mahasiswa) ---
        @if(array_intersect(['dekan', 'wadek_satu', 'admin_dekanat'], $userRole))
            if ($('#dekanatChart').length) {
                var ctx = $('#dekanatChart').get(0).getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Jumlah Mahasiswa Bimbingan',
                            data: @json($chartData),
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
                                    stepSize: 1, // Agar angka 1, 2, 3 terlihat jelas
                                    precision: 0
                                }
                                // Hapus 'max' agar skala menyesuaikan otomatis (Auto-scale)
                            }
                        }
                    }
                });
            }
        @endif

        @if(array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
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
                    options: { maintainAspectRatio: false, responsive: true, legend: { display: false } }
                });
            }
        @endif

        // --- 3. DEKANAT CHART ---
        @if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
            if ($('#dekanatChart').length) {
                    var dekanatChartCanvas = $('#dekanatChart').get(0).getContext('2d');
                    new Chart(dekanatChartCanvas, {
                        type: 'bar',
                        data: {
                            labels: @json($chartLabels), // Nama-nama Prodi
                            datasets: [{
                                label: 'Total Mahasiswa Bimbingan',
                                data: @json($chartData), // Jumlah mahasiswa per prodi
                                backgroundColor: 'rgba(60, 141, 188, 0.9)',
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 1 } }
                            }
                        }
                    });
                }
            @endif
    });

    $(document).ready(function () {
    $('#prodiFilterDekanat').on('change', function () {
        var selectedProdi = $(this).val();

        // --- Logika Filter Desktop ---
        var desktopRows = $('#dekanat-dosen-accordion tr[data-prodi]');
        
        if (selectedProdi === 'all') {
            // Tampilkan semua baris jika pilih "Semua Jurusan"
            $('#dekanat-dosen-accordion tr').show();
        } else {
            // Sembunyikan semua baris di dalam tbody
            $('#dekanat-dosen-accordion tr').hide();
            
            // Tampilkan baris utama yang prodi-nya cocok
            desktopRows.each(function() {
                if ($(this).attr('data-prodi') === selectedProdi) {
                    $(this).show();
                    // Tampilkan juga baris detail bimbingan (baris berikutnya)
                    $(this).next('tr').show(); 
                }
            });
        }

        // --- Logika Filter Mobile ---
        var mobileCards = $('#mobile-dekanat-dosen-accordion [data-prodi]');
        
        if (selectedProdi === 'all') {
            mobileCards.show();
        } else {
            mobileCards.hide();
            mobileCards.filter('[data-prodi="' + selectedProdi + '"]').show();
        }

        var selectedProdi = $(this).val();
        var desktopRows = $('#dekanat-dosen-accordion tr[data-prodi]');
        
        // 1. Logika Penyaringan (Filter)
        if (selectedProdi === 'all') {
            $('#dekanat-dosen-accordion tr').show();
            $('#mobile-dekanat-dosen-accordion [data-prodi]').show();
        } else {
            // Sembunyikan semua dulu
            $('#dekanat-dosen-accordion tr').hide();
            $('#mobile-dekanat-dosen-accordion [data-prodi]').hide();
            
            // Tampilkan yang cocok (Desktop)
            desktopRows.each(function() {
                if ($(this).attr('data-prodi') === selectedProdi) {
                    $(this).show();
                    $(this).next('tr').show(); // Detail bimbingan
                }
            });

            // Tampilkan yang cocok (Mobile)
            $('#mobile-dekanat-dosen-accordion [data-prodi="' + selectedProdi + '"]').show();
        }

        // 2. UPDATE TOTAL DOSEN (Dinamis)
        // Kita hitung jumlah baris data-prodi yang statusnya tidak hidden
        var currentCount = $('#dekanat-dosen-accordion tr[data-prodi]:visible').length;
        
        // Update teks di UI
        $('#totalDosenText').text('Total: ' + currentCount + ' Dosen');
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
    <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal" style="cursor: pointer;"></i>
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

@if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat', 'kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
    <h3>TTD Kaprodi</h3>
    <div class="info_box">
        <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat" icon="fas fa-lg fa-inbox" />
        <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD" icon="fas fa-lg fa-file" />
        <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD" icon="fas fa-lg fa-file-signature" />
        <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak" icon="fas fa-lg fa-file-excel" />
    </div>
    <hr>
    <h3>Permohonan Surat Tugas</h3>
    <div class="info_box">
        <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratPT }}" title="Total Surat" icon="fas fa-lg fa-inbox" />
        <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $PTdiproses }}" title="Sedang Diproses" icon="fas fa-lg fa-file" />
        <x-adminlte-info-box class="mr-3" theme="success" text="{{ $PTditerima }}" title="Diterima" icon="fas fa-lg fa-file-signature" />
        <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $PTditolak }}" title="Ditolak" icon="fas fa-lg fa-file-excel" />
    </div>
    <hr>
@endif

@if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
    <x-dekanat-dashboard 
        :monitoringDekanat="$monitoringDekanat"
    />
@endif

@if(array_intersect(['kaprodi', 'sekprodi', 'admin_prodi'], $userRole))
    <x-kaprodi-dashboard 
        :totalDosen="$totalDosen" 
        :totalMhs="$totalMhs" 
        :prodiOngoing="$prodiOngoing" 
        :prodiSelesai="$prodiSelesai" 
        :monitoringDosen="$monitoringDosen"
    />
@endif

@if(in_array('dosen', $userRole))
    <x-dosen-dashboard 
        :totalSuratPT="$totalSuratPT" 
        :PTdiproses="$PTdiproses" 
        :PTditerima="$PTditerima"
        :PTditolak="$PTditolak" 
        :bimbingan="$bimbingan"
        :totalBimbingan="$totalBimbingan"
        :bimbinganOngoing="$bimbinganOngoing"
        :bimbinganSelesai="$bimbinganSelesai" 
    />
@endif

@if(in_array('mahasiswa', $userRole))
    <h3>Statistik Surat Anda</h3>
    <div class="info_box">
        <x-adminlte-info-box class="mr-3" theme="primary" text="{{ $totalSuratTTD }}" title="Total Surat" icon="fas fa-lg fa-inbox" />
        <x-adminlte-info-box class="mr-3" theme="dark" text="{{ $belumTTD }}" title="Belum di TTD" icon="fas fa-lg fa-file" />
        <x-adminlte-info-box class="mr-3" theme="success" text="{{ $sudahTTD }}" title="Sudah di TTD" icon="fas fa-lg fa-file-signature" />
        <x-adminlte-info-box class="mr-3" theme="danger" text="{{ $ditolakTTD }}" title="Surat Ditolak" icon="fas fa-lg fa-file-excel" />
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