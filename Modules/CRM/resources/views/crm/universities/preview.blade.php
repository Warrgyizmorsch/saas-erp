@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <!-- Header with Back Button -->
                <div class="mb-4">
                    <a href="{{ route('university-details.index') }}" class="btn btn-secondary mb-3">
                        <i class="fas fa-arrow-left"></i> Back to Universities
                    </a>
                    <h2>{{ $university->name }} - Preview</h2>
                    <p class="text-muted">How this will appear on the frontend</p>
                </div>

                @if (!$detail || $detail->status !== 'published')
                    <div class="alert alert-warning">
                        <i class="fas fa-alert-circle"></i> This university detail is not published yet!
                    </div>
                @endif

                <!-- University Information Card -->
                <div class="card shadow-sm mb-4 overflow-hidden">
                    @if ($detail->banner_image)
                        <img src="{{ asset('storage/' . $detail->banner_image) }}" class="card-img-top" alt="Banner"
                            style="height: 300px; object-fit: cover;">
                    @endif
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3>{{ $university->name }}</h3>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $university->city }}, {{ $university->country }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                @if ($detail->thumbnail_image)
                                    <img src="{{ asset('storage/' . $detail->thumbnail_image) }}" alt="Logo"
                                        style="max-height: 80px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Statistics -->
                @if ($detail->global_ranking || $detail->country_ranking || $detail->batch_strength || $detail->global_diversity)
                    <div class="row mb-4">
                        @if ($detail->global_ranking)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Global Ranking</h5>
                                        <h3 class="text-primary">{{ $detail->global_ranking }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($detail->country_ranking)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Country Ranking</h5>
                                        <h3 class="text-info">{{ $detail->country_ranking }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($detail->batch_strength)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Batch Strength</h5>
                                        <h3 class="text-success">{{ number_format($detail->batch_strength) }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($detail->global_diversity)
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-center shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Global Diversity</h5>
                                        <h3 class="text-warning">{{ $detail->global_diversity }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Cost Information -->
                @if ($detail->cost_of_living || $detail->tuition_fee_from)
                    <div class="row mb-4">
                        @if ($detail->cost_of_living)
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Cost of Living</h5>
                                        <h4>{{ $detail->currency_code ?? 'GBP' }} {{ number_format($detail->cost_of_living, 2) }}
                                        </h4>
                                        <small class="text-muted">Annual</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($detail->tuition_fee_from)
                            <div class="col-md-6 mb-3">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">Tuition Fee From</h5>
                                        <h4>{{ $detail->currency_code ?? 'GBP' }} {{ number_format($detail->tuition_fee_from, 2) }}
                                        </h4>
                                        <small class="text-muted">Starting price</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tabs Section -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            @if ($detail->overview)
                                <li class="nav-item">
                                    <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview-content"
                                        role="tab">
                                        <i class="fas fa-info-circle"></i> Overview
                                    </a>
                                </li>
                            @endif
                            @if ($detail->global_ranking || $detail->country_ranking)
                                <li class="nav-item">
                                    <a class="nav-link" id="ranking-tab" data-bs-toggle="tab" href="#ranking-content"
                                        role="tab">
                                        <i class="fas fa-chart-bar"></i> Ranking
                                    </a>
                                </li>
                            @endif
                            @if ($detail->admission_requirements)
                                <li class="nav-item">
                                    <a class="nav-link" id="admission-tab" data-bs-toggle="tab" href="#admission-content"
                                        role="tab">
                                        <i class="fas fa-clipboard-list"></i> Admission
                                    </a>
                                </li>
                            @endif
                            @if ($detail->scholarship_info)
                                <li class="nav-item">
                                    <a class="nav-link" id="scholarship-tab" data-bs-toggle="tab" href="#scholarship-content"
                                        role="tab">
                                        <i class="fas fa-graduation-cap"></i> Scholarship
                                    </a>
                                </li>
                            @endif
                            @if ($detail->finances_info)
                                <li class="nav-item">
                                    <a class="nav-link" id="finances-tab" data-bs-toggle="tab" href="#finances-content"
                                        role="tab">
                                        <i class="fas fa-wallet"></i> Finances
                                    </a>
                                </li>
                            @endif
                            @if ($detail->accommodation_info)
                                <li class="nav-item">
                                    <a class="nav-link" id="accommodation-tab" data-bs-toggle="tab"
                                        href="#accommodation-content" role="tab">
                                        <i class="fas fa-home"></i> Accommodation
                                    </a>
                                </li>
                            @endif
                            @if ($detail->faq_content)
                                <li class="nav-item">
                                    <a class="nav-link" id="faq-tab" data-bs-toggle="tab" href="#faq-content" role="tab">
                                        <i class="fas fa-question-circle"></i> FAQs
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            @if ($detail->overview)
                                <div class="tab-pane fade show active" id="overview-content" role="tabpanel">
                                    <h5>Overview</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->overview)) !!}
                                    </div>
                                </div>
                            @endif
                            @if ($detail->global_ranking || $detail->country_ranking)
                                <div class="tab-pane fade" id="ranking-content" role="tabpanel">
                                    <h5>Ranking & Statistics</h5>
                                    <div class="row">
                                        @if ($detail->global_ranking)
                                            <div class="col-md-6">
                                                <p><strong>Global Ranking:</strong> {{ $detail->global_ranking }}</p>
                                            </div>
                                        @endif
                                        @if ($detail->country_ranking)
                                            <div class="col-md-6">
                                                <p><strong>Country Ranking:</strong> {{ $detail->country_ranking }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if ($detail->admission_requirements)
                                <div class="tab-pane fade" id="admission-content" role="tabpanel">
                                    <h5>Admission Requirements</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->admission_requirements)) !!}
                                    </div>
                                    @if ($detail->entry_requirements_url)
                                        <p class="mt-3">
                                            <a href="{{ $detail->entry_requirements_url }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                View Full Requirements <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            @endif
                            @if ($detail->scholarship_info)
                                <div class="tab-pane fade" id="scholarship-content" role="tabpanel">
                                    <h5>Scholarship Information</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->scholarship_info)) !!}
                                    </div>
                                    @if ($detail->scholarship_url)
                                        <p class="mt-3">
                                            <a href="{{ $detail->scholarship_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                Apply for Scholarship <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            @endif
                            @if ($detail->finances_info)
                                <div class="tab-pane fade" id="finances-content" role="tabpanel">
                                    <h5>Finance Information</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->finances_info)) !!}
                                    </div>
                                    @if ($detail->finances_url)
                                        <p class="mt-3">
                                            <a href="{{ $detail->finances_url }}" target="_blank" class="btn btn-sm btn-primary">
                                                Learn More <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            @endif
                            @if ($detail->accommodation_info)
                                <div class="tab-pane fade" id="accommodation-content" role="tabpanel">
                                    <h5>Accommodation</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->accommodation_info)) !!}
                                    </div>
                                    @if ($detail->accommodation_url)
                                        <p class="mt-3">
                                            <a href="{{ $detail->accommodation_url }}" target="_blank"
                                                class="btn btn-sm btn-primary">
                                                Find Accommodation <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            @endif
                            @if ($detail->faq_content)
                                <div class="tab-pane fade" id="faq-content" role="tabpanel">
                                    <h5>Frequently Asked Questions</h5>
                                    <div class="content-section">
                                        {!! nl2br(e($detail->faq_content)) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .content-section {
            line-height: 1.8;
            font-size: 1rem;
            color: #333;
        }

        .nav-tabs .nav-link {
            border: none;
            border-top: 3px solid transparent;
            color: #666;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-top-color: #007bff;
            color: #007bff;
        }

        .nav-tabs .nav-link.active {
            border-top-color: #007bff;
            color: #007bff;
            background-color: transparent;
            border-bottom: none;
        }
    </style>
@endsection