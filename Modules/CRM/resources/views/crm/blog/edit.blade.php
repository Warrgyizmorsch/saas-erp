@extends('layouts.app')

@section('content')
    {{-- Minimal page-specific tweaks; Bootstrap-first so dark-mode stays consistent --}}
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

        /* Quill height */
        .note-editor .note-editable {
            min-height: 400px;
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
            <!-- Page Header (mirrors User module) -->
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit Blog</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog List</a></li>
                        <li class="breadcrumb-item">Edit</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto d-flex" style="gap:10px;">
                    <a href="/crm-blog?site=warrgyizmorsch" class="btn btn-secondary">Back to Warr Blogs</a>
                </div>
            </div>

            <!-- Optional collapsible help -->
            <div id="collapseHelp" class="accordion-collapse collapse page-header-collapse">
                <div class="accordion-body pb-2">
                    <div class="alert alert-info mb-0">
                        Update content in the editor. FAQs below are saved as JSON in <code>faq_data</code>.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="crm-page-container">
        <div class="card">
            <div class="card-body">
                <form id="blogForm" action="{{ route('blog.update', $data['blog']->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Thumbnail (keeps same field name "photo"; shows current image + live preview) --}}
                    <div class="row g-3 align-items-start mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Thumbnail</label>
                        <div class="col-lg-9">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="image-preview" id="thumbPreview">
                                    @if ($data['blog']->images)
                                        <img src="{{ asset($data['blog']->images) }}" alt="preview">
                                    @else
                                        <img src="/images/blank.jpeg" alt="preview">
                                    @endif
                                </div>
                                <div>
                                    <label class="form-label mb-1">Upload image</label>
                                    <input type="file" name="photo" class="form-control" accept=".png,.jpg,.jpeg,.webp"
                                        id="thumbInput" onchange="showImageDimensionsAlert(this)">
                                    <div class="form-text">Allowed file types: png, jpg, jpeg, webp.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site column -->
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Website</label>
                        <div class="col-lg-9">
                            <select name="site" id="siteSelect" class="form-select">
                                <option value="wts" {{ ($data['blog']->site ?? 'wts') === 'wts' ? 'selected' : '' }}>WTS</option>
                                <option value="warrgyizmorsch" {{ ($data['blog']->site ?? 'wts') === 'warrgyizmorsch' ? 'selected' : '' }}>
                                    Warrgyizmorsch</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Category according to the site -->
                    <div class="row g-3 mb-4" id="categoryRow" style="display:none;">
                        <label class="col-lg-3 col-form-label fw-semibold">Category</label>
                        <div class="col-lg-9">
                            <select name="category" id="categorySelect" class="form-select">
                                <option value="">Select Category</option>
                                <option value="IT Services" {{ ($data['blog']->category == "IT Services") ? "selected" : "" }}>IT Services
                                </option>
                                <option value="Digital Marketing" {{ ($data['blog']->category == "Digital Marketing") ? "selected" : "" }}>
                                    Digital Marketing</option>
                                <option value="Web Development & Design" {{ ($data['blog']->category == "Web Development & Design") ? "selected" : "" }}>Web Development & Design</option>
                                <option value="Software & Business Solutions" {{ ($data['blog']->category == "Software & Business Solutions") ? "selected" : "" }}>Software & Business Solutions</option>
                                <option value="SEO" {{ ($data['blog']->category == "SEO") ? "selected" : "" }}>SEO</option>
                                <option value="E-commerce" {{ ($data['blog']->category == "E-commerce") ? "selected" : "" }}>E-commerce
                                </option>
                            </select>
                            <div class="form-text">Category is required only for Warrgyizmorsch.</div>
                        </div>
                    </div>

                    {{-- Blog URL (slug) --}}
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold" for="blogUrl">Blog URL</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" id="blogUrl" name="blogUrl" required
                                value="{{ $data['blog']->slug }}">
                        </div>
                    </div>

                    {{-- Title & Type --}}
                    <div class="row g-3 mb-4">
                        <label for="blogTitle" class="col-lg-3 col-form-label fw-semibold">Blog Title</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" id="blogTitle" name="blogTitle" required
                                value="{{ $data['blog']->title }}">
                            <input type="hidden" name="type" value="blog">
                        </div>
                    </div>

                    <!-- Author dropdown -->
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Author</label>
                        <div class="col-lg-9">
                            <select name="author_image" class="form-select">
                                <option value="">Select Author</option>

                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}"
                                        {{ (int) ($data['blog']->author_image ?? 0) === (int) $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Leave empty to keep no author</div>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Created At</label>
                        <div class="col-lg-9">
                            <input
                                type="datetime-local"
                                name="created_at"
                                class="form-control"
                                value="{{ optional($data['blog']->created_at)->format('Y-m-d\TH:i') }}"
                            >
                            <div class="form-text">You can change the blog publish date/time.</div>
                        </div>
                    </div>

                    {{-- Content (Quill) --}}
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Blog Content</label>
                        <div class="col-lg-9">
                            <textarea id="summernote" name="blogContent" required>{!! $data['blog']['content'] !!}</textarea>                            
                            <div class="form-text">Tables and lists are normalized to Bootstrap classes for better
                                dark-mode.</div>
                        </div>
                    </div>

                    {{-- Meta Tag --}}
                    <div class="row g-3 mb-4" style="margin-top: 80px;">
                        <label class="col-lg-3 col-form-label fw-semibold">Meta Tag</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="MetaTag" required
                                value="{{ $data['blog']->meta_title }}">
                        </div>
                    </div>

                    {{-- Meta Description --}}
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Meta Description</label>
                        <div class="col-lg-9">
                            <textarea class="form-control" name="Metadescription"
                                rows="3" required>{{ $data['blog']->meta_discribtion }}</textarea>
                        </div>
                    </div>

                    {{-- FAQ --}}
                    <div class="row g-3 mb-2">
                        <div class="col-lg-12 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">FAQ</h6>
                            <button type="button" class="btn btn-success btn-sm add-faq">+ Add FAQ</button>
                        </div>
                    </div>
                    <div id="faq-container" class="mb-3">
                        @foreach($faqData as $faq)
                            <div class="faq-entry mb-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Question</label>
                                        <input type="text" class="form-control faq-question" value="{{ $faq['question'] }}"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Answer</label>
                                        <textarea class="form-control faq-answer" rows="1"
                                            required>{{ $faq['answer'] }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn btn-danger btn-sm remove-faq">Remove</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="faq_data" id="faq_data">

                    {{-- Blog Status --}}
                    <div class="row g-3 mb-4">
                        <label class="col-lg-3 col-form-label fw-semibold">Status</label>
                        <div class="col-lg-9">
                            <select name="status" class="form-select">
                                <option value="draft" {{ $data['blog']->status === 'draft' ? 'selected' : '' }}>
                                    Draft
                                </option>
                                <option value="publish" {{ $data['blog']->status === 'publish' ? 'selected' : '' }}>
                                    Publish
                                </option>
                            </select>
                            <div class="form-text">
                                Draft blogs are hidden from the website.
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('blog.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
     <script>
      $('#summernote').summernote({
        placeholder: 'Hello stand alone ui',
        tabsize: 2,
        height: 120,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture']],
          ['view', ['codeview', 'help']]
        ]
      });
    </script>

    <script>
        // Thumbnail preview + optional dimensions alert (keeps your onchange behavior intact)
        function showImageDimensionsAlert(input) {
            const file = input.files?.[0];
            if (!file) return;
            const img = new Image();
            img.onload = function () {
                // You can customize the message or validation here if needed
                // alert(`Selected image: ${this.width}×${this.height}px`);
            };
            img.src = URL.createObjectURL(file);
        }

        (function () {
            const input = document.getElementById('thumbInput');
            const preview = document.querySelector('#thumbPreview img');
            if (input) {
                input.addEventListener('change', (e) => {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (ev) => { preview.src = ev.target.result; };
                    reader.readAsDataURL(file);
                });
            }
        })();

        
        // FAQ add/remove + JSON pack on submit
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

            document.querySelector(".add-faq").addEventListener("click", addFAQ);

            document.addEventListener("click", function (e) {
                if (e.target.classList.contains("remove-faq")) {
                    e.target.closest(".faq-entry").remove();
                }
            });

            document.getElementById("blogForm").addEventListener("submit", function () {
                let faqs = [];
                document.querySelectorAll(".faq-entry").forEach(entry => {
                    let question = entry.querySelector(".faq-question")?.value.trim();
                    let answer = entry.querySelector(".faq-answer")?.value.trim();
                    if (question && answer) faqs.push({ question, answer });
                });
                document.getElementById("faq_data").value = JSON.stringify(faqs);
            });
        });

        // category dropdown
        document.addEventListener("DOMContentLoaded", function () {
            const siteSelect = document.getElementById("siteSelect");
            const categoryRow = document.getElementById("categoryRow");
            const categorySelect = document.getElementById("categorySelect");

            function toggleCategory() {
                const isWarr = siteSelect.value === "warrgyizmorsch";
                categoryRow.style.display = isWarr ? "" : "none";
                if (!isWarr) categorySelect.value = "";
            }

            if (siteSelect) {
                siteSelect.addEventListener("change", toggleCategory);
                toggleCategory();
            }
        });
    </script>
@endsection