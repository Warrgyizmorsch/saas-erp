@extends('layouts.app')

@section('content')
<style>
    .entry-box{
        border: 1px dashed var(--bs-border-color);
        border-radius: .75rem;
        padding: 1rem;
        background: var(--bs-body-bg);
    }
</style>

@php
// ✅ Clone support (Create page can be opened with ?clone_id=ID)
$isClone = request()->filled('clone_id') && isset($clone) && $clone;

// For More Services multi-select
$selectedMoreOld = old('more_services', $selectedMoreServices ?? []);

// ✅ Prefill helpers (clone -> old() -> default)
$pref = fn($key, $default = '') => old($key, data_get($clone ?? null, $key, $default));

// City list (when cloning, controller passes $cities; otherwise empty)
$cities = $cities ?? [];
@endphp

<main>
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    {{ $isClone ? 'Clone Service Page' : 'Create Service Page' }}
                </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('warr-service-pages.index') }}">Service Pages</a></li>
                <li class="breadcrumb-item">{{ $isClone ? 'Clone' : 'Create' }}</li>
            </ul>
        </div>

        <div class="page-header-right ms-auto d-flex" style="gap:10px;">
            <a href="{{ route('warr-service-pages.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    @if($isClone)
        <div class="alert alert-info py-2 small mt-2">
            You are cloning from: <strong>{{ $clone->hero_title }}</strong> (Slug: <code>/{{ $clone->slug }}</code>)
            <br>
            ✅ Slug is intentionally left blank so a new unique slug can be generated.
        </div>
    @endif
</main>

