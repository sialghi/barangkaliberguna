@props(['id', 'title'])

<div class="card">
    <div class="card-header border-0">
        <h3 class="card-title font-weight-bold">{{ $title }}</h3>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-sm-8">
                <canvas id="{{ $id }}"
                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
            <div class="col-sm-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>