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

                <img src="{{ asset('images/WARR LOGO.webp') }}" alt="Logo" class="logo logo-lg" style="width: 230px; max-height: 150px; margin: 9px 0px 0px -24px;">

                <img src="{{ asset('images/WARR LOGO.webp') }}" alt="Small Logo" class="logo logo-sm">

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

                @if(function_exists('tenant_module_enabled') && tenant_module_enabled('POS'))
                    {{-- POS MODULE TITLE CAPTION WITH BULLETPROOF CLICK TRIGGER --}}
                    <li class="nxl-item nxl-caption premium-module-header" data-module="pos" onclick="toggleModuleSidebar('pos', this)">
                        <div class="premium-module-header-content">
                            <span class="premium-module-header-title">POS</span>
                            <span class="premium-module-accordion-btn">
                                <span class="premium-module-arrow-container">
                                    <i class="feather-chevron-right premium-module-arrow"></i>
                                </span>
                            </span>
                        </div>
                    </li>
                    {{-- POS MODULE ITEMS --}}
                    <li class="nxl-item premium-module-child module-pos">
                        <a href="{{ env('POS_URL', 'https://pos-erp-frontend.vercel.app/pos') }}" class="nxl-link" target="_blank">
                            <span class="nxl-micon">
                                <i class="feather-monitor"></i>
                            </span>
                            <span class="nxl-mtext">POS Dashboard</span>
                        </a>
                    </li>
                    <li class="nxl-item premium-module-child module-pos">
                        <a href="{{ env('POS_URL', 'https://pos-erp-frontend.vercel.app/pos') }}" class="nxl-link" target="_blank">
                            <span class="nxl-micon">
                                <i class="feather-file-text"></i>
                            </span>
                            <span class="nxl-mtext">Billing Management</span>
                        </a>
                    </li>
                @endif

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
/* ==========================================================
   SIDEBAR THEME VARIABLES
   Change colors only here
========================================================== */
:root {

    --sidebar-primary: #3B82F6;
    --sidebar-primary-hover: #2563EB;
    --sidebar-primary-active: #1D4ED8;
    --sidebar-active-bg: #DBEAFE;

    --sidebar-hover-bg: rgba(59, 130, 246, 0.08);

    --sidebar-border: rgba(59, 130, 246, 0.20);

    --sidebar-timeline: rgba(191, 219, 254, 0.9);
    --sidebar-timeline-hover: rgba(59, 130, 246, 0.35);
    --sidebar-timeline-active: rgba(59, 130, 246, 0.60);

    --sidebar-node: #BFDBFE;

    --sidebar-text: #475569;
    --sidebar-heading: #64748b;
    --sidebar-muted: #94a3b8;

    --sidebar-shadow: rgba(59, 130, 246, 0.08);
    --sidebar-glow: rgba(59, 130, 246, 0.35);
    --sidebar-glow-active: rgba(29, 78, 216, 0.45);
}

/* Hover Link */
.nxl-navigation .nxl-navbar li:hover > a {
    color: var(--sidebar-primary-hover) !important;
    transform: translateX(4px);
    background: var(--sidebar-hover-bg) !important;
    border-radius: 10px;
}

/* Hover Icon & Arrow */
.nxl-navigation .nxl-navbar li:hover > a .nxl-micon i,
.nxl-navigation .nxl-navbar li:hover > a .nxl-arrow i {
    color: var(--sidebar-primary-hover) !important;
    transition: color 0.25s ease;
}

/* Active Link */
.nxl-navigation .nxl-navbar li.active > a {
    background: var(--sidebar-active-bg) !important;
    color: var(--sidebar-primary-active) !important;
    border-radius: 10px;
    border: 1px solid var(--sidebar-border) !important;
    box-shadow: 0 4px 12px var(--sidebar-shadow);
}

/* Active Icon & Arrow */
.nxl-navigation .nxl-navbar li.active > a .nxl-micon i,
.nxl-navigation .nxl-navbar li.active > a .nxl-arrow i {
    color: var(--sidebar-primary-active) !important;
}

/* Module Headers */
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
    color: var(--sidebar-heading) !important;
    transition: color 0.25s ease;
}

.premium-module-header:hover .premium-module-header-title {
    color: var(--sidebar-primary-hover) !important;
}

.premium-module-accordion-btn {
    color: var(--sidebar-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s ease;
}

.premium-module-header:hover .premium-module-accordion-btn {
    color: var(--sidebar-primary-hover);
}

/* Chevron */
.premium-module-arrow-container {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.premium-module-arrow {
    font-size: 13px !important;
}

.premium-module-header .premium-module-arrow-container {
    transform: rotate(90deg) !important;
}

.premium-module-header.collapsed .premium-module-arrow-container {
    transform: rotate(0deg) !important;
}

/* Timeline */
.nxl-navigation .nxl-submenu {
    position: relative;
    padding-left: 20px !important;
    margin-left: 32px !important;
    margin-top: 6px !important;
    margin-bottom: 8px !important;
    border-left: 1.5px dashed var(--sidebar-timeline) !important;
    transition: border-color 0.3s ease;
    background: transparent !important;
}

.nxl-navigation .nxl-navbar li:hover > .nxl-submenu {
    border-left-color: var(--sidebar-timeline-hover) !important;
}

.nxl-navigation .nxl-navbar li.active > .nxl-submenu {
    border-left-color: var(--sidebar-timeline-active) !important;
}

/* Timeline Nodes */
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
    background-color: var(--sidebar-node);
    border: 1.5px solid #fff;
    transition: all 0.25s ease;
    z-index: 5;
}

.nxl-navigation .nxl-submenu li:hover::before {
    background-color: var(--sidebar-primary-hover) !important;
    transform: translateY(-50%) scale(1.4);
    box-shadow: 0 0 8px var(--sidebar-glow);
}

.nxl-navigation .nxl-submenu li.active::before {
    background-color: var(--sidebar-primary-active) !important;
    transform: translateY(-50%) scale(1.4);
    box-shadow: 0 0 8px var(--sidebar-glow-active);
}

.nxl-navigation .nxl-submenu li a::before {
    display: none !important;
}

/* Submenu Links */
.nxl-navigation .nxl-submenu .nxl-link {
    transition: all 0.2s ease !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: var(--sidebar-text) !important;
    padding: 8px 0 !important;
    background: transparent !important;
    border: none !important;
}

.nxl-navigation .nxl-submenu .nxl-link:hover {
    padding-left: 5px !important;
    color: var(--sidebar-primary-hover) !important;
    transform: none !important;
}

.nxl-navigation .nxl-submenu li.active > .nxl-link {
    color: var(--sidebar-primary-active) !important;
    font-weight: 600 !important;
    padding-left: 4px !important;
}

/* Nested Submenus */
.nxl-navigation .nxl-submenu .nxl-submenu {
    border-left: 1.5px dashed var(--sidebar-timeline) !important;
    padding-left: 15px !important;
    margin-left: 15px !important;
}

.nxl-navigation .navbar-content .nxl-submenu .nxl-link {
    margin-left: 0 !important;
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