<div class="crm-page-container">
    <div class="card">
        <div class="card-body">
            <form id="servicePageForm" action="{{ route('warr-service-pages.store') }}" method="POST">
                @csrf

                {{-- Main Service --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Main Service</label>
                    <div class="col-lg-9">
                        <select name="service_id" class="form-select" required>
                            <option value="">Select Service</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}"
                                    {{ (string) old('service_id', $clone->service_id ?? '') === (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Location --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Country</label>
                    <div class="col-lg-9">
                        <select name="country_id" id="countrySelect" class="form-select" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}"
                                    {{ (string) old('country_id', $clone->country_id ?? '') === (string) $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">City</label>
                    <div class="col-lg-9">
                        <select name="city_id" id="citySelect" class="form-select">
                            <option value="">Select City</option>

                            {{-- ✅ If cloning, controller passes cities of selected country --}}
                            @if(!empty($cities))
                                @foreach($cities as $ct)
                                    <option value="{{ $ct->id }}"
                                        {{ (string) old('city_id', $clone->city_id ?? '') === (string) $ct->id ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text">City will load after selecting Country.</div>
                    </div>
                </div>

                {{-- More Services (multi select) --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">More Services</label>
                    <div class="col-lg-9">
                        <select
                            name="more_services[]"
                            id="moreServicesSelect"
                            class="form-select"
                            multiple
                            data-select2-selector="tag"
                            data-placeholder="Select services (leave empty for all)"
                            data-allow-clear="true"
                        >
                            @foreach($services as $s)
                                <option value="{{ $s->id }}"
                                    {{ in_array($s->id, $selectedMoreOld, true) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            Leave blank to show all services. Selecting items will limit the display to those services only.
                        </div>
                    </div>
                </div>

                {{-- Hero --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Hero Title</label>
                    <div class="col-lg-9">
                        <input type="text" name="hero_title" class="form-control" required
                               value="{{ old('hero_title', $clone->hero_title ?? '') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Hero Description</label>
                    <div class="col-lg-9">
                        <textarea name="hero_description" class="form-control" rows="4">{{ old('hero_description', $clone->hero_description ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Slug --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Slug</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            {{-- ✅ On clone, keep blank by default to avoid collision --}}
                            <input type="text" name="slug" class="form-control"
                                   value="{{ old('slug', $isClone ? '' : ($clone->slug ?? '')) }}"
                                   placeholder="Leave blank to auto-generate from Hero Title">
                        </div>
                    </div>
                </div>

                {{-- Sections 1..4 --}}
                @for($i = 1; $i <= 4; $i++)
                    <hr class="my-4">
                    <h6 class="mb-3">Section {{ $i }}</h6>

                    {{-- Section Notes --}}
                    @if($i === 1)
                        <div class="alert alert-info py-2 small">
                            <strong>Section 1:</strong>
                            Card-based feature section. Each point appears as a card.
                            <br>
                            <strong>Recommended:</strong> 6 points (can vary if needed).
                        </div>
                    @elseif($i === 2)
                        <div class="alert alert-info py-2 small">
                            <strong>Section 2:</strong>
                            Two-column layout.
                            <ul class="mb-0 ps-3">
                                <li><strong>Left:</strong> Section title & description</li>
                                <li><strong>Right:</strong> Accordion-style points</li>
                            </ul>
                        </div>
                    @elseif($i === 3)
                        <div class="alert alert-info py-2 small">
                            <strong>Section 3:</strong>
                            CTA section with heading, description, and call-to-action button.
                            <br>
                            <strong>No points required for this section.</strong>
                        </div>
                    @elseif($i === 4)
                        <div class="alert alert-info py-2 small">
                            <strong>Section 4:</strong>
                            CTA with visual layout 
                            <ul class="mb-0 ps-3">
                                <li><strong>Left:</strong> Image</li>
                                <li><strong>Right:</strong> Exactly 2 CTA points</li>
                            </ul>
                            OR Unique Image Section (Heading and Content)
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <label class="col-lg-3 col-form-label fw-semibold">Title</label>
                        <div class="col-lg-9">
                            <input type="text" name="section{{ $i }}_title" class="form-control"
                                   value="{{ old("section{$i}_title", data_get($clone ?? null, "section{$i}_title", '')) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <label class="col-lg-3 col-form-label fw-semibold">Description</label>
                        <div class="col-lg-9">
                            <textarea name="section{{ $i }}_description" class="form-control" rows="4">{{ old("section{$i}_description", data_get($clone ?? null, "section{$i}_description", '')) }}</textarea>
                        </div>
                    </div>

                    {{-- Points: heading + description pairs --}}
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">Section {{ $i }} Points</h6>
                        <button type="button" class="btn btn-success btn-sm add-point" data-section="{{ $i }}">+ Add Point</button>
                    </div>

                    <div id="points-container-{{ $i }}" class="mb-3"></div>
                    <input type="hidden" name="section{{ $i }}_points" id="section{{ $i }}_points">
                @endfor

                {{-- Meta --}}
                <hr class="my-4">
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Meta Title</label>
                    <div class="col-lg-9">
                        <input type="text" name="meta_title" class="form-control"
                               value="{{ old('meta_title', $clone->meta_title ?? '') }}">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Meta Description</label>
                    <div class="col-lg-9">
                        <textarea name="meta_description" class="form-control" rows="3">{{ old('meta_description', $clone->meta_description ?? '') }}</textarea>
                    </div>
                </div>

                {{-- FAQ --}}
                <hr class="my-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0">FAQ</h6>
                    <button type="button" class="btn btn-success btn-sm add-faq">+ Add FAQ</button>
                </div>

                <div id="faq-container" class="mb-3"></div>
                <input type="hidden" name="faq_data" id="faq_data">

                {{-- Status (CREATE: only Draft) --}}
                <div class="row g-3 mb-4">
                    <label class="col-lg-3 col-form-label fw-semibold">Status</label>
                    <div class="col-lg-9">
                        <select name="status" class="form-select" required>
                            <option value="draft" selected>Draft</option>
                        </select>
                        <div class="form-text">
                            New pages are always created as Draft. Publish is available only in Edit.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warr-service-pages.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        {{ $isClone ? 'Create Cloned Page' : 'Submit' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ Pass clone data to JS for Points + FAQ prefill --}}
<script>
    window.__WARR_CLONE__ = @json($clone ?? null);
    window.__WARR_CLONE_FAQ__ = @json($faqData ?? []);
</script>

<script>
    // Country -> Cities
    document.getElementById('countrySelect').addEventListener('change', async function () {
        const countryId = this.value;
        const citySelect = document.getElementById('citySelect');
        citySelect.innerHTML = `<option value="">Select City</option>`;
        if (!countryId) return;

        const url = `{{ route('warr-service-pages.cities') }}?country_id=${countryId}`;
        const res = await fetch(url);
        const cities = await res.json();

        cities.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            citySelect.appendChild(opt);
        });
    });

    // Points add/remove
    function pointHtml(sectionNo){
        return `
            <div class="entry-box mb-2 point-entry">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Point Heading</label>
                        <input type="text" class="form-control point-heading" placeholder="Heading...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Point Description</label>
                        <textarea class="form-control point-description" rows="1" placeholder="Description..."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-danger btn-sm remove-point">Remove</button>
                    </div>
                </div>
            </div>
        `;
    }

    document.addEventListener('click', function(e){
        if(e.target.classList.contains('add-point')){
            const section = e.target.getAttribute('data-section');
            document.getElementById(`points-container-${section}`).insertAdjacentHTML('beforeend', pointHtml(section));
        }
        if(e.target.classList.contains('remove-point')){
            e.target.closest('.point-entry').remove();
        }
    });

    // FAQ add/remove
    function faqHtml(){
        return `
            <div class="entry-box mb-3 faq-entry">
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
            </div>
        `;
    }

    document.addEventListener('click', function(e){
        if(e.target.classList.contains('add-faq')){
            document.getElementById('faq-container').insertAdjacentHTML('beforeend', faqHtml());
        }
        if(e.target.classList.contains('remove-faq')){
            e.target.closest('.faq-entry').remove();
        }
    });

    // ✅ Clone prefill for Points + FAQ
    document.addEventListener("DOMContentLoaded", function () {
        const clone = window.__WARR_CLONE__;

        // Prefill points
        if (clone) {
            for (let s = 1; s <= 4; s++) {
                const points = clone[`section${s}_points`] || [];
                points.forEach(p => {
                    // add a new row
                    document.querySelector(`.add-point[data-section="${s}"]`)?.click();

                    // fill last row
                    const container = document.getElementById(`points-container-${s}`);
                    const last = container?.querySelector(".point-entry:last-child");
                    if (!last) return;

                    last.querySelector(".point-heading").value = p.heading || "";
                    last.querySelector(".point-description").value = p.description || "";
                });
            }
        }

        // Prefill FAQ
        const faqContainer = document.getElementById('faq-container');

        if (clone && faqContainer) {
            faqContainer.innerHTML = "";

            const faqs = window.__WARR_CLONE_FAQ__ || [];
            faqs.forEach(f => {
                faqContainer.insertAdjacentHTML('beforeend', faqHtml());
                const last = faqContainer.querySelector('.faq-entry:last-child');
                if (!last) return;
                last.querySelector('.faq-question').value = f.question || "";
                last.querySelector('.faq-answer').value = f.answer || "";
            });

            if (faqs.length === 0) {
                faqContainer.insertAdjacentHTML('beforeend', faqHtml());
            }
        } else {
            // default one FAQ row
            document.getElementById('faq-container')?.insertAdjacentHTML('beforeend', faqHtml());
        }
    });

    // Pack JSON on submit
    document.getElementById('servicePageForm').addEventListener('submit', function () {

        // sections points JSON packing
        for(let s=1; s<=4; s++){
            const points = [];
            document.querySelectorAll(`#points-container-${s} .point-entry`).forEach(entry => {
                const heading = entry.querySelector('.point-heading')?.value.trim();
                const description = entry.querySelector('.point-description')?.value.trim();
                if(heading || description){
                    points.push({ heading: heading || "", description: description || "" });
                }
            });
            document.getElementById(`section${s}_points`).value = JSON.stringify(points);
        }

        // faq json packing
        const faqs = [];
        document.querySelectorAll('#faq-container .faq-entry').forEach(entry => {
            const q = entry.querySelector('.faq-question')?.value.trim();
            const a = entry.querySelector('.faq-answer')?.value.trim();
            if(q && a) faqs.push({ question: q, answer: a });
        });
        document.getElementById('faq_data').value = JSON.stringify(faqs);
    });
</script>
@endsection
