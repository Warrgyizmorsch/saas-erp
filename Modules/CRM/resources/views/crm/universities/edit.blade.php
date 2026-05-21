@extends('shared::layouts.app')

@section('content')
    <style>
        .image-preview {
            width: 200px;
            height: 150px;
            border-radius: .5rem;
            overflow: hidden;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .note-editor .note-editable {
            min-height: 300px;
        }

        /* FAQ card look */
        .faq-entry {
            border: 1px dashed var(--bs-border-color);
            border-radius: .75rem;
            padding: 1rem;
            background: var(--bs-body-bg);
        }
    </style>

    <main>
        <div>
            <!-- Page Header (mirrors Blog module) -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">
                            {{ $university->id ? $university->name . ' - Edit Details' : 'Create University' }}
                        </h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('university-details.index') }}">Universities</a>
                        </li>
                        <li class="breadcrumb-item">Edit</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto d-flex" style="gap:10px;">
                    <a href="{{ route('university-details.index') }}" class="btn btn-secondary">Back to Universities</a>
                </div>
            </div>
        </div>
    </main>

    <div class="crm-page-container">
        <div class="card">
            <div class="card-body">
                <!-- Form -->
                <form id="universityForm" action="{{ $university->id
        ? route('university-details.store', $university->id)
        : route('university-details.store-new') 
        }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4" id="universityTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab"
                                data-bs-target="#overview" type="button">Overview</button>
                        </li>
                        @if($university->id)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="ranking-tab" data-bs-toggle="tab" data-bs-target="#ranking"
                                    type="button">Ranking & Stats</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="costs-tab" data-bs-toggle="tab" data-bs-target="#costs"
                                    type="button">Costs</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="admission-tab" data-bs-toggle="tab" data-bs-target="#admission"
                                    type="button">Admission</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="scholarship-tab" data-bs-toggle="tab" data-bs-target="#scholarship"
                                    type="button">Scholarship</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="finances-tab" data-bs-toggle="tab" data-bs-target="#finances"
                                    type="button">Finances</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="accommodation-tab" data-bs-toggle="tab"
                                    data-bs-target="#accommodation" type="button">Accommodation</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media"
                                    type="button">Media</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses"
                                    type="button">Courses</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq"
                                    type="button">FAQs</button>
                            </li>
                        @endif
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="universityTabContent">
                        @if(!$university->id)
                            <div class="alert alert-info">
                                Please create the university by entering basic details such as name, slug, country, and
                                city.
                                Additional information like rankings, media, and other details can be added after saving.
                            </div>
                        @endif
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">University Name</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="name"
                                        value="{{ old('name', $university->name) }}" placeholder="University Name">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">University Slug</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="slug"
                                        value="{{ old('slug', $university->slug) }}" placeholder="Slug (optional)">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Country</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="country"
                                        value="{{ old('country', $university->country) }}">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">City</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" name="city"
                                        value="{{ old('city', $university->city) }}">
                                </div>
                            </div>

                            @if($university->id)
                                <div class="row g-3 mb-4">
                                    <label class="col-lg-3 col-form-label fw-semibold">University Overview</label>
                                    <div class="col-lg-9">
                                        <textarea id="summernote-overview"
                                            name="overview">{!! $detail->overview ?? '' !!}</textarea>
                                        <div class="form-text">Provide a comprehensive overview of the university, its
                                            history, mission, and values.</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <script>
                            const nameInput = document.querySelector('[name="name"]');
                            const slugInput = document.querySelector('[name="slug"]');

                            let slugManuallyEdited = false;

                            // Detect manual editing of slug
                            slugInput.addEventListener('input', function () {
                                slugManuallyEdited = true;
                            });

                            nameInput.addEventListener('input', function () {
                                if (slugManuallyEdited) return; // stop auto update

                                let slug = this.value
                                    .toLowerCase()
                                    .replace(/[^a-z0-9]+/g, '-')
                                    .replace(/(^-|-$)/g, '');

                                slugInput.value = slug;
                            });
                        </script>

                        <!-- Ranking Tab -->
                        <div class="tab-pane fade" id="ranking" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Global Ranking</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" id="global_ranking" name="global_ranking"
                                        value="{{ $detail->global_ranking ?? '' }}" placeholder="e.g., #45 globally">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Country Ranking</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" id="country_ranking" name="country_ranking"
                                        value="{{ $detail->country_ranking ?? '' }}" placeholder="e.g., #5 in UK">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Batch Strength</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" id="batch_strength" name="batch_strength"
                                        value="{{ $detail->batch_strength ?? '' }}" placeholder="e.g., 14196">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Global Diversity (%)</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" id="global_diversity" name="global_diversity"
                                        value="{{ $detail->global_diversity ?? '' }}" placeholder="e.g., 41%">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">
                                    Ranking Overview
                                </label>
                                <div class="col-lg-9">
                                    <textarea id="summernote-ranking" name="ranking_info">
                                            {!! $detail->ranking_info ?? '' !!}
                                        </textarea>
                                    <div class="form-text">
                                        Provide detailed ranking insights such as QS ranking, world ranking,
                                        achievements, recognitions, and global position.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Costs Tab -->
                        <div class="tab-pane fade" id="costs" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Cost of Living (Annual)</label>
                                <div class="col-lg-9">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="cost_of_living" name="cost_of_living"
                                            value="{{ $detail->cost_of_living ?? '' }}" placeholder="26592" step="0.01">
                                        <select name="currency_code" class="form-select" style="max-width: 150px;">
                                            <option value="GBP" {{ ($detail->currency_code ?? 'GBP') === 'GBP' ? 'selected' : '' }}>
                                                GBP £</option>
                                            <option value="USD" {{ $detail->currency_code === 'USD' ? 'selected' : '' }}>
                                                USD $</option>
                                            <option value="EUR" {{ $detail->currency_code === 'EUR' ? 'selected' : '' }}>
                                                EUR €</option>
                                            <option value="AUD" {{ $detail->currency_code === 'AUD' ? 'selected' : '' }}>
                                                AUD $</option>
                                            <option value="CAD" {{ $detail->currency_code === 'CAD' ? 'selected' : '' }}>
                                                CAD $</option>
                                            <option value="INR" {{ $detail->currency_code === 'INR' ? 'selected' : '' }}>
                                                INR ₹</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Tuition Fee From</label>
                                <div class="col-lg-9">
                                    <input type="number" class="form-control" id="tuition_fee_from" name="tuition_fee_from"
                                        value="{{ old('tuition_fee_from', $detail->tuition_fee_from ?? $minFee) }}"
                                        placeholder="17000" step="0.01">
                                    <div class="form-text">Starting price for courses</div>
                                </div>
                            </div>
                        </div>

                        <!-- Admission Tab -->
                        <div class="tab-pane fade" id="admission" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Admission Requirements</label>
                                <div class="col-lg-9">
                                    <textarea id="summernote-admission"
                                        name="admission_requirements">{!! $detail->admission_requirements ?? '' !!}</textarea>
                                    <div class="form-text">Details about eligibility criteria, documents needed, and
                                        application process.</div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Entry Requirements URL</label>
                                <div class="col-lg-9">
                                    <input type="url" class="form-control" id="entry_requirements_url"
                                        name="entry_requirements_url" value="{{ $detail->entry_requirements_url ?? '' }}"
                                        placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- Scholarship Tab -->
                        <div class="tab-pane fade" id="scholarship" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Scholarship Information</label>
                                <div class="col-lg-9">
                                    <textarea id="summernote-scholarship"
                                        name="scholarship_info">{!! $detail->scholarship_info ?? '' !!}</textarea>
                                    <div class="form-text">Details about available scholarships, eligibility, and
                                        application process.</div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Scholarship URL</label>
                                <div class="col-lg-9">
                                    <input type="url" class="form-control" id="scholarship_url" name="scholarship_url"
                                        value="{{ $detail->scholarship_url ?? '' }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- Finances Tab -->
                        <div class="tab-pane fade" id="finances" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Finance Information</label>
                                <div class="col-lg-9">
                                    <textarea id="summernote-finances"
                                        name="finances_info">{!! $detail->finances_info ?? '' !!}</textarea>
                                    <div class="form-text">Information about costs, payment plans, financial aid, and
                                        bursaries.</div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Finance Details URL</label>
                                <div class="col-lg-9">
                                    <input type="url" class="form-control" id="finances_url" name="finances_url"
                                        value="{{ $detail->finances_url ?? '' }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- Accommodation Tab -->
                        <div class="tab-pane fade" id="accommodation" role="tabpanel">
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Accommodation Information</label>
                                <div class="col-lg-9">
                                    <textarea id="summernote-accommodation"
                                        name="accommodation_info">{!! $detail->accommodation_info ?? '' !!}</textarea>
                                    <div class="form-text">Details about on-campus and off-campus accommodation options.
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Accommodation URL</label>
                                <div class="col-lg-9">
                                    <input type="url" class="form-control" id="accommodation_url" name="accommodation_url"
                                        value="{{ $detail->accommodation_url ?? '' }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <!-- FAQs Tab -->
                        <div class="tab-pane fade" id="faq" role="tabpanel">
                            {{-- FAQ Header with Add Button --}}
                            <div class="row g-3 mb-2">
                                <div class="col-lg-12 d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0">Frequently Asked Questions</h6>
                                    <button type="button" class="btn btn-success btn-sm add-faq">+ Add FAQ</button>
                                </div>
                            </div>

                            {{-- FAQ Container --}}
                            <div id="faq-container" class="mb-3">
                                @php
                                    $faqs = [];
                                    if (!empty($detail->faq_content)) {
                                        $decoded = json_decode($detail->faq_content, true);
                                        $faqs = is_array($decoded) ? $decoded : [];
                                    }
                                @endphp

                                @if (count($faqs) > 0)
                                    @foreach ($faqs as $faq)
                                        <div class="faq-entry mb-3">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Question</label>
                                                    <input type="text" class="form-control faq-question"
                                                        value="{{ $faq['question'] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Answer</label>
                                                    <textarea class="form-control faq-answer"
                                                        rows="1">{{ $faq['answer'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" class="btn btn-danger btn-sm remove-faq">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="faq-entry mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Question</label>
                                                <input type="text" class="form-control faq-question">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Answer</label>
                                                <textarea class="form-control faq-answer" rows="1"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <button type="button" class="btn btn-danger btn-sm remove-faq">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Hidden field to store FAQ data as JSON --}}
                            <input type="hidden" name="faq_content" id="faq_content_json">

                            <div class="form-text">Add FAQ questions and answers. They will be displayed on the
                                university details page.</div>
                        </div>

                        <!-- Media Tab -->
                        <div class="tab-pane fade" id="media" role="tabpanel">
                            <div class="row g-3 align-items-start mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Banner Image</label>
                                <div class="col-lg-9">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="image-preview" id="bannerPreview">
                                            @if ($detail->banner_image)
                                                <img src="{{ asset('storage/' . $detail->banner_image) }}">
                                            @elseif ($university->banner_image)
                                                <img src="{{ asset($university->banner_image) }}">
                                            @else
                                                <img src="/images/blank.jpeg">
                                            @endif
                                        </div>
                                        <div>
                                            <label class="form-label mb-1">Upload image</label>
                                            <input type="file" name="banner_image" class="form-control"
                                                accept=".png,.jpg,.jpeg,.webp" id="bannerInput">
                                            <div class="form-text">Max 5MB. Formats: PNG, JPG, WebP</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-start mb-4">
                                <label class="col-lg-3 col-form-label fw-semibold">Thumbnail Image</label>
                                <div class="col-lg-9">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="image-preview" id="thumbPreview">
                                            @if ($detail->thumbnail_image)
                                                <img src="{{ asset('storage/' . $detail->thumbnail_image) }}" alt="thumbnail">
                                            @else
                                                <img src="/images/blank.jpeg" alt="preview">
                                            @endif
                                        </div>
                                        <div>
                                            <label class="form-label mb-1">Upload image</label>
                                            <input type="file" name="thumbnail_image" class="form-control"
                                                accept=".png,.jpg,.jpeg,.webp" id="thumbInput">
                                            <div class="form-text">Max 2MB. Formats: PNG, JPG, WebP</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Courses Tab -->
                        <div class="tab-pane fade" id="courses" role="tabpanel">
                            <style>
                                .course-card {
                                    border: 1px solid var(--bs-border-color);
                                    border-radius: 0.5rem;
                                    margin-bottom: 1.5rem;
                                    overflow: hidden;
                                }

                                .course-header {
                                    background: var(--bs-gray-100);
                                    padding: 1rem;
                                    cursor: pointer;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    font-weight: 500;
                                }

                                .course-header:hover {
                                    background: var(--bs-gray-200);
                                }

                                .course-body {
                                    padding: 1.5rem;
                                    display: none;
                                }

                                .course-body.show {
                                    display: block;
                                }

                                .course-field {
                                    margin-bottom: 1.5rem;
                                }

                                .course-field label {
                                    font-weight: 600;
                                    margin-bottom: 0.5rem;
                                    display: block;
                                    font-size: 0.875rem;
                                }

                                .course-actions {
                                    display: flex;
                                    gap: 0.5rem;
                                    margin-top: 1rem;
                                }
                            </style>

                            <div class="mb-5">
                                <h6 class="mb-4">Existing Courses</h6>

                                @if ($university->courses && $university->courses->count() > 0)
                                    <div id="coursesList">
                                        @foreach ($university->courses as $index => $course)
                                            <div class="course-card" data-course-id="{{ $course->id }}">
                                                <div class="course-header"
                                                    onclick="document.querySelector('.course-body[data-course-id=&quot;{{ $course->id }}&quot;]').classList.toggle('show'); this.querySelector('.toggle-icon').classList.toggle('rotate')">
                                                    <div>
                                                        <strong>{{ $course->course_name }}</strong>
                                                        <small class="text-muted ms-3">{{ $course->course_type ?? 'N/A' }} •
                                                            {{ $course->duration }}</small>
                                                    </div>
                                                    <span class="toggle-icon" style="transition: transform 0.3s;">▼</span>
                                                </div>
                                                <div class="course-body" data-course-id="{{ $course->id }}">
                                                    <div class="courseEditForm" data-course-id="{{ $course->id }}">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Course Name</label>
                                                                    <input type="text" class="form-control" name="course_name"
                                                                        value="{{ $course->course_name }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Duration</label>
                                                                    <input type="text" class="form-control" name="duration"
                                                                        value="{{ $course->duration }}" placeholder="e.g., 2 Years">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Course Type</label>
                                                                    <input type="text" class="form-control" name="course_type"
                                                                        value="{{ $course->course_type }}"
                                                                        placeholder="e.g., Postgraduate">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Location</label>
                                                                    <input type="text" class="form-control" name="location"
                                                                        value="{{ $course->location }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Tuition Fee</label>
                                                                    <input type="number" class="form-control" name="tuition_fee"
                                                                        value="{{ $course->tuition_fee }}" min="0" step="0.01">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Application Fee</label>
                                                                    <input type="number" class="form-control" name="application_fee"
                                                                        value="{{ $course->application_fee ?? 0 }}" min="0"
                                                                        step="0.01">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Currency Code</label>
                                                                    <select class="form-select currencySelect" name="currency_code"
                                                                        data-course-id="{{ $course->id }}">
                                                                        <option value="USD" {{ $course->currency_code === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                                                        <option value="GBP" {{ $course->currency_code === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                                                        <option value="EUR" {{ $course->currency_code === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                                                        <option value="INR" {{ $course->currency_code === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                                                                        <option value="CAD" {{ $course->currency_code === 'CAD' ? 'selected' : '' }}>CAD (C$)</option>
                                                                        <option value="AUD" {{ $course->currency_code === 'AUD' ? 'selected' : '' }}>AUD (A$)</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="course-field">
                                                                    <label>Currency Symbol</label>
                                                                    <input type="text" class="form-control currencySymbol"
                                                                        name="currency_symbol"
                                                                        value="{{ $course->currency_symbol }}" readonly
                                                                        style="background: var(--bs-gray-100);">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="course-actions">
                                                            <button type="button" class="btn btn-sm btn-primary saveCourseButtton"
                                                                data-course-id="{{ $course->id }}">
                                                                <i class="fas fa-save"></i> Save
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger deleteCourseButton"
                                                                data-course-id="{{ $course->id }}">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </div>
                                                        <div class="courseMessage mt-2" style="display: none;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> No courses added yet.
                                    </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div>
                                <h6 class="mb-4">Add New Course</h6>
                                <div id="addCourseForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Course Name</label>
                                                <input type="text" class="form-control" name="course_name"
                                                    placeholder="e.g., Master of Business Administration">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Duration</label>
                                                <input type="text" class="form-control" name="duration"
                                                    placeholder="e.g., 2 Years">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Course Type</label>
                                                <input type="text" class="form-control" name="course_type"
                                                    placeholder="e.g., Postgraduate">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Location</label>
                                                <input type="text" class="form-control" name="location"
                                                    placeholder="e.g., New York, USA">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Tuition Fee</label>
                                                <input type="number" class="form-control" name="tuition_fee"
                                                    placeholder="Enter amount" min="0" step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Application Fee</label>
                                                <input type="number" class="form-control" name="application_fee"
                                                    placeholder="Optional" min="0" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>Currency Code</label>
                                                <select class="form-select" name="currency_code" id="newCourseCurrency"
                                                    required>
                                                    <option value="USD">USD ($)</option>
                                                    <option value="GBP" selected>GBP (£)</option>
                                                    <option value="EUR">EUR (€)</option>
                                                    <option value="INR">INR (₹)</option>
                                                    <option value="CAD">CAD (C$)</option>
                                                    <option value="AUD">AUD (A$)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="course-field">
                                                <label>&nbsp;</label>
                                                <button type="button" id="addCourseBtn" class="btn btn-success w-100">
                                                    <i class="fas fa-plus"></i> Add Course
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="addCourseMessage" class="alert mt-3" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mb-4" style="margin-top: 40px;">
                        <label class="col-lg-3 col-form-label fw-semibold">Status</label>
                        <div class="col-lg-9">
                            <select name="status" class="form-select">
                                <option value="draft" {{ $detail->status === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="published" {{ $detail->status === 'published' ? 'selected' : '' }}>
                                    Publish
                                </option>
                            </select>
                            <div class="form-text">
                                Published details will be visible to students on the frontend.
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('university-details.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery & Summernote -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>

    <script>
        // Initialize Summernote editors
        const summernoteConfig = {
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview', 'help']]
            ]
        };

        $('#summernote-overview').summernote(summernoteConfig);
        $('#summernote-ranking').summernote(summernoteConfig);
        $('#summernote-admission').summernote(summernoteConfig);
        $('#summernote-scholarship').summernote(summernoteConfig);
        $('#summernote-finances').summernote(summernoteConfig);
        $('#summernote-accommodation').summernote(summernoteConfig);

        // Form submission handler
        document.getElementById('universityForm').addEventListener('submit', function (e) {
            // Handle FAQ data - convert to JSON and save to faq_content field
            let faqs = [];
            document.querySelectorAll(".faq-entry").forEach(entry => {
                let question = entry.querySelector(".faq-question")?.value.trim();
                let answer = entry.querySelector(".faq-answer")?.value.trim();
                if (question && answer) faqs.push({ question, answer });
            });
            document.getElementById("faq_content_json").value = JSON.stringify(faqs);
        });

        // FAQ add/remove functionality
        document.addEventListener("DOMContentLoaded", function () {
            function addFAQ() {
                let container = document.getElementById("faq-container");
                let wrapper = document.createElement("div");
                wrapper.className = "faq-entry mb-3";
                wrapper.innerHTML = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Question</label>
                                <input type="text" class="form-control faq-question" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Answer</label>
                                <textarea class="form-control faq-answer" rows="1" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-danger btn-sm remove-faq">Remove</button>
                            </div>
                        </div>
                    `;
                container.appendChild(wrapper);
            }

            const addBtn = document.querySelector(".add-faq");
            if (addBtn) {
                addBtn.addEventListener("click", addFAQ);
            }

            document.addEventListener("click", function (e) {
                if (e.target.classList.contains("remove-faq")) {
                    e.target.closest(".faq-entry").remove();
                }
            });
        });

        // Image preview functionality
        (function () {
            // Banner image preview
            const bannerInput = document.getElementById('bannerInput');
            const bannerPreview = document.querySelector('#bannerPreview img');
            if (bannerInput) {
                bannerInput.addEventListener('change', (e) => {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (ev) => { bannerPreview.src = ev.target.result; };
                    reader.readAsDataURL(file);
                });
            }

            // Thumbnail image preview
            const thumbInput = document.getElementById('thumbInput');
            const thumbPreview = document.querySelector('#thumbPreview img');
            if (thumbInput) {
                thumbInput.addEventListener('change', (e) => {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (ev) => { thumbPreview.src = ev.target.result; };
                    reader.readAsDataURL(file);
                });
            }

            // Delete course
            document.querySelectorAll('.deleteCourseButton').forEach(button => {
                button.addEventListener('click', async function (e) {
                    e.preventDefault();
                    if (!confirm('Are you sure you want to delete this course?')) return;

                    const courseId = this.dataset.courseId;
                    const courseCard = document.querySelector(`.course-card[data-course-id="${courseId}"]`);

                    try {
                        const response = await fetch(`/university-details/delete-course/${courseId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            courseCard.style.opacity = '0.5';
                            courseCard.style.pointerEvents = 'none';
                            setTimeout(() => {
                                courseCard.remove();
                            }, 300);
                        } else {
                            alert(result.message || 'Error deleting course');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                });
            });

            // Add new course
            const addCourseForm = document.getElementById('addCourseForm');
            if (addCourseForm) {
                document.getElementById('addCourseBtn').addEventListener('click', async function () {
                    const form = document.getElementById('addCourseForm');
                    const formData = new FormData(form);
                    const messageDiv = document.getElementById('addCourseMessage');

                    const courseData = {
                        course_name: formData.get('course_name'),
                        duration: formData.get('duration'),
                        course_type: formData.get('course_type'),
                        location: formData.get('location'),
                        tuition_fee: parseFloat(formData.get('tuition_fee')),
                        application_fee: parseFloat(formData.get('application_fee')) || 0,
                        currency_code: formData.get('currency_code'),
                        currency_symbol: currencySymbols[formData.get('currency_code')] || '$',
                        university_id: "{{ $university->id }}"
                    };

                    try {
                        const response = await fetch('{{ route('university-details.add-course') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(courseData)
                        });

                        const result = await response.json();

                        if (result.success) {
                            messageDiv.className = 'alert alert-success';
                            messageDiv.innerText = result.message;
                            messageDiv.style.display = 'block';

                            setTimeout(() => location.reload(), 1000);
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (err) {
                        messageDiv.className = 'alert alert-danger';
                        messageDiv.innerText = err.message;
                        messageDiv.style.display = 'block';
                    }
                });
            }
        })();
    </script>

    <script>
        $(document).ready(function () {

            let universityId = "{{ $university->id }}";
            let csrfToken = "{{ csrf_token() }}";

            /* =========================
               UPDATE COURSE
            ========================= */

            $(".saveCourseButtton").click(function () {

                let courseId = $(this).data("course-id");
                let card = $(".course-card[data-course-id='" + courseId + "']");
                let form = card.find(".courseEditForm");

                let formData = {
                    _token: csrfToken,
                    course_name: form.find("input[name='course_name']").val(),
                    duration: form.find("input[name='duration']").val(),
                    course_type: form.find("input[name='course_type']").val(),
                    location: form.find("input[name='location']").val(),
                    tuition_fee: form.find("input[name='tuition_fee']").val(),
                    application_fee: form.find("input[name='application_fee']").val(),
                    currency_code: form.find("select[name='currency_code']").val(),
                    currency_symbol: form.find("input[name='currency_symbol']").val()
                };

                $.ajax({
                    url: "/university-details/update-course/" + courseId,
                    type: "POST",
                    data: formData,
                    success: function (response) {

                        form.find(".courseMessage")
                            .removeClass("text-danger")
                            .addClass("text-success")
                            .text(response.message)
                            .show();

                    },
                    error: function () {

                        form.find(".courseMessage")
                            .removeClass("text-success")
                            .addClass("text-danger")
                            .text("Error updating course")
                            .show();
                    }
                });

            });


            /* =========================
               CURRENCY AUTO SYMBOL
            ========================= */

            $(".currencySelect").change(function () {

                let code = $(this).val();
                let symbol = "$";

                switch (code) {
                    case "USD": symbol = "$"; break;
                    case "GBP": symbol = "£"; break;
                    case "EUR": symbol = "€"; break;
                    case "INR": symbol = "₹"; break;
                    case "CAD": symbol = "C$"; break;
                    case "AUD": symbol = "A$"; break;
                }

                $(this).closest(".row")
                    .find(".currencySymbol")
                    .val(symbol);

            });

        });
    </script>
@endsection