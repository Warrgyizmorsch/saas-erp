@extends('shared::layouts.app')

@section('content')
    <style>
        /* Reuse compact image style like User List */
        .thumb-img {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            overflow: hidden;
        }

        .thumb-img img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }

        /* Optional: align action icons like User List */
        .action-links {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-links .btn-edit,
        .action-links .btn-delete {
            background: transparent;
            border: none;
            padding: 6px 8px;
            cursor: pointer;
        }

        .action-links .btn-edit i {
            color: #0d6efd;
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
            <!-- Page Header (same skeleton as User List) -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Subject-Page List</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Subject-Page List</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto d-flex" style="gap:10px;">

                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        Filter
                    </a>
                    <a href="{{ route('crm-subject-pages.create') }}" class="btn btn-primary">Create</a>
                </div>
            </div>

            <!-- Collapsible Filter (mirrors User List filter UX) -->
            <div id="collapseOne"
                class="accordion-collapse collapse page-header-collapse {{ request('title') || request('type') || request('status') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <div>
                        <form method="GET" action="{{ url()->current() }}" class="mb-3 row g-2">
                            <div class="col-md-4">
                                <input type="text" name="title" class="form-control" placeholder="Search by Title"
                                    value="{{ request('title') }}">
                            </div>

                            <div class="col-md-4">
                                <select name="status" class="form-select" data-select2-selector="tag">
                                    <option value="">All Status</option>
                                    <option value="publish" {{ request('status') === 'publish' ? 'selected' : '' }}>
                                        Publish
                                    </option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>
                                        Draft
                                    </option>
                                </select>
                            </div>



                            <div class="col-md-4 d-flex">
                                <button class="btn btn-primary me-2" type="submit">Filter</button>
                                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="crm-page-container">
        <!-- Table Card (styled like User List) -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Url</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['SubjectPage'] as $subject)
                            <tr>
                                <td style="justify-items: center;">
                                    <div class="thumb-img">
                                        @if(!empty($subject->images))
                                            <img src="{{ asset($subject->images) }}" alt="thumb">
                                        @else
                                            <img src="/images/blank.jpeg" alt="default_thumb" />
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $subject->title }}</td>
                                <td>{{ $subject->slug }}</td>
                                <td>
                                    <span class="badge {{ $subject->status === 'publish' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($subject->status) }}
                                    </span>
                                </td>
                                <td>{{ $subject->created_at?->format('d M Y') }}</td>
                                <td>
                                    <div class="action-links">
                                        <a href="{{ route('crm-subject-pages.edit', ['id' => $subject->id]) }}" class="btn-edit"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Keep your delete modal flow intact (functionality unchanged) -->
                                        <button type="button" class="btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#delete{{ $subject->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <div class="modal fade" id="delete{{ $subject->id }}" tabindex="-1"
                                            aria-labelledby="deleteLabel{{ $subject->id }}" aria-hidden="true">
                                            <div class="modal-dialog"
                                                style="display: flex; justify-content: center; align-items:center; height: 100vh;">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteLabel{{ $subject->id }}">Confirm
                                                            Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this entry?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <form action="{{ route('crm-subject-pages.destroy', $subject->id) }}"
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
                                <td colspan="5">Subject Page Not Found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="m-4" style="display: flex; justify-content: center;">
                {{ $data['SubjectPage']->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection