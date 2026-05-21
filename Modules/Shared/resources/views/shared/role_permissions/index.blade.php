@extends('shared::layouts.app')

@section('content')
    <x-slot name="title">Role Permissions</x-slot>

    <div class="container">
        <h2 class="mb-4">Manage Role Permissions</h2>

        <div class="row">
            <!-- Roles List -->
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">Roles</div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        @foreach($roles as $role)
                            <a href="{{ route('role-permissions.index', ['role_id' => $role->id]) }}"
                                class="btn btn-outline-primary {{ $selectedRole && $selectedRole->id === $role->id ? 'active' : '' }}">
                                {{ $role->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Permissions Tree -->
            <div class="col-md-12">
                @if($selectedRole)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            Manage Permissions for: <strong>{{ $selectedRole->name }}</strong>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('role-permissions-id.update', $selectedRole->id) }}">
                                @csrf

                                @php
                                    function renderPermissionTree($menus, $menuPermissions, $parentId = null)
                                    {
                                        foreach ($menus as $menu) {
                                            $checked = isset($menuPermissions[$menu->id]) && $menuPermissions[$menu->id] ? 'checked' :
                                                '';
                                            $parentAttr = $parentId ? 'data-parent="' . $parentId . '"' : '';

                                            echo '<li class="mb-2">';
                                            echo '<div class="form-check">';
                                            echo '<input type="checkbox" name="permissions[' . $menu->id . ']" value="1"
                                                                                                                                        class="form-check-input permission-checkbox" ' . $checked . ' ' . $parentAttr . '>';
                                            echo '<label class="form-check-label">' . $menu->title . '</label>';
                                            echo '</div>';

                                            if ($menu->childrenRecursive && $menu->childrenRecursive->count()) {
                                                echo '<ul class="ms-4 list-unstyled">';
                                                renderPermissionTree($menu->childrenRecursive, $menuPermissions, $menu->id);
                                                echo '</ul>';
                                            }

                                            echo '</li>';
                                        }
                                    }
                                @endphp

                                <ul class="list-unstyled">
                                    @php renderPermissionTree($allMenus, $menuPermissions) @endphp
                                </ul>

                                @if($extraRoutes->count())
                                    <h5 class="mt-4">Route Permissions</h5>

                                    <!-- Menu buttons row -->
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach($extraRoutes as $menuId => $routes)
                                            @php
                                                $menuName = $routes->first()?->menu?->title ?? 'Other';
                                                $tabId = 'routesTab' . ($menuId ?? 'other');
                                            @endphp
                                            <button class="btn btn-outline-primary btn-sm route-tab-btn" type="button"
                                                data-tab="{{ $tabId }}">
                                                {{ $menuName }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Tab contents -->
                                    @foreach($extraRoutes as $menuId => $routes)
                                        @php
                                            $menuName = $routes->first()?->menu?->title ?? 'Other';
                                            $tabId = 'routesTab' . ($menuId ?? 'other');
                                        @endphp

                                        <div class="route-tab-content mb-3" id="{{ $tabId }}" style="display: none;">
                                            <div class="card card-body">
                                                <h6 class="fw-bold">{{ $menuName }} Routes</h6>
                                                <ul class="list-unstyled ms-3 mb-0">
                                                    @foreach($routes as $route)
                                                        @php
                                                            $checked = isset($routePermissions[$route->id]) && $routePermissions[$route->id] ? 'checked' : '';
                                                        @endphp
                                                        <li class="mb-2">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="permissions_route[{{ $route->id }}]" value="1"
                                                                    class="form-check-input" {{ $checked }}>
                                                                <label class="form-check-label">
                                                                    {{ $route->name }} <small class="text-muted">({{ $route->method }}
                                                                        {{ $route->route_name }})</small>
                                                                </label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach

                                    @push('scripts')
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function () {
                                                const buttons = document.querySelectorAll(".route-tab-btn");
                                                const contents = document.querySelectorAll(".route-tab-content");

                                                buttons.forEach(btn => {
                                                    btn.addEventListener("click", function () {
                                                        const targetId = this.dataset.tab;

                                                        // Hide all tab contents
                                                        contents.forEach(c => c.style.display = 'none');

                                                        // Show the selected tab
                                                        document.getElementById(targetId).style.display = 'block';

                                                        // Optional: highlight the active button
                                                        buttons.forEach(b => b.classList.remove('active'));
                                                        this.classList.add('active');
                                                    });
                                                });

                                                // Show first tab by default
                                                if (buttons.length) buttons[0].click();
                                            });
                                        </script>
                                    @endpush
                                @endif


                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Permissions</button>
                                    <a href="{{ route('role-permissions.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        Select a role to manage permissions.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".permission-checkbox").forEach(checkbox => {
                    checkbox.addEventListener("change", function () {
                        const isChecked = this.checked;
                        // --- Handle children (downwards) ---
                        const li = this.closest("li");
                        if (li) {
                            const childCheckboxes = li.querySelectorAll("ul .permission-checkbox");
                            childCheckboxes.forEach(child => {
                                child.checked = isChecked;
                            });
                        }
                        // --- Handle parent (upwards recursively) ---
                        updateParents(this);
                    });
                });

                function updateParents(childCheckbox) {
                    const parentId = childCheckbox.dataset.parent;
                    if (!parentId) return; // no parent, stop recursion
                    const parent = document.querySelector('input[name="permissions[' + parentId + ']"]');
                    if (parent) {
                        const siblings = document.querySelectorAll('input[data-parent="' + parentId + '"]');
                        const allChecked = Array.from(siblings).every(sib => sib.checked);
                        const someChecked = Array.from(siblings).some(sib => sib.checked);
                        // Full checked or unchecked parent
                        parent.checked = allChecked;
                        // --- Optional: add indeterminate state ---
                        parent.indeterminate = !allChecked && someChecked;
                        // Recursively check its parent
                        updateParents(parent);
                    }
                }
            });
        </script>

    @endpush
@endsection