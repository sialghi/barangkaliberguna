@props(['id', 'name', 'prodi', 'count', 'ongoing', 'finished', 'parent' => null, 'exportUrl' => null])

<div class="card mb-3 border shadow-sm mobile-card" style="border-radius: 8px;" {{ $attributes }}>
    <div class="card-header bg-white p-3 collapsed" data-toggle="collapse" data-target="#mobile-collapse-{{ $id }}"
        aria-expanded="false" style="cursor: pointer; border-bottom: none;">

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="font-weight-bold mb-1 text-dark" style="font-size: 1rem;">{{ $name }}</h6>
                <div class="text-muted small mb-2">{{ $prodi }}</div>
                <div class="small font-weight-bold">
                    <span style="color: #fd7e14;">Ongoing: {{ $ongoing }}</span>
                    <span class="mx-1"></span>
                    <span style="color: #28a745;">Selesai: {{ $finished }}</span>
                </div>
            </div>
            <i class="fas fa-chevron-down text-muted transition-icon"></i>
        </div>
    </div>

    <div id="mobile-collapse-{{ $id }}" class="collapse" @if($parent) data-parent="{{ $parent }}" @endif>
        <div class="card-body bg-light p-3 border-top">
            <h6 class="font-weight-bold mb-3 small text-dark">Daftar Mahasiswa ({{ $count }})</h6>
            <div class="btn-group w-100 mb-3" role="group">
                <button type="button" class="btn btn-primary btn-sm btn-filter-inner shadow-sm"
                    data-target="#mobile-collapse-{{ $id }}" data-filter="all">Semua</button>
                <button type="button" class="btn btn-secondary btn-sm btn-filter-inner shadow-sm"
                    data-target="#mobile-collapse-{{ $id }}" data-filter="selesai">Selesai</button>
                <button type="button" class="btn btn-secondary btn-sm btn-filter-inner shadow-sm"
                    data-target="#mobile-collapse-{{ $id }}" data-filter="ongoing">Ongoing</button>
            </div>

            @if ($exportUrl)
                <a href="{{ $exportUrl }}" class="btn btn-success btn-sm w-100 mb-3">
                    <i class="fas fa-file-excel mr-1"></i> Download Excel
                </a>
            @endif

            <div class="student-list">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>