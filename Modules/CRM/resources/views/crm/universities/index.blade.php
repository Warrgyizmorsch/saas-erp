@extends('layouts.app')

@section('content')
    <style>
        .action-links {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-links .btn-edit,
        .action-links .btn-preview,
        .action-links .btn-delete {
            background: transparent;
            border: none;
            padding: 6px 8px;
            cursor: pointer;
        }

        .action-links .btn-edit i {
            color: #0d6efd;
        }

        .action-links .btn-preview i {
            color: #198754;
        }

        .action-links .btn-delete i {
            color: #dc3545;
        }

        .modal-backdrop.show {
            opacity: 0;
        }

        .modal-backdrop {
            position: static;
        }
    </style>

    <main>
        <div>
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">University Master Details</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">University Master</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto d-flex" style="gap:10px;">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseFilter">
                        Filter
                    </a>
                    <a href="{{ route('university-details.create') }}" class="btn btn-primary">
                        Add University
                    </a>
                </div>
            </div>

            <!-- Collapsible Filter -->
            <div id="collapseFilter"
                class="accordion-collapse collapse page-header-collapse {{ request('search') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <div>
                        <form method="GET" action="{{ route('university-details.index') }}" class="row g-2">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search by university name or country..."
                                    value="{{ request('search') }}">
                            </div>

                            <div class="col-md-4 d-flex">
                                <button class="btn btn-primary me-2" type="submit">Filter</button>
                                <a href="{{ route('university-details.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="crm-page-container">
        <!-- Table Card -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>University Name</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($universities as $university)
                            <tr>
                                <td>
                                    <strong>{{ $university->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $university->country ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if ($university->details)
                                        @if ($university->details->status === 'published')
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Not Started</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($university->details)
                                        {{ $university->details->updated_at->format('d M Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-links">
                                        <a href="{{ route('university-details.edit', $university->id) }}" class="btn-edit"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if ($university->details)
                                            <a href="{{ route('university-details.preview', $university->id) }}"
                                                class="btn-preview" title="Preview" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif

                                        <button type="button" class="btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $university->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $university->id }}" tabindex="-1"
                                            aria-labelledby="deleteLabel{{ $university->id }}" aria-hidden="true">
                                            <div class="modal-dialog"
                                                style="display: flex; justify-content: center; align-items:center; height: 100vh;">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteLabel{{ $university->id }}">
                                                            Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this university detail?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <form
                                                            action="{{ route('university-details.destroy', $university->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4">
                                    <p class="text-muted">No universities found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="m-4" style="display: flex; justify-content: center;">
                {{ $universities->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection