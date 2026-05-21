@extends('shared::layouts.app')

@section('content')
    <style>
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .profile-img img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
    </style>

    <main>
        <div>
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">User List</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">User List</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <a href="{{ route('users.create') }}" class="btn btn-primary me-2">
                        <i class="feather-plus me-2"></i> Add User
                    </a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        Filter
                    </a>
                </div>
            </div>
            <div id="collapseOne"
                class="accordion-collapse collapse page-header-collapse {{ request('search') || request('role_id') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <div>
                        <!-- 🔍 Filter Section -->
                        <form method="GET" action="{{ route('users.index') }}" class="mb-3 row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search Name, Email or Contact No." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-4">
                                <select name="role_id" class="form-select" data-select2-selector="tag">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 d-flex">
                                <button class="btn btn-primary me-2" type="submit">Filter</button>
                                <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <div class="crm-page-container">

        <!-- Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Country Code</th>
                            <th>Contact No</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="profile-img">
                                        @if($user->image)
                                            <img src="{{ asset('storage/' . $user->image) }}" alt="profile">
                                        @else
                                            <img src="/images/blank.jpeg" alt="default_Img" />
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role?->name ?? 'No Role' }}</td>
                                <td>{{ $user->country_code }}</td>
                                <td>{{ $user->contact_no }}</td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <form action="{{ route('users.userUpdateStatus', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <select name="is_active" class="form-select" data-select2-selector="status"
                                            onchange="this.form.submit()">
                                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="{{ route('users.edit', $user) }}" class="btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-delete"
                                                onclick="return confirm('Delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="m-4" style="display: flex; justify-content: center;">
                {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection