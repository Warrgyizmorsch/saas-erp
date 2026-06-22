@props(['buckets','filterBucket', 'totalLeadsCount', 'filteredLeadCount', 'sources', 'owners','categories'])

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">

        <div class="page-header-title">
            <h5 class="m-b-10">Leads</h5>
        </div>

        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Home</a>
            </li>
            <li class="breadcrumb-item">Leads</li>
        </ul>

        <div class="d-flex align-items-center ms-3 gap-2">
            <span class="badge bg-primary px-2 py-1" style="font-size: 0.8rem;">
                Total: {{ $totalLeadsCount }}
            </span>

            @if(request()->query() && $filteredLeadCount != $totalLeadsCount)
            <span class="badge bg-info px-2 py-1" style="font-size: 0.8rem;">
                Filtered: {{ $filteredLeadCount }}
            </span>
            @endif
        </div>
    </div>

    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2">

            {{-- Collapse Toggle --}}
            <button class="btn btn-icon btn-light-brand"
                data-bs-toggle="collapse"
                data-bs-target="#collapseOne">
                <i class="feather-bar-chart"></i>
            </button>

            {{-- Bucket Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown">
                    <i class="feather-filter"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-end">

                    <a href="{{ route('modern.leads.index', request()->except('bucket_id')) }}"
                        class="dropdown-item {{ request('bucket_id') ? '' : 'active' }}">
                        All Buckets
                    </a>

                    @foreach($buckets as $bucket)
                    <a href="{{ route('modern.leads.index', array_merge(request()->query(), ['bucket_id' => $bucket->id, 'lead_status' => ''])) }}"
                        class="dropdown-item {{ request('bucket_id') == $bucket->id ? 'active' : '' }}">
                        {{ $bucket->name }}
                    </a>
                    @endforeach

                </div>
            </div>

            {{-- Create --}}
            <button class="btn btn-icon btn-light-brand"
                onclick="typeof openCreateModal === 'function' ? openCreateModal() : window.location.href='{{ route('lead.create') }}'">
                <i class="feather-plus"></i>
            </button>


            {{-- Import --}}
            <div class="dropdown d-flex align-items-center">
                <button class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown">
                    <i class="feather-paperclip"></i>
                </button>

                <span id="import-spinner" class="spinner-border spinner-border-sm text-primary ms-2 d-none"></span>

                <div class="dropdown-menu dropdown-menu-end">
                    <!-- ✅ EXPORT -->
                    <a href="{{ route('leads.export', request()->query()) }}" class="dropdown-item" download>
                        <i class="feather-download me-2"></i> Export Excel
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="{{ route('lead.sample') }}"  class="dropdown-item" download>Download Sample</a>
                    <div class="dropdown-divider"></div>

                    <label for="importFile" class="dropdown-item">Import</label>
                    <input type="file" id="importFile" class="d-none" accept=".csv,.xlsx,.xls">
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ✅ COLLAPSE --}}
<div id="collapseOne" class="collapse mt-3 {{ request()->query() ? 'show' : '' }}">
    <div class="card card-body shadow-sm">

        <form method="GET" action="{{ route('modern.leads.index') }}">

            {{-- ✅ Preserve bucket --}}
            @if(request('bucket_id'))
            <input type="hidden" name="bucket_id" value="{{ request('bucket_id') }}">
            @endif

            @if(request('lead_status'))
            <input type="hidden"
                name="lead_status"
                value="{{ request('lead_status') }}">
            @endif

            <div class="row g-3">

                <div class="col-12 col-md-2 d-block d-md-block">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search..."
                        value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="date" name="from" class="form-control"
                        value="{{ request('from') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="date" name="to" class="form-control"
                        value="{{ request('to') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <select name="source" class="form-select">
                        <option value="">All Sources</option>
                        @foreach($sources ?? [] as $source)
                        <option value="{{ $source }}"
                            {{ request('source') == $source ? 'selected' : '' }}>
                            {{ $source }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-none d-md-block">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($filterBucket as $bucket)
                        @if($bucket->children)
                        <optgroup label="{{ $bucket->name }}">
                            @foreach($bucket->children as $child)
                            <option value="{{ $child->name }}"
                                {{ request('status') == $child->name ? 'selected' : '' }}>
                                {{ $child->name }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-block d-md-block">
                    <select name="lead_engagement_status" class="form-select">
                        <option value="">All Engagement</option>
                        <option value="hot" {{ request('lead_engagement_status') == 'hot' ? 'selected' : '' }}>Hot</option>
                        <option value="warm" {{ request('lead_engagement_status') == 'warm' ? 'selected' : '' }}>Warm</option>
                        <option value="cold" {{ request('lead_engagement_status') == 'cold' ? 'selected' : '' }}>Cold</option>
                        <option value="dead" {{ request('lead_engagement_status') == 'dead' ? 'selected' : '' }}>Dead</option>
                    </select>
                </div>


                <div class="col-md-3 d-none d-md-block">
                    <select name="owner_id" class="form-select">
                        <option value="">All Owners</option>
                        <option value="null" {{ old('owner_id', request('owner_id')) == 'null' ? 'selected' : '' }}>Unknown</option>
                        @foreach($owners ?? [] as $owner)
                        <option value="{{ $owner->id }}"
                            {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="text" name="country" class="form-control"
                        placeholder="Country"
                        value="{{ request('country') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="text" name="course" class="form-control"
                        placeholder="Course"
                        value="{{ request('course') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="text" name="campaign_name" class="form-control"
                        placeholder="Campaign"
                        value="{{ request('campaign_name') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="text" name="adset_name" class="form-control"
                        placeholder="Adset"
                        value="{{ request('adset_name') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <input type="text" name="ad_name" class="form-control"
                        placeholder="Ad Name"
                        value="{{ request('ad_name') }}">
                </div>

                <div class="col-md-2 d-none d-md-block">
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>

                        @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

               

                <!-- <div class="col-12 col-md-6 p-2 d-flex gap-2 flex-wrap">

                    <button type="submit" name="lead_engagement_status" value=""
                        class="btn btn-sm  {{ request('lead_engagement_status') == '' ? 'btn-primary' : 'btn-light' }}">
                        All
                    </button>

                    <button type="submit" name="lead_engagement_status" value="hot"
                        class="btn btn-sm {{ request('lead_engagement_status') == 'hot' ? 'btn-danger' : 'btn-light' }}">
                         Hot
                    </button>

                    <button type="submit" name="lead_engagement_status" value="warm"
                        class="btn btn-sm {{ request('lead_engagement_status') == 'warm' ? 'btn-warning' : 'btn-light' }}">
                         Warm
                    </button>

                    <button type="submit" name="lead_engagement_status" value="cold"
                        class="btn btn-sm {{ request('lead_engagement_status') == 'cold' ? 'btn-info' : 'btn-light' }}">
                         Cold
                    </button>

                    <button type="submit" name="lead_engagement_status" value="dead"
                        class="btn btn-sm {{ request('lead_engagement_status') == 'dead' ? 'btn-dark' : 'btn-light' }}">
                         Dead
                    </button>

                </div> -->


            </div>

            {{-- ✅ BUTTONS SAME LINE --}}
            <div class="d-flex justify-content-start gap-2 mt-4 border-top pt-3">

                <a href="{{ route('modern.leads.index') }}"
                    class="btn btn-light border text-danger">
                    Reset
                </a>

                <button type="submit" class="btn btn-primary">
                    Filter
                </button>

                {{-- Today --}}
                <a href="{{ route('modern.leads.index', array_merge(request()->query(), ['date_filter' => 'today'])) }}"
                    class="btn {{ request('date_filter') == 'today' ? 'btn-warning text-dark' : 'btn-light border' }}">
                    Today
                </a>

                {{-- Yesterday --}}
                <a href="{{ route('modern.leads.index', array_merge(request()->query(), ['date_filter' => 'yesterday'])) }}"
                    class="btn {{ request('date_filter') == 'yesterday' ? 'btn-warning text-dark' : 'btn-light border' }}">
                    Yesterday
                </a>

            </div>

        </form>

    </div>
</div>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- IMPORT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const fileInput = document.getElementById('importFile');
        const spinner = document.getElementById('import-spinner');

        fileInput.addEventListener('change', function() {

            if (!this.files.length) return;

            const formData = new FormData();
            formData.append('file', this.files[0]);

            spinner.classList.remove('d-none');

            fetch("{{ route('lead.import') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (data.status === "success") {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        const jobId = data.job_id;

                        const interval = setInterval(() => {
                            fetch(`/lead-import-status/${jobId}`)
                                .then(res => res.json())
                                .then(resp => {
                                    if (resp.status === 'success') {
                                        const job = resp.data;

                                        if (job.job_status === 'completed' || job.job_status === 'failed') {
                                            clearInterval(interval);
                                            spinner.classList.add('d-none');
                                        }
                                    }
                                });
                        }, 2000);

                    } else {
                        spinner.classList.add('d-none');
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => {
                    spinner.classList.add('d-none');
                    Swal.fire('Error', 'Something went wrong', 'error');
                });

        });

    });
</script>