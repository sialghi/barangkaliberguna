@props(['name', 'nim', 'title', 'count', 'status'])

<div class="card mx-2 mt-2 mobile-card" data-status="{{ strtolower($status) == 'on-going' || strtolower($status) == 'ongoing' ? 'ongoing' : 'selesai' }}">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="font-weight-bold mb-0" style="font-size: 1rem;">{{ $name }}</h6>
                <span class="text-muted" style="font-size: 0.85rem;">{{ $nim }}</span>
            </div>
            @if(strtolower($status) == 'on-going' || strtolower($status) == 'ongoing')
                <span class="badge badge-warning px-2 py-1" style="font-size: 0.75rem; color: #d9534f; background-color: #f7e1c280;">Ongoing</span>
            @else
                <span class="badge badge-success px-2 py-1" style="font-size: 0.75rem; background-color: #d4edda; color: #28a745;">Selesai</span>
            @endif
        </div>
        <p class="card-text text-dark mb-2" style="font-size: 0.9rem; line-height: 1.4;">
            {{ $title }}
        </p>
        <hr class="my-2">
        <p class="mb-0 font-weight-bold" style="font-size: 0.9rem;">Bimbingan: <span class="font-weight-normal">{{ $count }}x</span></p>
    </div>
</div>