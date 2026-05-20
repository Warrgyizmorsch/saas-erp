@extends('layouts.app')

@section('content')
    <style>
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

        .badge-pill {
            border-radius: 999px;
            padding: .45rem .65rem;
        }

        .slug-small {
            font-size: 12px;
            color: var(--bs-secondary-color);
        }
    </style>

    <main>
        <div>
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Service Pages</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Service Pages</li>
                    </ul>
                </div>

                <div class="page-header-right ms-auto d-flex" style="gap:10px;">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseFilter">
                        Filter
                    </a>

                    <a href="{{ route('warr-service-pages.create') }}" class="btn btn-primary">Create</a>
                </div>
            </div>

            <div id="collapseFilter"
                class="accordion-collapse collapse page-header-collapse {{ request('status') || request('service_id') || request('country_id') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <form method="GET" action="{{ url()->current() }}" class="mb-3 row g-2">

                        <div class="col-md-4">
                            <select name="status" class="form-select" data-select2-selector="tag">
                                <option value="">All Status</option>
                                <option value="publish" {{ request('status') === 'publish' ? 'selected' : '' }}>Publish</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <select name="service_id" class="form-select" data-select2-selector="tag">
                                <option value="">All Services</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" {{ (string) request('service_id') === (string) $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <select name="country_id" class="form-select" data-select2-selector="tag">
                                <option value="">All Countries</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}" {{ (string) request('country_id') === (string) $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 d-flex">
                            <button class="btn btn-primary me-2" type="submit">Filter</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </main>

    <div class="crm-page-container">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hero Title</th>
                            <th>Slug</th>
                            <th>Main Service</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['pages'] as $page)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $page->hero_title }}</div>
                                </td>
                                <td>
                                    <div class="slug-small">/{{ $page->slug }}</div>
                                </td>
                                <td>{{ $page->service?->name ?? '-' }}</td>
                                <td>
                                    <div>
                                        {{ $page->country?->name ?? '-' }}
                                        @if($page->city)
                                            <span class="text-muted">•</span> {{ $page->city->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-pill {{ $page->status === 'publish' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($page->status) }}
                                    </span>
                                </td>
                                <td>{{ $page->created_at?->format('d M Y') }}</td>

                                <td>
                                    <div class="action-links">
                                        <a href="{{ route('warr-service-pages.edit', ['id' => $page->id]) }}"
                                            class="btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <button type="button" class="btn-delete" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal{{ $page->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <a href="{{ route('warr-service-pages.create', ['clone_id' => $page->id]) }}" title="Clone">
                                            <i class="fas fa-copy"></i>
                                        </a>

                                        <div class="modal fade" id="deleteModal{{ $page->id }}" tabindex="-1"
                                            aria-labelledby="deleteLabel{{ $page->id }}" aria-hidden="true">
                                            <div class="modal-dialog"
                                                style="display: flex; justify-content: center; align-items:center; height: 100vh;">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteLabel{{ $page->id }}">
                                                            Confirm Delete
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete this service page?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>

                                                        <form action="{{ route('warr-service-pages.delete', $page->id) }}"
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
                                <td colspan="7">No Service Pages found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="m-4" style="display:flex;justify-content:center;">
                {{ $data['pages']->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
