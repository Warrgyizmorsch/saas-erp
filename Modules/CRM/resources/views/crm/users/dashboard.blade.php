@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $title ?? 'Dashboard' }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item">{{ $breadcrumb ?? 'Analytics' }}</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">

                {{-- Mobile Back Button --}}
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    {{-- Date Range Picker --}}
                    <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                        <span class="reportrange-picker-field">{{ $dateRange ?? '' }}</span>
                    </div>

                    {{-- Filter Dropdown --}}
                    <div class="dropdown filter-dropdown">
                        <a class="btn btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10"
                            data-bs-auto-close="outside">
                            <i class="feather-filter me-2"></i>
                            <span>Filter</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            @foreach($filters ?? ['Role', 'Team', 'Email', 'Member', 'Recommendation'] as $filter)
                                <div class="dropdown-item">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="{{ $filter }}" checked>
                                        <label class="custom-control-label c-pointer"
                                            for="{{ $filter }}">{{ $filter }}</label>
                                    </div>
                                </div>
                            @endforeach

                            <div class="dropdown-divider"></div>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-plus me-3"></i>
                                <span>Create New</span>
                            </a>
                            <a href="javascript:void(0);" class="dropdown-item">
                                <i class="feather-filter me-3"></i>
                                <span>Manage Filter</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Mobile Filter Toggle --}}
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="main-content" style="min-height: 100vh;">
        <div class="row">
            <div class="container text-center mt-5">
                <h1>Welcome, {{ Auth::user()->name }} 👋</h1>
                <p>You are logged in as a basic user.</p>
            </div>
        </div>
    </div>
@endsection