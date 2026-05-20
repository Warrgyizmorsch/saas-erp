@extends('layouts.app')

@section('content')

    <style>
        #warrLeadList thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        #warrLeadList tbody td:first-child,
        #warrLeadList thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 20;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            background: #fff;
        }

        .table-responsive { overflow-x: auto; }

        .page-url-col {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .page-url-link {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .data-column {
            min-width: 250px;
            max-width: 250px;
            white-space: normal !important;
        }

        .warr-comment-cell {
            cursor: pointer;
        }

        .warr-comment-input {
            width: 100%;
            min-height: 38px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            outline: none;
        }

    </style>

    @php
$filtersApplied =
    request('search') ||
    request('from') ||
    request('to') ||
    request('source') ||
    request('status');
    @endphp

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">
                    Warr Leads
                    
                </h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Warr Leads</li>
            </ul>
            <span class="badge bg-primary px-2 py-1" style="font-size: 0.8rem; margin-left: 5px;">
                Total: {{ $totalLeadsCount }} | Showing: {{ $filteredLeadCount }}
            </span>
        </div>

        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>

                <button class="btn btn-light-brand" type="button"
                        data-bs-toggle="collapse" data-bs-target="#warrLeadFilters">
                    <i class="feather-filter me-2"></i> Filters
                </button>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            <strong>Whoops!</strong> There were some problems with your input:
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER PANEL --}}
    <div id="warrLeadFilters"
         class="accordion-collapse collapse page-header-collapse {{ $filtersApplied ? 'show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('warr-leads.index') }}" class="row g-3 mb-4">

                <div class="col-md-3">
                    <label class="form-label">Search (Name/Email/Mobile)</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Page Url</label>
                    <select name="source" class="form-control" data-select2-selector="tag">
                        <option value="">All Url</option>
                        @foreach(($page_url ?? []) as $src)
                            <option value="{{ $src }}" {{ request('page_url') == $src ? 'selected' : '' }}>
                                {{ $src }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" data-select2-selector="tag">
                        <option value="">All Status</option>
                        @foreach(($statuses ?? []) as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ ucwords($st) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('warr-leads.index') }}" class="btn btn-danger">Reset</a>
                </div>

            </form>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="warrLeadList">
                                <thead>
                                <tr>
                                    <th class="bg-white">#</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Date</th>
                                    <th>Message by User</th>
                                    <th>Service Categories</th>
                                    <th>Status</th>
                                    <th>Comment</th>
                                    <th>Source</th>
                                    <th>Page Url</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td class="bg-white">{{ $loop->iteration + ($leads->currentPage() - 1) * $leads->perPage() }}</td>
                                        <td>{{ $lead->name ?? 'N/A' }}</td>
                                        <td>{{ $lead->company_name ?? '-' }}</td>
                                        <td>{{ $lead->email ?? 'N/A' }}</td>
                                        <td>{{ $lead->mobile_no ?? '-' }}</td>
                                        <td>{{ $lead->created_at ? $lead->created_at->format('d M Y') : 'N/A' }}</td>
                                        <td class="data-column">{{ $lead->message ?? '-' }}</td>
                                        <td class="data-column">{{ $lead->service_categories ?? '-' }}</td>
                                        <td>
                                            <form action="{{ route('warr-leads.updateWarrLead', $lead->id) }}"
                                                method="POST"
                                                class="warr-status-form">
                                                @csrf
                                                @method('PUT')

                                                <select name="status"
                                                        class="form-control warr-status-select"
                                                        data-select2-selector="tag">
                                                    <option value="new" {{ ($lead->status ?? 'new') === 'new' ? 'selected' : '' }}>New</option>
                                                    <option value="hold" {{ ($lead->status ?? '') === 'hold' ? 'selected' : '' }}>Hold</option>
                                                    <option value="executed" {{ ($lead->status ?? '') === 'executed' ? 'selected' : '' }}>Executed</option>
                                                    <option value="dead" {{ ($lead->status ?? '') === 'dead' ? 'selected' : '' }}>Dead</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="data-column warr-comment-cell"
                                            data-id="{{ $lead->id }}"
                                            data-url="{{ route('warr-leads.updateWarrLead', $lead->id) }}"
                                            title="Double click to edit">
                                            <span class="warr-comment-text">
                                                {{ $lead->comment ?? '-' }}
                                            </span>
                                        </td>

                                        <td class="data-column">{{ $lead->source ?? '-' }}</td>

                                        <td class="page-url-col">
                                            @if(!empty($lead->page_url))
                                                @php
        $cleanUrl = strtok(trim($lead->page_url), '?');
                                                @endphp

                                                @if(filter_var($cleanUrl, FILTER_VALIDATE_URL))
                                                    <a href="{{ $cleanUrl }}" target="_blank" title="{{ $cleanUrl }}" class="page-url-link">
                                                        {{ $cleanUrl }}
                                                    </a>
                                                @else
                                                    {{ $cleanUrl }}
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No leads found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="m-4" style="display:flex;justify-content:center;">
                            {{ $leads->links('pagination::bootstrap-4') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
  $(document).ready(function () {

    // ✅ Update Status (dropdown change)
    $(document).on("change", ".warr-status-select", function () {
      const form = $(this).closest("form");
      const url  = form.attr("action");
      const data = form.serialize(); // includes _token + _method + status

      $.ajax({
        url: url,
        type: "POST", // PUT spoofed by _method
        data: data,
        success: function (res) {
          toastr.success(res.message || "Status updated!");
        },
        error: function (xhr) {
          toastr.error(xhr.responseJSON?.message || "Status update failed!");
        }
      });
    });

    // ✅ Double click to edit comment
    $(document).on("dblclick", ".warr-comment-cell", function () {
      const td = $(this);

      // prevent multiple editors
      if (td.find("input, textarea").length) return;

      const currentText = td.find(".warr-comment-text").text().trim();
      const url = td.data("url");

      const input = $(`<textarea class="warr-comment-input" rows="2"></textarea>`);
      input.val(currentText === "-" ? "" : currentText);

      td.data("old", input.val()); // store old value
      td.html(input);
      input.trigger("focus");

      // Save on Enter (without shift), Cancel on Escape
      input.on("keydown", function (e) {
        if (e.key === "Enter" && !e.shiftKey) {
          e.preventDefault();
          saveComment(td, url, input.val());
        }
        if (e.key === "Escape") {
          const old = (td.data("old") || "").trim();
          td.html(`<span class="warr-comment-text">${old || "-"}</span>`);
        }
      });

      // Save on blur
      input.on("blur", function () {
        saveComment(td, url, input.val());
      });
    });

    function saveComment(td, url, newValue) {
      const oldValue = (td.data("old") || "").trim();
      const nextValue = (newValue || "").trim();

      // If unchanged, restore only
      if (nextValue === oldValue) {
        td.html(`<span class="warr-comment-text">${nextValue || "-"}</span>`);
        return;
      }

      $.ajax({
        url: url,
        type: "POST", // PUT spoofed by _method
        data: {
          _token: "{{ csrf_token() }}",
          _method: "PUT",
          comment: nextValue
        },
        success: function (res) {
          toastr.success(res.message || "Comment updated!");
          const updated = (res.comment || nextValue || "").trim();
          td.html(`<span class="warr-comment-text">${updated || "-"}</span>`);
        },
        error: function (xhr) {
          toastr.error(xhr.responseJSON?.message || "Comment update failed!");
          td.html(`<span class="warr-comment-text">${oldValue || "-"}</span>`);
        }
      });
    }

  });
</script>

@endsection
