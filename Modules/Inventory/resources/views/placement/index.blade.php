@extends('shared::layouts.app')
@section('content')

      <div class="page-header d-flex justify-content-between align-items-center ">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ isset($placement) ? 'Edit placement' : 'Add placement' }}
                </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">
                    {{ isset($placement) ? 'Edit Placement' : 'Add placement' }}
                </li>
            </ul>
        </div>

        <div class="d-flex gap-2">
            @if(!isset($placement))
            <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                <i class="feather-plus me-2"></i> Add Placement
            </button>
            @else
            <a href="{{ route('placement.index') }}" class="btn btn-primary btn-sm">
                Add New
            </a>
            @endif
        </div>
    </div>


    <div class="main-content">

        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ isset($placement) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"> {{ isset($placement) ? 'Edit Placement' : 'Add New Placement' }}</h5>
                    <form
                        action="{{ isset($placement) ? route('placement.update',$placement->id) : route('placement.store') }}"
                        method="POST">
                        @csrf
                        @if(isset($placement))
                        @method('PUT')
                        @endif

                        <div class="row">

                            <div class="col-lg-4 mb-3">
                                <label class="form-label">Placement Name</label>
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-user"></i></div>
                                    <input type="text" placeholder="Enter Placement Name" class="form-control" name="name" value="{{ old('name', $placement->name ?? '' )}}">
                                </div>

                                @if ($errors->has('name'))
                                <div class="text-danger mt-1 small">
                                    {{ $errors->first('name') }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <button class="btn btn-primary">
                            {{ isset($placement) ? 'Update placement' : 'Save placement' }}
                        </button>

                    </form>
                </div>
            </div>
        </div>


        <div class="card stretch stretch-full">
            <div class="card-body ">
                @php
                    $splitPlacements = $placements->split(2);
                    $globalIndex = 0;
                @endphp
                
                <div class="row">
                    @foreach($splitPlacements as $chunk)
                    <div class="col-md-6 {{ $loop->first && $splitPlacements->count() > 1 ? 'border-end' : '' }} px-4">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($chunk as $p)
                                    @php $globalIndex++; @endphp
                                    <tr>
                                        <td>{{ $globalIndex }}</td>
                                        <td>{{$p->name}}</td>
                                        <td class="text-end">
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('placement.edit', $p->id) }}" class="avatar-text avatar-md">
                                                    <i class="feather feather-edit-3"></i>
                                                </a>
                                                <button class="avatar-text avatar-md" data-bs-toggle="offcanvas" data-bs-target="#deletePlacement{{ $p->id }}">
                                                    <i class="feather feather-trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- DELETE OFFCANVAS -->
                                    <div class="offcanvas offcanvas-end" tabindex="-1" id="deletePlacement{{ $p->id }}">
                                        <form method="POST" action="{{ route('placement.destroy', $p->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <div class="offcanvas-header border-bottom">
                                                <h5 class="mb-0">Delete Placement</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                                            </div>
                                            <div class="offcanvas-body">
                                                <p>Are you sure you want to delete <strong>{{ $p->name }}</strong>?</p>
                                            </div>
                                            <div class="border-top p-3 d-flex gap-2">
                                                <button type="submit" class="btn btn-primary w-50">Yes, Delete</button>
                                                <button type="button" class="btn btn-danger w-50" data-bs-dismiss="offcanvas">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection
