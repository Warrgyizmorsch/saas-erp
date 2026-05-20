@extends('layouts.app')

@section('content')
    <style>
        .image-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--bs-body-bg);
            border: 2px dashed var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .author-table img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>

    <main class="p-4">
        {{-- Page Header --}}
        <div class="page-header mb-4">
            <div class="page-header-title">
                <h5 class="m-b-10">Manage Blog Authors</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item">Blog Authors</li>
            </ul>
        </div>

        <div class="row g-4">
            {{-- Left Side: Create/Edit Form --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary">{{ isset($editAuthor) ? 'Update Author' : 'Add New Author' }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('author.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="author_id" value="{{ $editAuthor->id ?? '' }}">

                            {{-- Photo Section --}}
                            <div class="text-center">
                                <div class="image-preview" id="thumbPreview">
                                    <img src="{{ isset($editAuthor) && $editAuthor->photo ? asset('storage/' . $editAuthor->photo) : '/images/blank.jpeg' }}" id="previewImg">
                                </div>
                                <div class="mb-3">
                                    <label for="thumbInput" class="form-label btn btn-sm btn-light-primary border">
                                        Choose Photo
                                    </label>
                                    <input type="file" name="photo" id="thumbInput" hidden accept="image/*">
                                    <div class="small text-muted">JPG, PNG (Max 2MB)</div>
                                </div>
                            </div>

                            {{-- Inputs --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter author name" value="{{ $editAuthor->name ?? '' }}" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase">Short Bio/Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Brief about author...">{{ $editAuthor->description ?? '' }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> {{ isset($editAuthor) ? 'Update Author' : 'Save Author' }}
                                </button>
                                @if(isset($editAuthor))
                                    <a href="{{ route('author.index') }}" class="btn btn-light">Cancel Edit</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right Side: Author List --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Author Directory</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 author-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Author</th>
                                        <th>Description</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($authors as $author)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $author->photo ? asset('storage/' . $author->photo) : "/images/blank.jpeg" }}" alt="author">
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $author->name }}</div>
                                                    <div class="small text-muted">ID: #{{ $author->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted small" style="max-width: 300px; white-space: normal;">
                                                {{ Str::limit($author->description, 100) }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group gap-2">
                                                <a href="{{ route('author.edit', $author->id) }}" class="btn-edit" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('author.destroy', $author->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this author?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">No authors found in database.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Smooth Image Preview
        document.getElementById('thumbInput').onchange = function (evt) {
            const [file] = this.files;
            if (file) {
                document.getElementById('previewImg').src = URL.createObjectURL(file);
            }
        }
    </script>
@endsection