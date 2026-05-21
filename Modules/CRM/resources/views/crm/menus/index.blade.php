@extends('shared::layouts.app')

@section('content')
    <x-slot name="title">Menus Management</x-slot>

    <main>
        <div>
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manage Menus</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Menus</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <div class="crm-page-container">

        {{-- Add/Edit Form --}}
        <div class="card mb-4">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">{{ isset($editMenu) ? 'Edit Menu' : 'Add New Menu' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST"
                    action="{{ isset($editMenu) ? route('menus.update', $editMenu->id) : route('menus.store') }}">
                    @csrf
                    @if(isset($editMenu)) @method('PUT') @endif

                    <div class="row g-2">
                        {{-- Title --}}
                        <div class="col-md-3">
                            <input type="text" name="title" class="form-control" placeholder="Menu Title"
                                value="{{ old('title', $editMenu->title ?? '') }}" required>
                        </div>

                        {{-- Icon --}}
                        <div class="col-md-2">
                            <div class="input-group">
                                <input type="text" name="icon" id="iconInput" class="form-control"
                                    placeholder="Icon Class (e.g. fas fa-home)"
                                    value="{{ old('icon', $editMenu->icon ?? '') }}">

                                <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    <i class="{{ old('icon', $editMenu->icon ?? 'fas fa-icons') }}"></i>
                                </button>

                                <ul class="dropdown-menu p-2" style="max-height: 250px; overflow-y: auto;">
                                    @php
                                        $commonIcons = [
                                            // Dashboard & Navigation
                                            'fas fa-home' => 'Home',
                                            'fas fa-tachometer-alt' => 'Dashboard',
                                            'fas fa-bars' => 'Menus',
                                            'fas fa-route' => 'Routes',

                                            // User Management
                                            'fas fa-users' => 'Users',
                                            'fas fa-user' => 'User',
                                            'fas fa-user-plus' => 'Add User',
                                            'fas fa-user-edit' => 'Edit User',
                                            'fas fa-user-shield' => 'Roles',
                                            'fas fa-lock' => 'Permissions',
                                            'fas fa-key' => 'Access Control',
                                            'fas fa-id-badge' => 'Profile',

                                            // Settings & Configurations
                                            'fas fa-cogs' => 'Settings',
                                            'fas fa-sliders-h' => 'Preferences',
                                            'fas fa-wrench' => 'Tools',
                                            'fas fa-database' => 'Database',
                                            'fas fa-server' => 'Server',
                                            'fas fa-cog' => 'Configuration',

                                            // CRM Core Features
                                            'fas fa-handshake' => 'Clients',
                                            'fas fa-building' => 'Company',
                                            'fas fa-briefcase' => 'Projects',
                                            'fas fa-tasks' => 'Tasks',
                                            'fas fa-file-alt' => 'Documents',
                                            'fas fa-envelope' => 'Emails',
                                            'fas fa-phone' => 'Calls',
                                            'fas fa-comments' => 'Chat',

                                            // Sales & Finance
                                            'fas fa-shopping-cart' => 'Orders',
                                            'fas fa-file-invoice-dollar' => 'Invoices',
                                            'fas fa-credit-card' => 'Payments',
                                            'fas fa-wallet' => 'Finance',
                                            'fas fa-chart-line' => 'Reports',
                                            'fas fa-chart-pie' => 'Analytics',

                                            // Miscellaneous
                                            'fas fa-list' => 'List',
                                            'fas fa-bell' => 'Notifications',
                                            'fas fa-calendar-alt' => 'Calendar',
                                            'fas fa-clock' => 'Time Tracking',
                                            'fas fa-upload' => 'Upload',
                                            'fas fa-download' => 'Download',
                                            'fas fa-search' => 'Search',
                                            'fas fa-question-circle' => 'Help',
                                        ];
                                    @endphp
                                    @foreach($commonIcons as $class => $label)
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center"
                                                onclick="document.getElementById('iconInput').value='{{ $class }}'">
                                                <i class="{{ $class }} me-2"></i> {{ $label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        {{-- Parent --}}
                        <div class="col-md-3">
                            <select name="parent_id" class="form-select" data-select2-selector="tag">
                                <option value="">Select Parent</option>
                                @php
                                    function renderParentOptions($menus, $prefix = '', $excludeId = null, $selectedId = null)
                                    {
                                        foreach ($menus as $menu) {
                                            if ($menu->id == $excludeId)
                                                continue;
                                            $isSelected = $selectedId == $menu->id ? 'selected' : '';
                                            echo '<option value="' . $menu->id . '" ' . $isSelected . '>' . $prefix . $menu->title . '</option>';
                                            if ($menu->childrenRecursive->count()) {
                                                renderParentOptions($menu->childrenRecursive, $prefix . '--', $excludeId, $selectedId);
                                            }
                                        }
                                    }
                                    renderParentOptions($allMenus, '', $editMenu->id ?? null, old('parent_id', $editMenu->parent_id ?? null));
                                @endphp
                            </select>
                        </div>

                        {{-- Route --}}
                        <div class="col-md-3">
                            <select name="route_id" class="form-select" data-select2-selector="tag">
                                <option value="">Select Route</option>
                                @foreach(\App\Models\Route::where('is_deleted', 0)->get() as $route)
                                    <option value="{{ $route->id }}" {{ old('route_id', $editMenu->route_id ?? '') == $route->id ? 'selected' : '' }}>
                                        {{ $route->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sort Order --}}
                        <div class="col-md-1">
                            <input type="number" name="sort_order" class="form-control" placeholder="Sort"
                                value="{{ old('sort_order', $editMenu->sort_order ?? 1) }}">
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-2 mt-2 d-flex">
                            <button type="submit" class="btn btn-success w-100">
                                {{ isset($editMenu) ? 'Update' : 'Add' }}
                            </button>
                            @if(isset($editMenu))
                                <a href="{{ route('menus.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Menus Tree --}}
        <div class="card">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">Menus Tree</h5>
            </div>
            <div class="card-body">

                @php
                    function renderMenuTree($menu)
                    {
                        $hasChildren = $menu->childrenRecursive && $menu->childrenRecursive->count();

                        $html = '<li class="mb-2">';
                        $html .= '<div class="d-flex align-items-center gap-2 border-bottom">';
                        $html .= $menu->icon ? '<i class="' . $menu->icon . '"></i>' : '';
                        $html .= '<strong>' . $menu->title . '</strong>';
                        $html .= ' <small class="text-muted">(' . ($menu->route->name ?? 'No Route') . ')</small>';
                        $html .= '<div class="ms-auto d-flex gap-1">';
                        $html .= '<a href="' . route('menus.edit', $menu->id) . '" class="btn-edit"><i class="fas fa-edit"></i></a>';

                        // Delete form (manual CSRF + method)
                        $html .= '<form method="POST" action="' . route('menus.destroy', $menu->id) . '" class="d-inline">';
                        $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
                        $html .= '<input type="hidden" name="_method" value="DELETE">';
                        $html .= '<button type="submit" class="btn-delete" onclick="return confirm(\'Delete this menu?\')">';
                        $html .= '<i class="fas fa-trash"></i></button></form>';

                        $html .= '</div></div>';

                        if ($hasChildren) {
                            $html .= '<ul class="list-unstyled ps-4 mt-1">';
                            foreach ($menu->childrenRecursive as $child) {
                                $html .= renderMenuTree($child);
                            }
                            $html .= '</ul>';
                        }

                        $html .= '</li>';
                        return $html;
                    }
                @endphp

                <ul class="list-unstyled">
                    @foreach($allMenus as $menu)
                        {!! renderMenuTree($menu) !!}
                    @endforeach
                </ul>

            </div>
        </div>
    </div>
@endsection