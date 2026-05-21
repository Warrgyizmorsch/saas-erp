@extends('shared::layouts.app')

@section('content')
    <x-slot name="title">Routes Management</x-slot>

    <main>
        <div>
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manage Routes</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Routes</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        Filter
                    </a>
                </div>
            </div>
            <div id="collapseOne"
                class="accordion-collapse collapse page-header-collapse {{ request('name') || request('route_name') || request('method') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <div>
                        {{-- Filter Form --}}
                        <form method="GET" action="{{ route('routes.index') }}" class="mb-3 row g-2">
                            <div class="col-md-3">
                                <input type="text" name="name" class="form-control" placeholder="Filter by Name"
                                    value="{{ request('name') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="route_name" class="form-control" placeholder="Filter by Route"
                                    value="{{ request('route_name') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="method" class="form-select" data-select2-selector="tag">
                                    <option value="">Filter by Method</option>
                                    <option value="GET" {{ request('method') == 'GET' ? 'selected' : '' }}>GET</option>
                                    <option value="POST" {{ request('method') == 'POST' ? 'selected' : '' }}>POST</option>
                                    <option value="PUT" {{ request('method') == 'PUT' ? 'selected' : '' }}>PUT</option>
                                    <option value="DELETE" {{ request('method') == 'DELETE' ? 'selected' : '' }}>DELETE
                                    </option>
                                    <option value="PATCH" {{ request('method') == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="menu_id" class="form-select" data-select2-selector="tag">
                                    <option value="">Filter by Menu</option>
                                    @foreach($flattenMenus as $menu)
                                        <option value="{{ $menu->id }}" {{ request('menu_id') == $menu->id ? 'selected' : '' }}>
                                            {{ $menu->display_title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-3 d-flex">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('routes.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="crm-page-container">
        {{-- Add / Edit Route Form --}}
        <div class="card mb-4">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">{{ isset($editRoute) ? 'Edit Route' : 'Add New Route' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST"
                    action="{{ isset($editRoute) ? route('routes.update', $editRoute->id) : route('routes.store') }}">
                    @csrf
                    @if(isset($editRoute))
                        @method('PUT')
                    @endif

                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="name" class="form-control" placeholder="Route Name"
                                value="{{ old('name', $editRoute->name ?? '') }}" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="route_name" class="form-control" placeholder="routes.index"
                                value="{{ old('route_name', $editRoute->route_name ?? '') }}" required>
                        </div>
                        <div class="col-md-3">
                            <select name="method" class="form-select" data-select2-selector="tag" required>
                                <option value="GET" {{ old('method', $editRoute->method ?? '') == 'GET' ? 'selected' : '' }}>
                                    GET</option>
                                <option value="POST" {{ old('method', $editRoute->method ?? '') == 'POST' ? 'selected' : '' }}>POST</option>
                                <option value="PUT" {{ old('method', $editRoute->method ?? '') == 'PUT' ? 'selected' : '' }}>
                                    PUT</option>
                                <option value="DELETE" {{ old('method', $editRoute->method ?? '') == 'DELETE' ? 'selected' : '' }}>DELETE</option>
                                <option value="PATCH" {{ old('method', $editRoute->method ?? '') == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="menu_id" class="form-select" data-select2-selector="tag">
                                <option value="">Select Menu</option>
                                @foreach($flattenMenus as $menu)
                                    <option value="{{ $menu->id }}" {{ old('menu_id', $editRoute->menu_id ?? '') == $menu->id ? 'selected' : '' }}>
                                        {{ $menu->display_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-success w-100">
                                {{ isset($editRoute) ? 'Update' : 'Add' }}
                            </button>
                            @if(isset($editRoute))
                                <a href="{{ route('routes.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Routes Table --}}
        <div class="card">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">Routes List</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sr.No.</th>
                            <th>Name</th>
                            <th>Route</th>
                            <th>Method</th>
                            <th>Menu</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                            <tr>
                                <td>{{ $routes->firstItem() + $loop->index }}</td>
                                <td>{{ $route->name }}</td>
                                <td>{{ $route->route_name }}</td>
                                <td><span class="badge bg-info">{{ $route->method }}</span></td>

                                <td>{{ optional($route->menu)->title ?? '-' }}</td>

                                <td>
                                    <div class="action-links">
                                        {{-- Edit --}}
                                        <a href="{{ route('routes.edit', $route->id) }}" class="btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('routes.destroy', $route->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete" title="Delete"
                                                onclick="return confirm('Delete this route?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No routes found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="m-3">
                    {{ $routes->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>
@endsection