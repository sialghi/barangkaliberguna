@php($logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout'))
@php($profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout'))

@if (config('adminlte.usermenu_profile_url', false))
    @php($profile_url = Auth::user()->adminlte_profile_url())
@endif

@if (config('adminlte.use_route_url', false))
    @php($profile_url = $profile_url ? route($profile_url) : '')
    @php($logout_url = $logout_url ? route($logout_url) : '')
@else
    @php($profile_url = $profile_url ? url($profile_url) : '')
    @php($logout_url = $logout_url ? url($logout_url) : '')
@endif

<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if (config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}" class="user-image img-circle elevation-2"
                alt="{{ Auth::user()->name }}">
        @endif
        <span @if (config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        {{-- User menu header --}}
        @if (!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li
                class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if (!config('adminlte.usermenu_image')) h-auto @endif">
                @if (config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}" class="img-circle elevation-2"
                        alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if (!config('adminlte.usermenu_image')) mt-0 d-flex @endif">
                    <span class="d-flex flex-column mr-2">
                        {{ Auth::user()->name }}
                    </span>
                    <span class="d-flex flex-column align-items-start">
                        <span class="d-flex flex-column align-items-start ">
                            @foreach ($userPivot as $pivot)
                                <span class="badge badge-warning mb-1">
                                    @if ($pivot->role->nama === 'dekan')
                                        <i class="fas fa-fw fa-crown"></i>
                                        Dekan - {{ $pivot->fakultas->nama }}
                                    @elseif ($pivot->role->nama === 'wadek_satu')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Wakil Dekan I - {{ $pivot->fakultas->nama }}
                                    @elseif ($pivot->role->nama === 'wadek_dua')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Wakil Dekan II - {{ $pivot->fakultas->nama }}
                                    @elseif ($pivot->role->nama === 'wadek_tiga')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Wakil Dekan III - {{ $pivot->fakultas->nama }}
                                    @elseif ($pivot->role->nama === 'admin_dekanat')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Admin Dekanat - {{ $pivot->fakultas->nama }}
                                    @elseif ($pivot->role->nama === 'kaprodi')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Kaprodi - {{ $pivot->programStudi->nama }}
                                    @elseif ($pivot->role->nama === 'sekprodi')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Sekprodi - {{ $pivot->programStudi->nama }}
                                    @elseif ($pivot->role->nama === 'admin_prodi')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Admin Prodi - {{ $pivot->programStudi->nama }}
                                    @elseif ($pivot->role->nama === 'dosen')
                                        <i class="fas fa-fw fa-user-tie"></i>
                                        Dosen - {{ $pivot->programStudi->nama }}
                                    @elseif ($pivot->role->nama === 'mahasiswa')
                                        <i class="fas fa-fw fa-user"></i>
                                        Mahasiswa - {{ $pivot->programStudi->nama }}
                                    @elseif ($pivot->role->nama === 'staf')
                                        <i class="fas fa-fw fa-user"></i>
                                        Staf - {{ $pivot->programStudi->nama }}
                                    @endif
                                </span>
                            @endforeach
                        </span>
                    </span>
                    @if (config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu('navbar-user'), 'item')

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            {{-- <ul>
                <li>
                    @foreach ($userRole as $role)
                        <span class="badge badge-info">{{ $role }}</span>
                    @endforeach
                </li>
            </ul> --}}
            @if ($profile_url)
                <a href="{{ $profile_url }}" class="btn btn-default btn-flat">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    {{ __('adminlte::menu.profile') }}
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if (!$profile_url) btn-block @endif"
                href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                {{ __('adminlte::adminlte.log_out') }}
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if (config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>

    </ul>

</li>
