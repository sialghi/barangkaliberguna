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

        // LOGIKA FILTER PRODI (GLOBAL) - VERSI FIX ACCORDION
        $('#prodiFilterDekanat').on('change', function () {
            var selectedProdi = $(this).val();
            var $tbody = $('#dekanat-dosen-accordion');
            
            // Ambil semua baris dosen (yang punya atribut data-prodi)
            var $lecturerRows = $tbody.find('tr[data-prodi]');

            $lecturerRows.each(function() {
                var $thisDosenRow = $(this);
                var $detailRow = $thisDosenRow.next('tr'); // Baris detail (list mahasiswa) tepat dibawahnya
                
                // Cek apakah prodi cocok atau 'all'
                if (selectedProdi === 'all' || $thisDosenRow.data('prodi') === selectedProdi) {
                    // 1. Tampilkan Baris Dosen
                    $thisDosenRow.show();
                    
                    // 2. [KUNCI PERBAIKAN] Reset display detail row
                    // Jangan pakai .show(), tapi hapus properti display agar Bootstrap Collapse bisa bekerja lagi
                    $detailRow.css('display', ''); 
                } else {
                    // 1. Sembunyikan Baris Dosen
                    $thisDosenRow.hide();
                    
                    // 2. Sembunyikan Baris Detail juga (agar tidak melayang sendirian jika sedang terbuka)
                    $detailRow.hide();
                }
            });

            // Update Counter Total Dosen
            var count = $lecturerRows.filter(':visible').length;
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

$(document).ready(function() {
    
    // --- 1. LOGIKA FILTER PRODI (GLOBAL) ---
    $('#prodiFilterDekanat').on('change', function () {
        var selectedProdi = $(this).val();
        var $rows = $('#dekanat-dosen-accordion > tr[data-prodi]'); // Hanya baris parent (dosen)
        
        if (selectedProdi === 'all') {
            $rows.show();
        } else {
            $rows.hide();
            $rows.filter('[data-prodi="' + selectedProdi + '"]').show();
        }
        
        // Update Counter
        $('#totalDosenText').text('Total: ' + $rows.filter(':visible').length + ' Dosen');
    });

    $(document).on('click', '.btn-filter-inner', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var filterType = $btn.data('filter'); // 'all', 'ongoing', 'selesai'
        
        // 1. Ubah Tampilan Tombol
        $btn.siblings().removeClass('btn-primary').addClass('btn-secondary');
        $btn.removeClass('btn-secondary').addClass('btn-primary');

        // 2. Cari Container & Baris Data
        var $container = $btn.closest('.collapse');
        var $tbody = $container.find('table.inner-table tbody');
        
        // Ambil hanya baris data mahasiswa (abaikan baris pesan kosong jika sudah ada)
        var $studentRows = $tbody.find('tr[data-status]'); 

        // 3. Logika Filtering
        if (filterType === 'all') {
            $studentRows.show();
        } else {
            $studentRows.hide();
            $studentRows.filter(function() {
                return $(this).data('status') === filterType;
            }).show();
        }

        // 4. LOGIKA BARU: Cek Empty State (Tidak ada data)
        // Hitung berapa baris mahasiswa yang terlihat
        var visibleCount = $studentRows.filter(':visible').length;
        var $emptyMsgRow = $tbody.find('.dynamic-empty-msg'); // Cari baris pesan kosong custom

        if (visibleCount === 0) {
            // Jika tidak ada mahasiswa yang tampil, munculkan pesan
            if ($emptyMsgRow.length === 0) {
                // Buat baris baru jika belum ada
                var msg = '<tr class="dynamic-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-3">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>';
                $tbody.append(msg);
            } else {
                // Tampilkan jika sudah ada (tapi tersembunyi)
                $emptyMsgRow.show();
            }
        } else {
            // Jika ada mahasiswa, sembunyikan pesan kosong
            if ($emptyMsgRow.length > 0) {
                $emptyMsgRow.hide();
            }
        }
    });

});

$(document).on('click', '.btn-filter', function() {
    var $btn = $(this);
    var filterType = $btn.data('filter'); // 'all', 'ongoing', 'selesai'
    
    // 1. Ubah Tampilan Tombol
    $btn.siblings().removeClass('btn-primary').addClass('btn-secondary');
    $btn.removeClass('btn-secondary').addClass('btn-primary');

    // 2. Cari Container Tabel & Mobile
    // Mencari baris row tabel yang terletak setelah row tombol filter
    var $dashboardRow = $btn.closest('.row').next('.row'); 
    var $tbody = $dashboardRow.find('table tbody');
    var $rows = $tbody.find('tr[data-status]'); // Hanya baris data (bukan baris pesan kosong)

    // 3. Filter Baris Tabel (Desktop)
    if (filterType === 'all') {
        $rows.show();
    } else {
        $rows.hide();
        $rows.filter('[data-status="' + filterType + '"]').show();
    }

    // --- LOGIKA PESAN "TIDAK ADA DATA" (DESKTOP) ---
    var visibleCount = $rows.filter(':visible').length;
    var $emptyRow = $tbody.find('.dosen-empty-msg'); // Cari baris pesan kosong

    if (visibleCount === 0) {
        if ($emptyRow.length === 0) {
            // Buat baris pesan jika belum ada
            var msg = '<tr class="dosen-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-4">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>';
            $tbody.append(msg);
        } else {
            $emptyRow.show();
        }
    } else {
        // Sembunyikan pesan jika data ada
        if ($emptyRow.length > 0) {
            $emptyRow.hide();
        }
    }

    // 4. Filter Mobile (Optional - Jika Anda menggunakan view mobile)
    var $mobileContainer = $dashboardRow.find('.d-md-none');
    var $mobileCards = $mobileContainer.children('div[data-status]'); // Div pembungkus yg kita buat tadi

    if ($mobileCards.length > 0) {
        if (filterType === 'all') {
            $mobileCards.show();
        } else {
            $mobileCards.hide();
            $mobileCards.filter('[data-status="' + filterType + '"]').show();
        }

        // Logika Pesan Kosong Mobile
        var visibleMobile = $mobileCards.filter(':visible').length;
        var $emptyMobile = $mobileContainer.find('.mobile-empty-msg');

        if (visibleMobile === 0) {
            if ($emptyMobile.length === 0) {
                $mobileContainer.append('<div class="text-center text-muted font-italic py-4 mobile-empty-msg">Tidak ada data.</div>');
            } else {
                $emptyMobile.show();
            }
        } else {
            $emptyMobile.hide();
        }
    }
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