{{-- Desktop View --}}
<div class="card bg-light d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table class="table table-striped projects" style="min-width: 800px; margin-bottom: 0;">
                <thead class="thead-dark">
                    <tr>
                        {{ $header }}
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($footer))
        <div class="card-footer py-2">
            {{ $footer }}
        </div>
    @endif
</div>

{{-- Mobile View --}}
<div class="d-md-none">
    {{ $mobile ?? '' }}
</div>