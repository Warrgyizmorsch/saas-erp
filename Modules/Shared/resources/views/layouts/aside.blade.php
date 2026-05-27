@php

use App\Models\TenantModule;
use Illuminate\Support\Facades\Route;

$modules = TenantModule::where(
    'tenant_id',
    tenant()->id
)
    ->where('enabled', true)
    ->get();

/**
 * Recursive menu renderer
 */
function renderMenuItem($item)
{
    $hasChildren = !empty($item['children']);

    $html = '<li class="nxl-item ' . ($hasChildren ? 'nxl-hasmenu' : '') . '">';

    $route = 'javascript:void(0);';

    if (isset($item['route'])) {

        // If route starts with / treat as URL
        if (str_starts_with($item['route'], '/')) {

            $route = url($item['route']);

        }
        // Otherwise treat as named route
        elseif (Route::has($item['route'])) {

            $route = route($item['route']);

        }
    }

    $html .= '<a href="' . $route . '" class="nxl-link">';

    if (!empty($item['icon'])) {

        $html .= '
                <span class="nxl-micon">
                    <i class="' . $item['icon'] . '"></i>
                </span>
            ';
    }

    $html .= '
            <span class="nxl-mtext">
                ' . $item['title'] . '
            </span>
        ';

    if ($hasChildren) {

        $html .= '
                <span class="nxl-arrow">
                    <i class="feather-chevron-right"></i>
                </span>
            ';
    }

    $html .= '</a>';

    // CHILD MENUS
    if ($hasChildren) {

        $html .= '<ul class="nxl-submenu">';

        foreach ($item['children'] as $child) {

            $html .= renderMenuItem($child);

        }

        $html .= '</ul>';
    }

    $html .= '</li>';

    return $html;
}

@endphp

<nav class="nxl-navigation">

    <div class="navbar-wrapper">

        {{-- LOGO --}}
        <div class="m-header">

            <a href="{{ route('dashboard') }}" class="b-brand">

                <img src="{{ asset('images/mewar-logo.png') }}" alt="Logo" class="logo logo-lg" style="width: 230px; max-height: 150px; margin: 9px 0px 0px -24px;">

                <img src="{{ asset('images/logo.png') }}" alt="Small Logo" class="logo logo-sm">

            </a>

        </div>

        {{-- SIDEBAR CONTENT --}}
        <div class="navbar-content">

            <ul class="nxl-navbar">

                {{-- DASHBOARD --}}
                <li class="nxl-item">

                    <a href="{{ route('dashboard') }}" class="nxl-link">

                        <span class="nxl-micon">
                            <i class="feather-home"></i>
                        </span>

                        <span class="nxl-mtext">
                            Dashboard
                        </span>

                    </a>

                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-users"></i>
                        </span>
                        <span class="nxl-mtext">Users</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('users.index') }}">Users List</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('users.session') }}">Login History & Sessions</a>
                        </li>
                    </ul>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon">
                            <i class="feather-shield"></i>
                        </span>
                        <span class="nxl-mtext">Roles & Rights</span>
                        <span class="nxl-arrow">
                            <i class="feather-chevron-right"></i>
                        </span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('routes.index') }}">Routes Management</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('menus.index') }}">Menus Configuration</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('roles.index') }}">Role Management</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('role-permissions.index') }}">Role Permissions</a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="{{ route('user-permissions.index') }}">User Overrides</a>
                        </li>
                    </ul>
                </li>

                {{-- DYNAMIC MODULE MENUS --}}
                @foreach($modules as $module)

                    @php

    $menuPath = base_path(
        'Modules/' .
        $module->module .
        '/config/menu.php'
    );

                    @endphp

                    @if(file_exists($menuPath))

                        @php
        $menu = include($menuPath);
                        @endphp

                        {{-- MODULE TITLE --}}
                        <li class="nxl-item nxl-caption">
                            <label>
                                {{ $menu['name'] ?? $module->module }}
                            </label>
                        </li>

                        {{-- MODULE ITEMS --}}
                        @foreach($menu['items'] ?? [] as $item)

                            {!! renderMenuItem($item) !!}

                        @endforeach

                    @endif

                @endforeach

            </ul>

        </div>

    </div>

</nav>