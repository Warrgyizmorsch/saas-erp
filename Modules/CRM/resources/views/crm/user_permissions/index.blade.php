@extends('shared::layouts.app')

@section('content')
    <x-slot name="title">User Permissions</x-slot>

    <div class="container">
        <h2 class="mb-4">Manage User Permissions</h2>

        <!-- Search -->
        <form method="GET" action="{{ route('user-permissions.index') }}" class="mb-3 d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search by email..."
                value="{{ request('search') }}">
            <button class="btn btn-primary">Search</button>
        </form>

        <div class="row">
            <!-- User List -->
            <div class="col-md-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header">Users</div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        @foreach($users as $user)
                            <a href="{{ route('user-permissions.index', ['user_id' => $user->id, 'search' => request('search')]) }}"
                                class="btn btn-outline-primary {{ $selectedUser && $selectedUser->id === $user->id ? 'active' : '' }}">
                                {{ $user->email }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="col-md-12">
                @if($selectedUser)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            Manage Permissions for: <strong>{{ $selectedUser->email }}</strong>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('user-permissions-id.update', $selectedUser->id) }}">
                                @csrf

                                <!-- Menus -->
                                <ul class="list-unstyled">
                                    @php
                                        if (!function_exists('renderUserPermissionTree')) {
                                            function renderUserPermissionTree($menus, $userMenuOverrides, $rolePermissions, $parentId = null)
                                            {
                                                foreach ($menus as $menu) {
                                                    // Role default (0 = deny if no entry)
                                                    $roleAllowed = (int) ($rolePermissions['menus'][$menu->id] ?? 0);

                                                    // User overrides are stored flat: [menu_id => is_allowed]
                                                    $hasUserOverride = array_key_exists($menu->id, $userMenuOverrides);
                                                    $userAllowed = $hasUserOverride ? (int) $userMenuOverrides[$menu->id] : null;

                                                    // Effective permission = user override OR role fallback
                                                    $effectiveAllowed = $hasUserOverride ? $userAllowed : $roleAllowed;

                                                    // Checked state
                                                    $checked = $effectiveAllowed ? 'checked' : '';
                                                    $parentAttr = $parentId ? 'data-parent="' . $parentId . '"' : '';

                                                    echo '<li class="mb-2">';
                                                    echo '  <div class="form-check">';
                                                    echo '      <input type="checkbox" name="permissions[' . $menu->id . ']" value="1"
                                                                                                       class="form-check-input permission-checkbox" ' . $checked . ' ' . $parentAttr . '>';
                                                    echo '      <label class="form-check-label">' . $menu->title . '</label>';

                                                    // --- Badge logic (same as routes) ---
                                                    if ($hasUserOverride) {
                                                        echo '  <span class="badge bg-warning ms-1">Overridden</span>';
                                                    } elseif ($roleAllowed) {
                                                        echo '  <span class="badge bg-info ms-1">Role</span>';
                                                    }
                                                    // else → no badge

                                                    echo '  </div>';

                                                    // Recurse into children
                                                    if ($menu->childrenRecursive && $menu->childrenRecursive->count()) {
                                                        echo '<ul class="ms-4 list-unstyled">';
                                                        renderUserPermissionTree($menu->childrenRecursive, $userMenuOverrides, $rolePermissions, $menu->id);
                                                        echo '</ul>';
                                                    }

                                                    echo '</li>';
                                                }
                                            }
                                        }
                                    @endphp

                                    @php renderUserPermissionTree($allMenus, $menuPermissions, $rolePermissions) @endphp
                                </ul>


                                <!-- Routes -->
                                @if($extraRoutes->count())
                                    <h5 class="mt-4">Route Permissions</h5>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach($extraRoutes as $menuId => $routes)
                                            @php $tabId = 'routesTab' . ($menuId ?? 'other'); @endphp
                                            <button class="btn btn-outline-primary btn-sm route-tab-btn" type="button"
                                                data-tab="{{ $tabId }}">{{ $routes->first()?->menu?->title ?? 'Other' }}</button>
                                        @endforeach
                                    </div>

                                    @foreach($extraRoutes as $menuId => $routes)
                                        @php $tabId = 'routesTab' . ($menuId ?? 'other'); @endphp
                                        <div class="route-tab-content mb-3" id="{{ $tabId }}" style="display: none;">
                                            <div class="card card-body">
                                                <h6 class="fw-bold">{{ $routes->first()?->menu?->title ?? 'Other' }} Routes</h6>
                                                <ul class="list-unstyled ms-3 mb-0">
                                                    @foreach($routes as $route)
                                                        @php
                                                            $roleAllowed = $rolePermissions['routes'][$route->id] ?? 0;
                                                            $userAllowed = $routePermissions[$route->id] ?? null;
                                                            $effectiveAllowed = $userAllowed !== null ? $userAllowed : $roleAllowed;
                                                            $checked = $effectiveAllowed ? 'checked' : '';
                                                        @endphp
                                                        <li class="mb-2">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="permissions_route[{{ $route->id }}]" value="1"
                                                                    class="form-check-input" {{ $checked }}>
                                                                <label class="form-check-label">
                                                                    {{ $route->name }}
                                                                    <small class="text-muted">({{ $route->method }}
                                                                        {{ $route->route_name }})</small>
                                                                </label>

                                                                @if($userAllowed !== null)
                                                                    <span class="badge bg-warning">Overridden</span>
                                                                @elseif($roleAllowed)
                                                                    <span class="badge bg-info">Role</span>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Permissions</button>
                                    <a href="{{ route('user-permissions.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">Search & select a user to manage permissions.</div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Cascade menu selection
                document.querySelectorAll(".permission-checkbox").forEach(cb => {
                    cb.addEventListener("change", function () {
                        const li = this.closest("li");
                        if (li) {
                            li.querySelectorAll("ul .permission-checkbox").forEach(child => {
                                child.checked = this.checked;
                            });
                        }
                        updateParents(this);
                    });
                });

                function updateParents(cb) {
                    const parentId = cb.dataset.parent;
                    if (!parentId) return;
                    const parent = document.querySelector('input[name="permissions[' + parentId + ']"]');
                    if (parent) {
                        const siblings = document.querySelectorAll('input[data-parent="' + parentId + '"]');
                        const allChecked = Array.from(siblings).every(sib => sib.checked);
                        const someChecked = Array.from(siblings).some(sib => sib.checked);
                        parent.checked = allChecked;
                        parent.indeterminate = !allChecked && someChecked;
                        updateParents(parent);
                    }
                }

                // Tabs for routes
                const buttons = document.querySelectorAll(".route-tab-btn");
                const contents = document.querySelectorAll(".route-tab-content");
                buttons.forEach(btn => {
                    btn.addEventListener("click", function () {
                        contents.forEach(c => c.style.display = 'none');
                        document.getElementById(this.dataset.tab).style.display = 'block';
                        buttons.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
                if (buttons.length) buttons[0].click();
            });
        </script>
    @endpush
@endsection