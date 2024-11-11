@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand logo-switch d-flex flex-row align-items-center {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link logo-switch d-flex flex-row align-items-center {{ config('adminlte.classes_brand') }}"
    @endif>
    {{-- Small brand logo --}}
    <img src="{{ asset(config('adminlte.logo_img', 'vendor/adminlte/dist/img/AdminLTELogo.png')) }}"
    alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
    class="{{ config('adminlte.logo_img_class', 'brand-image-xl') }} logo-xs mt-2">

    {{-- Large brand logo --}}
    <img src="{{ asset(config('adminlte.logo_img_xl')) }}"
    alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
    class="{{ config('adminlte.logo_img_xl_class', 'brand-image-xs') }} logo-xl mt-1">
    <span class="mt-0 ml-5 text-sm">Sistem Informasi Layanan <br> Fakultas Sains dan Teknologi</span>
</a>
