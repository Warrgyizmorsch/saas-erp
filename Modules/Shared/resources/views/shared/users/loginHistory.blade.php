@extends('shared::layouts.app')

@section('content')

    <main>
        <div>
            <div class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Login History</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Active Users</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="action-links d-flex gap-2">
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Add New</a>
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne">
                            Filter
                        </a>
                    </div>
                </div>
            </div>

            <!-- 🔍 Filter Section -->
            <div id="collapseOne"
                class="accordion-collapse collapse page-header-collapse {{ request('search') || request('role_id') || request('from') || request('to') ? 'show' : '' }}">
                <div class="accordion-body pb-2">
                    <form method="GET" action="{{ route('users.session') }}" class="row g-3">

                        <div class="col-md-3">
                            <label for="role_id" class="form-label">User Role</label>
                            <select name="role_id" id="role_id" class="form-select" data-select2-selector="tag">
                                <option value="">All Roles</option>
                                @foreach ($userRole as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" placeholder="Name or Email"
                                value="{{ request('search') }}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label for="from" class="form-label">From</label>
                            <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label for="to" class="form-label">To</label>
                            <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control">
                        </div>

                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('users.session') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="crm-page-container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card-body">

                        <div class="card stretch stretch-full">

                            <div class="card-header p-0">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" role="tablist">

                                    <li class="nav-item flex-fill border-top">
                                        <a class="nav-link {{ request('tab') != 'activity' ? 'active' : '' }}"
                                            data-bs-toggle="tab" href="#loginHistoryTab">
                                            Login History
                                        </a>
                                    </li>

                                    <li class="nav-item flex-fill border-top">
                                        <a class="nav-link {{ request('tab') == 'activity' ? 'active' : '' }}"
                                            data-bs-toggle="tab" href="#activityHistoryTab">
                                            Activity History
                                        </a>
                                    </li>

                                </ul>
                            </div>

                            <div class="card-body">

                                <div class="tab-content">

                                    <!-- ================= LOGIN HISTORY ================= -->
                                    <div class="tab-pane fade {{ request('tab') != 'activity' ? 'show active' : '' }}"
                                        id="loginHistoryTab">
                                        <h5 class="mb-3">Login History</h5>

                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>User</th>
                                                        <th>Logged In?</th>
                                                        <th>IP</th>
                                                        <th>Device</th>
                                                        <th>Last Login</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @forelse ($users as $user)

                                                        @php
                                                            $session = $sessions[$user->id] ?? null;
                                                            $latestLogin = $user->loginHistories->last();
                                                            $isLoggedIn = false;

                                                            if ($session) {
                                                                $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
                                                                $expiryTime = $lastActivity->copy()->addMinutes(config('session.lifetime'));
                                                                $isLoggedIn = now()->lt($expiryTime);
                                                            }
                                                        @endphp

                                                        <tr>
                                                            <td>{{ $user->name }}</td>

                                                            <td>
                                                                <span
                                                                    class="badge {{ $isLoggedIn ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $isLoggedIn ? 'Yes' : 'No' }}
                                                                </span>
                                                            </td>

                                                            <td>{{ optional($latestLogin)->ip_address ?? '-' }}</td>

                                                            <td>{{ Str::limit(optional($latestLogin)->user_agent, 40) ?? '-' }}
                                                            </td>

                                                            <td>
                                                                {{ optional(optional($latestLogin)->created_at)->diffForHumans() ?? '-' }}
                                                            </td>

                                                            <td>
                                                                <div class="action-links">

                                                                    @if ($isLoggedIn)
                                                                        <form method="POST"
                                                                            action="{{ route('users.logout', $user) }}">
                                                                            @csrf
                                                                            <button type="submit" class="btn-delete"
                                                                                style="font-size:18px; padding:0;"
                                                                                title="Logout User">
                                                                                <i class="fa fa-sign-out"></i>
                                                                            </button>
                                                                        </form>
                                                                    @endif

                                                                    <a href="{{ route('users.history', $user->id) }}"
                                                                        class="btn-history" style="font-size:18px;"
                                                                        title="View History">
                                                                        <i class="fa fa-history"></i>
                                                                    </a>

                                                                    <a href="{{ route('users.leadHistory', $user->id) }}"
                                                                        class="btn-edit" style="font-size:18px;"
                                                                        title="View Lead History">
                                                                        <i class="fa fa-list"></i>
                                                                    </a>

                                                                </div>
                                                            </td>
                                                        </tr>

                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted">
                                                                No users found.
                                                            </td>
                                                        </tr>
                                                    @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        <div class="mt-3 d-flex justify-content-center">
                                            {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
                                        </div>

                                    </div>

                                    <!-- ================= ACTIVITY HISTORY ================= -->
                                    <div class="tab-pane fade {{ request('tab') == 'activity' ? 'show active' : '' }}"
                                        id="activityHistoryTab">
                                        <h5 class="mb-3">Activity History</h5>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">

                                                <thead>
                                                    <tr>
                                                        <th style="width:80px;">Sr No</th>
                                                        <th>User</th>
                                                        <th>Activity</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @forelse ($activityLogs as $index => $log)
                                                        <tr>
                                                            <!-- Serial Number -->
                                                            <td>
                                                                {{ $activityLogs->firstItem() + $index }}
                                                            </td>

                                                            <!-- User Name -->
                                                            <td>
                                                                {{ optional($log->user)->name ?? '-' }}
                                                            </td>

                                                            <!-- Activity -->
                                                            <td>
                                                                {{-- Agar future me activity column add karo --}}
                                                                @if(isset($log->activity))
                                                                    {{ $log->activity }}
                                                                @else
                                                                    {{ gmdate('H:i:s', $log->active_seconds) }}
                                                                @endif
                                                            </td>


                                                            <!-- Date -->
                                                            <td>
                                                                {{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}
                                                                <br>
                                                                <small class="text-muted">
                                                                    {{ optional($log->created_at)->diffForHumans() }}
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-4">
                                                                <i class="fa fa-database me-1"></i>
                                                                No activity history found
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>

                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

                                            <!-- Showing entries -->
                                            <div class="text-muted small">
                                                Showing
                                                <strong>{{ $activityLogs->firstItem() }}</strong>
                                                to
                                                <strong>{{ $activityLogs->lastItem() }}</strong>
                                                of
                                                <strong>{{ $activityLogs->total() }}</strong> entries
                                            </div>

                                            <!-- Links -->
                                            <div>
                                                {{ $activityLogs->appends(array_merge(request()->query(), ['tab' => 'activity']))->links('pagination::bootstrap-4') }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection