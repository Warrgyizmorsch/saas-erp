@extends('layouts.app')

@section('content')

<style>
    li.hover-item:hover {
        background-color: #9CA3AF;
        color: white;
        cursor: pointer;
    }

    .btn {
        border-radius: 8px;
        font-size: 13px;
        padding: 6px 12px;
    }
</style>

<main>
    <div>
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Manage Buckets</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item">Buckets</li>
                </ul>
            </div>
        </div>
        
    </div>
</main>

    <div class="crm-page-container">
        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Bucket Form (Add/Edit) --}}
        <div class="card mb-4 rounded-3">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">
                    {{ $editBucket ? 'Edit Bucket' : 'Add New Bucket' }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ $editBucket ? route('bucket.update', $editBucket->id) : route('bucket.store') }}" 
                    method="POST" class="">
                    @csrf
                    @if($editBucket)
                        @method('PUT')
                    @endif

                    <div class="row g-2 align-items-center">
                        {{-- Bucket Name --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1 text-primary"></i> Bucket Name
                            </label>
                            <input type="text" name="name" value="{{ $editBucket->name ?? '' }}" class="form-control"
                                placeholder="Enter bucket name" required>
                        </div>
                        
                        {{-- Parent Bucket --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-sitemap me-1 text-primary"></i> Parent Bucket
                            </label>
                            <select name="parent_id" class="form-control" data-select2-selector="tag">
                                <option value="">None (Root Bucket)</option>
                                @foreach($allBuckets as $bucketOption)
                                    @if(is_null($bucketOption->parent_id))
                                        <option value="{{ $bucketOption->id }}" @if($editBucket && $editBucket->parent_id == $bucketOption->id) selected @endif>
                                            {{ $bucketOption->name }}
                                        </option>
                                    @endif
                                @endforeach

                            </select>
                        </div>

                        {{-- Bucket Color --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-palette me-1 text-primary"></i> Bucket Color
                            </label>
                            <select name="bucket_color" class="form-control" data-select2-selector="tag">
                                <option value="">Select Color</option>
                                <option value="bg-primary" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-primary') ? 'selected' : '' }}>
                                    Blue
                                </option>
                                <option value="bg-success" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-success') ? 'selected' : '' }}>
                                    Green
                                </option>
                                <option value="bg-danger" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-danger') ? 'selected' : '' }}>
                                    Red
                                </option>
                                <option value="bg-warning" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-warning') ? 'selected' : '' }}>
                                    Yellow
                                </option>
                                <option value="bg-info" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-info') ? 'selected' : '' }}>
                                    Teal
                                </option>
                                <option value="bg-secondary" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-secondary') ? 'selected' : '' }}>
                                    Gray
                                </option>
                                <option value="bg-dark" {{ (isset($editBucket) && $editBucket->bucket_color == 'bg-dark') ? 'selected' : '' }}>
                                    Black
                                </option>
                            </select>
                        </div>

                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-md-2 d-flex align-items-end mt-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-{{ $editBucket ? 'warning' : 'success' }} shadow-sm">
                                {{ $editBucket ? 'Update' : 'Add' }}
                            </button>
                            @if($editBucket)
                                <a href="{{ route('bucket.index') }}" class="btn btn-secondary shadow-sm">
                                    Cancel
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- Bucket List --}}

        <div class="card mb-4 rounded-3">
            <div class="card-header text-white d-flex align-items-center">
                <h5 class="mb-0 fw-bold">Bucket List</h5>
            </div>
            <div class="card-body">
        
                <ul class="list-unstyled">
                    @foreach ($buckets as $bucket)
                        <li class="mb-2">
                            <div class="d-flex align-items-center gap-2 border-bottom">
                                <strong>
                                    @if($bucket->bucket_color)
                                        <span class="{{ $bucket->bucket_color }}" style="display: inline-block; height: 10px; width: 10px; border-radius: 50%;">
                                        </span>
                                    @endif
                                    {{ $bucket->name }}
                                </strong>
                                <div class="ms-auto d-flex gap-1">
                                    <a href="{{ route('bucket.edit', $bucket->id) }}" class="btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('bucket.destroy', $bucket->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete" onclick="return confirm('Delete this bucket?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Show Children --}}
                            @if ($bucket->children->count() > 0)
                                <ul class="list-unstyled ps-4 mt-1">
                                    @foreach ($bucket->children as $child)
                                        <li class="mb-2">
                                            <div class="d-flex align-items-center gap-2 border-bottom">
                                                <strong>
                                                    @if($child->bucket_color)
                                                    <span class="{{ $child->bucket_color }}" style="display: inline-block; height: 10px; width: 10px; border-radius: 50%;">
                                                    </span>
                                                    @endif
                                                    {{ $child->name }}
                                                </strong>
                                                <div class="ms-auto d-flex gap-1">
                                                    <a href="{{ route('bucket.edit', $child->id) }}" class="btn-edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('bucket.destroy', $child->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn-delete"
                                                            onclick="return confirm('Delete this bucket?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
        
            </div>
        </div>
     
    </div>
@endsection
