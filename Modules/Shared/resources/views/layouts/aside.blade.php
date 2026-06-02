@php

use Modules\Shared\App\Models\Menu;
use Illuminate\Support\Facades\Route;

$user = auth()->user();
$menus = $user ? Menu::getMenusForUser($user) : collect();

/**
 * Recursive menu renderer for submenus
 */
if (!function_exists('renderMenuItem')) {
    function renderMenuItem($item, $customClass = '')
    {
        $children = $item->childrenRecursive;
        $hasChildren = $children->isNotEmpty();

        $html = '<li class="nxl-item ' . ($hasChildren ? 'nxl-hasmenu' : '') . ' ' . $customClass . '">';

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
                    @php $upperTitle = strtoupper($menu->title); @endphp
                    @if($upperTitle === 'CRM' || $upperTitle === 'HRMS' || $upperTitle === 'INVENTORY')
                        @if(function_exists('tenant_module_enabled') && !tenant_module_enabled($menu->title))
                            @continue
                        @endif
                        {{-- MODULE TITLE CAPTION WITH BULLETPROOF CLICK TRIGGER --}}
                        <li class="nxl-item nxl-caption premium-module-header" data-module="{{ strtolower($menu->title) }}" onclick="toggleModuleSidebar('{{ strtolower($menu->title) }}', this)">
                            <div class="premium-module-header-content">
                                <span class="premium-module-header-title">{{ $upperTitle }}</span>
                                <span class="premium-module-accordion-btn">
                                    <span class="premium-module-arrow-container">
                                        <i class="feather-chevron-right premium-module-arrow"></i>
                                    </span>
                                </span>
                            </div>
                        </li>
                        {{-- MODULE ITEMS (Render as sibling main items, with custom helper classes) --}}
                        @foreach($menu->childrenRecursive as $child)
                            {!! renderMenuItem($child, 'premium-module-child module-' . strtolower($menu->title)) !!}
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

{{-- PREMIUM SIDEBAR CSS AND INTERACTIONS --}}
<style>
/* ==========================================================================
   PREMIUM SIDEBAR ACCORDION & TIMELINE CUSTOM DESIGN SYSTEM
   ========================================================================== */

/* Cohesive active & hover state highlights globally across the entire sidebar navigation */
.nxl-navigation .nxl-navbar a {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    border: 1px solid transparent;
}

/* Hover Link */
.nxl-navigation .nxl-navbar li:hover > a {
    color: #ff7a00 !important;
    transform: translateX(4px);
    background: rgba(255, 122, 0, 0.02) !important;
    border-radius: 10px;
}

/* Hover Icon & Arrow */
.nxl-navigation .nxl-navbar li:hover > a .nxl-micon i,
.nxl-navigation .nxl-navbar li:hover > a .nxl-arrow i {
    color: #ff7a00 !important;
    transition: color 0.25s ease;
}

/* Active Link */
.nxl-navigation .nxl-navbar li.active > a {
    background: rgba(255, 122, 0, 0.06) !important;
    color: #ff5100 !important;
    border-radius: 10px;
    border: 1px solid rgba(255, 122, 0, 0.12) !important;
    box-shadow: 0 4px 12px rgba(255, 122, 0, 0.02);
}

/* Active Icon & Arrow */
.nxl-navigation .nxl-navbar li.active > a .nxl-micon i,
.nxl-navigation .nxl-navbar li.active > a .nxl-arrow i {
    color: #ff5100 !important;
}

/* Collapsible Module Headers (Left-aligned clean caption) */
.premium-module-header {
    cursor: pointer;
    user-select: none;
    padding: 22px 24px 10px 24px !important;
    background: transparent !important;
    border: none !important;
    display: block !important;
}

.premium-module-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.premium-module-header-title {
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #64748b !important;
    transition: color 0.25s ease;
}

.premium-module-header:hover .premium-module-header-title {
    color: #ff7a00 !important;
}

.premium-module-accordion-btn {
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}

.premium-module-header:hover .premium-module-accordion-btn {
    color: #ff7a00;
}

/* Rotatable chevron container */
.premium-module-arrow-container {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.premium-module-arrow {
    font-size: 13px !important;
}

/* When expanded (default), rotate chevron to point down (90deg) */
.premium-module-header .premium-module-arrow-container {
    transform: rotate(90deg) !important;
}

/* When collapsed, rotate chevron back to point right (0deg) */
.premium-module-header.collapsed .premium-module-arrow-container {
    transform: rotate(0deg) !important;
}

/* Connective vertical dashed timeline line globally for all submenus */
.nxl-navigation .nxl-submenu {
    position: relative;
    padding-left: 20px !important;
    margin-left: 32px !important;
    margin-top: 6px !important;
    margin-bottom: 8px !important;
    border-left: 1.5px dashed rgba(226, 232, 240, 0.8) !important;
    transition: border-color 0.3s ease;
    background: transparent !important;
}

/* Hovering a menu changes its submenu line color */
.nxl-navigation .nxl-navbar li:hover > .nxl-submenu {
    border-left-color: rgba(255, 122, 0, 0.2) !important;
}

/* Active menu changes its submenu line color */
.nxl-navigation .nxl-navbar li.active > .nxl-submenu {
    border-left-color: rgba(255, 122, 0, 0.35) !important;
}

/* Submenu circular timeline bullet node styling */
.nxl-navigation .nxl-submenu li {
    position: relative;
    list-style: none !important;
}

.nxl-navigation .nxl-submenu li::before {
    content: "";
    position: absolute;
    left: -21px;
    top: 50%;
    transform: translateY(-50%);
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #cbd5e1;
    border: 1.5px solid #fff;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 5;
}

/* Hover node effect */
.nxl-navigation .nxl-submenu li:hover::before {
    background-color: #ff7a00 !important;
    transform: translateY(-50%) scale(1.4);
    box-shadow: 0 0 8px rgba(255, 122, 0, 0.6);
}

/* Active node effect */
.nxl-navigation .nxl-submenu li.active::before {
    background-color: #ff5100 !important;
    transform: translateY(-50%) scale(1.4);
    box-shadow: 0 0 8px rgba(255, 81, 0, 0.7);
}

/* Remove default dots/squares in standard styles */
.nxl-navigation .nxl-submenu li a::before {
    display: none !important;
}

/* Submenu Link Hover & Active Effects */
.nxl-navigation .nxl-submenu .nxl-link {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #4b5563 !important;
    padding: 8px 0px !important;
    background: transparent !important;
    border: none !important;
}

.nxl-navigation .nxl-submenu .nxl-link:hover {
    padding-left: 5px !important;
    color: #ff7a00 !important;
    transform: none !important;
}

.nxl-navigation .nxl-submenu li.active > .nxl-link {
    color: #ff5100 !important;
    font-weight: 600 !important;
    padding-left: 4px !important;
}

/* Normal Sub-Submenu Connector timeline support */
.nxl-navigation .nxl-submenu .nxl-submenu {
    border-left: 1.5px dashed rgba(226, 232, 240, 0.6) !important;
    padding-left: 15px !important;
    margin-left: 15px !important;
}

.nxl-navigation .navbar-content .nxl-submenu .nxl-link {
    margin-left: 0px !important;
}
</style>

{{-- Accordion Expanded State Persistence Script --}}
<script>
// Bulletproof global module toggle function
function toggleModuleSidebar(moduleName, headerEl) {
    const isCollapsed = headerEl.classList.contains('collapsed');
    
    if (typeof jQuery !== 'undefined') {
        const $ = jQuery;
        const $header = $(headerEl);
        const $children = $('.premium-module-child.module-' + moduleName);
        
        if (isCollapsed) {
            $header.removeClass('collapsed');
            $children.stop(true, true).slideDown(250);
            localStorage.setItem('sidebar_module_' + moduleName, 'expanded');
        } else {
            $header.addClass('collapsed');
            $children.stop(true, true).slideUp(250);
            localStorage.setItem('sidebar_module_' + moduleName, 'collapsed');
        }
    } else {
        // Vanilla fallback
        const children = document.querySelectorAll('.premium-module-child.module-' + moduleName);
        if (isCollapsed) {
            headerEl.classList.remove('collapsed');
            children.forEach(c => c.style.display = 'block');
            localStorage.setItem('sidebar_module_' + moduleName, 'expanded');
        } else {
            headerEl.classList.add('collapsed');
            children.forEach(c => c.style.display = 'none');
            localStorage.setItem('sidebar_module_' + moduleName, 'collapsed');
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Restore states for each module on page load
    const headers = document.querySelectorAll('.premium-module-header');
    
    headers.forEach(function (header) {
        const moduleName = header.getAttribute('data-module');
        const savedState = localStorage.getItem('sidebar_module_' + moduleName);
        const children = document.querySelectorAll('.premium-module-child.module-' + moduleName);
        
        // If a child is active, we MUST expand it and override any saved state
        let hasActiveChild = false;
        children.forEach(function (child) {
            if (child.classList.contains('active') || child.querySelector('.active') !== null) {
                hasActiveChild = true;
            }
        });
        
        if (hasActiveChild) {
            header.classList.remove('collapsed');
            children.forEach(c => c.style.display = 'block');
            localStorage.setItem('sidebar_module_' + moduleName, 'expanded');
        } else if (savedState === 'collapsed') {
            header.classList.add('collapsed');
            children.forEach(c => c.style.display = 'none');
        } else {
            // Default expanded
            header.classList.remove('collapsed');
            children.forEach(c => c.style.display = 'block');
        }
    });
});
</script>