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
                        <h5 class="m-b-10">Blog List</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Blog List</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto d-flex" style="gap:10px;">
                    <a href="/crm-blog?site=warrgyizmorsch" class="btn btn-secondary">Warr Blogs</a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        Filter
                    </a>
                    <a href="{{ route('blog.create') }}" class="btn btn-primary">Create</a>
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

                            <div class="col-md-4">
                                <select name="site" class="form-select" data-select2-selector="tag">
                                    <option value="">WTS Blogs</option>
                                    <option value="warrgyizmorsch" {{ request('site') === 'warrgyizmorsch' ? 'selected' : '' }}>
                                        Warrgyizmorsch Blogs
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

    <div class="crm-page-container">
        <!-- Table Card (styled like User List) -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['blog'] as $blog)
                            <tr>
                                <td>
                                    <div class="thumb-img">
                                        @if(!empty($blog->images))
                                            <img src="{{ asset($blog->images) }}" alt="thumb">
                                        @else
                                            <img src="/images/blank.jpeg" alt="default_thumb" />
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $blog->title }}</td>
                                <td>
                                    <span class="badge {{ $blog->status === 'publish' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($blog->status) }}
                                    </span>
                                </td>
                                <td>{{ $blog->created_at?->format('d M Y') }}</td>
                                <td>
                                    <div class="action-links">
                                        <a href="{{ route('blog.edit', ['id' => $blog->id]) }}" class="btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Keep your delete modal flow intact (functionality unchanged) -->
                                        <button type="button" class="btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#kt_modal_create_delete{{ $blog->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <div class="modal fade" id="kt_modal_create_delete{{ $blog->id }}" tabindex="-1"
                                            aria-labelledby="deleteLabel{{ $blog->id }}" aria-hidden="true">
                                            <div class="modal-dialog"
                                                style="display: flex; justify-content: center; align-items:center; height: 100vh;">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteLabel{{ $blog->id }}">Confirm
                                                            Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this blog entry?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <form action="{{ route('blog.destroy', $blog->id) }}" method="POST">
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
                                <td colspan="5">No blogs found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="m-4" style="display: flex; justify-content: center;">
                {{ $data['blog']->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection