@php

use Modules\Shared\App\Models\Menu;
use Illuminate\Support\Facades\Route;

$user = auth()->user();
$menus = $user ? Menu::getMenusForUser($user) : collect();

/**
 * Recursive menu renderer for submenus
 */
if (!function_exists('renderMenuItem')) {
    function renderMenuItem($item)
    {
        $children = $item->childrenRecursive;
        $hasChildren = $children->isNotEmpty();

        $html = '<li class="nxl-item ' . ($hasChildren ? 'nxl-hasmenu' : '') . '">';

        $route = 'javascript:void(0);';

        if ($item->route) {
            $routeName = $item->route->route_name;

            // If route starts with / treat as URL
            if (str_starts_with($routeName, '/')) {
                $route = url($routeName);
            }
            // Otherwise treat as named route
            elseif (Route::has($routeName)) {
                $route = route($routeName);
            } else {
                $route = url($routeName); // fallback
            }
        }

        $html .= '<a href="' . $route . '" class="nxl-link">';

        if (!empty($item->icon)) {
            $html .= '
                    <span class="nxl-micon">
                        <i class="' . $item->icon . '"></i>
                    </span>
                ';
        }

        $html .= '
                <span class="nxl-mtext">
                    ' . $item->title . '
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
            foreach ($children as $child) {
                $html .= renderMenuItem($child);
            }
            $html .= '</ul>';
        }

        $html .= '</li>';

        return $html;
    }
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

                @foreach($menus as $menu)
                    @if($menu->title === 'CRM' || $menu->title === 'HRMS' || $menu->title === 'Inventory')
                        @if(function_exists('tenant_module_enabled') && !tenant_module_enabled($menu->title))
                            @continue
                        @endif
                        {{-- MODULE TITLE CAPTION --}}
                        <li class="nxl-item nxl-caption">
                            <label>
                                {{ $menu->title }}
                            </label>
                        </li>
                        {{-- MODULE ITEMS (Render its children as top-level items) --}}
                        @foreach($menu->childrenRecursive as $child)
                            {!! renderMenuItem($child) !!}
                        @endforeach
                    @else
                        {{-- STANDARD MENU ITEM --}}
                        {!! renderMenuItem($menu) !!}
                    @endif
                @endforeach

            </ul>

        </div>

    </div>

</nav>