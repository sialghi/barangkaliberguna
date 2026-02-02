@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
<link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        // --- 1. DOSEN CHART ---
        // PENTING: Data chart ini ($bimbinganSelesai, $bimbinganOngoing) 
        // harus sudah dihitung dengan logika Nilai Skripsi Lengkap di Controller.
        @if(in_array('dosen', $userRole))
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
                    options: { maintainAspectRatio: false, responsive: true, legend: { display: false }, cutout: '0%' }
                })
            }
        @endif

        // --- 2. LOGIKA FILTER PRODI (GLOBAL) ---
        $('#prodiFilterDekanat').on('change', function () {
            var selectedProdi = $(this).val();
            var $tbody = $('#dekanat-dosen-accordion');
            var $lecturerRows = $tbody.find('tr[data-prodi]');

            $lecturerRows.each(function() {
                var $thisDosenRow = $(this);
                var $detailRow = $thisDosenRow.next('tr');
                
                if (selectedProdi === 'all' || $thisDosenRow.data('prodi') === selectedProdi) {
                    $thisDosenRow.show();
                    $detailRow.css('display', ''); 
                } else {
                    $thisDosenRow.hide();
                    $detailRow.hide();
                }
            });

            var count = $lecturerRows.filter(':visible').length;
            $('#totalDosenText').text('Total: ' + count + ' Dosen');
        });

        // --- 3. CHART DEKANAT ---
        @if(array_intersect(['dekan', 'wadek_satu', 'admin_dekanat'], $userRole))
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
                            y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                        }
                    }
                });
            }
        @endif

        // --- 4. CHART PRODI ---
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

        // --- 5. LOGIKA FILTER STATUS (LOKAL CHILD) ---
        $(document).on('click', '.btn-filter-inner', function(e) {
            e.preventDefault(); e.stopPropagation();
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
                $studentRows.filter(function() { return $(this).data('status') === filterType; }).show();
            }

            // Empty State Logic
            var visibleCount = $studentRows.filter(':visible').length;
            var $emptyMsgRow = $tbody.find('.dynamic-empty-msg');

            if (visibleCount === 0) {
                if ($emptyMsgRow.length === 0) {
                    $tbody.append('<tr class="dynamic-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-3">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>');
                } else { $emptyMsgRow.show(); }
            } else {
                if ($emptyMsgRow.length > 0) { $emptyMsgRow.hide(); }
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

            if (filterType === 'all') { $rows.show(); } else {
                $rows.hide(); $rows.filter('[data-status="' + filterType + '"]').show();
            }

            var visibleCount = $rows.filter(':visible').length;
            var $emptyRow = $tbody.find('.dosen-empty-msg');

            if (visibleCount === 0) {
                if ($emptyRow.length === 0) {
                    $tbody.append('<tr class="dosen-empty-msg"><td colspan="5" class="text-center text-muted font-italic py-4">Tidak ada mahasiswa bimbingan dengan status ini.</td></tr>');
                } else { $emptyRow.show(); }
            } else { if ($emptyRow.length > 0) { $emptyRow.hide(); } }

            // Mobile Filter Logic
            var $mobileContainer = $dashboardRow.find('.d-md-none');
            var $mobileCards = $mobileContainer.children('div[data-status]');
            if ($mobileCards.length > 0) {
                if (filterType === 'all') { $mobileCards.show(); } else {
                    $mobileCards.hide(); $mobileCards.filter('[data-status="' + filterType + '"]').show();
                }
                // Mobile Empty Msg
                var visibleMobile = $mobileCards.filter(':visible').length;
                var $emptyMobile = $mobileContainer.find('.mobile-empty-msg');
                if (visibleMobile === 0) {
                    if ($emptyMobile.length === 0) {
                         $mobileContainer.append('<div class="text-center text-muted font-italic py-4 mobile-empty-msg">Tidak ada data.</div>');
                    } else { $emptyMobile.show(); }
                } else { $emptyMobile.hide(); }
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

{{-- 1. INFO BOX SURAT --}}
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

{{-- 2. DEKANAT DASHBOARD --}}
@if(array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'], $userRole))
    <x-dekanat-dashboard :monitoringDekanat="$monitoringDekanat" />
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

{{-- 4. DOSEN DASHBOARD --}}
@if(in_array('dosen', $userRole))
    {{-- Kita harus kirim variabel yang berisi status yang benar ke component --}}
    {{-- Karena component x-dosen-dashboard menerima $bimbingan apa adanya, --}}
    {{-- kita perlu memodifikasi collection $bimbingan di sini sebelum dikirim (opsional) --}}
    {{-- atau biarkan logic status di dalam component/view child seperti di bawah --}}

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

{{-- 5. MAHASISWA DASHBOARD --}}
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