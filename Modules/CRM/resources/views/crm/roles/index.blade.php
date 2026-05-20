@extends('layouts.app')

@section('content')

<x-slot name="title">Roles Management</x-slot>

<main>
    <div>
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Manage Roles</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item">Roles</li>
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
            class="accordion-collapse collapse page-header-collapse {{ request('name') ? 'show' : '' }}">
            <div class="accordion-body pb-2">
                <div>
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('roles.index') }}" class="mb-3 row g-2">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control" placeholder="Filter by Name" value="{{ request('name') }}">
                        </div>
                        <div class="col-md-3 d-flex">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</main>

<div class="crm-page-container">

    {{-- Add / Edit Role Form --}}
    <div class="card mb-4">

        <div class="card-header text-white d-flex align-items-center">
            <h5 class="mb-0 fw-bold">{{ isset($editRole) ? 'Edit Role' : 'Add New Role' }}</h5>
        </div>

        <div class="card-body">
            <form method="POST" 
                  action="{{ isset($editRole) ? route('roles.update', $editRole->id) : route('roles.store') }}">
                @csrf
                @if(isset($editRole))
                    @method('PUT')
                @endif

                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="name" 
                               class="form-control" 
                               placeholder="Role Name" 
                               value="{{ old('name', $editRole->name ?? '') }}" required>
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-success w-100">
                            {{ isset($editRole) ? 'Update' : 'Add' }}
                        </button>
                        @if(isset($editRole))
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Roles Table --}}
    <div class="card">
        <div class="card-header text-white d-flex align-items-center">
            <h5 class="mb-0 fw-bold">Roles List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="max-width: 20px;">Sr. No.</th>
                        <th>Role Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>{{ $roles->firstItem() + $loop->index }}</td>
                            <td>{{ $role->name }}</td>
                            <td>
                                <div class="action-links">
                                    {{-- Edit --}}
                                <a href="{{ route('roles.edit', $role->id) }}" 
                                class="btn-edit" 
                                title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Delete --}}
                                <form method="POST" 
                                    action="{{ route('roles.destroy', $role->id) }}" 
                                    class="d-inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn-delete" 
                                            title="Delete" 
                                            onclick="return confirm('Delete this role?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No roles found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-2">
                {{ $roles->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
