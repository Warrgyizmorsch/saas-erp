@extends('shared::layouts.app')

@section('title', 'SaaS ERP • Central Administration')

@section('content')
<div class="crm-page-container p-4">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="page-header-left">
            <div class="page-header-title">
                <h3 class="fw-bold mb-1" style="color: #0f172a; font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">Central Administration</h3>
            </div>
            <ul class="breadcrumb mb-0 bg-transparent p-0 fs-12 text-muted">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">System Control Center</li>
            </ul>
        </div>

        <div class="page-header-right">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light-primary text-primary border border-primary-dashed px-3 py-2 fs-12 rounded-3 d-flex align-items-center gap-2">
                    <i class="feather-calendar"></i>
                    {{ now()->format('l, F d, Y') }}
                </span>
                <span class="badge bg-success-soft text-success px-3 py-2 fs-12 rounded-3 border border-success-dashed d-flex align-items-center gap-1">
                    <span class="d-inline-block bg-success rounded-circle me-1 spinner-grow spinner-grow-sm" style="width: 6px; height: 6px;" role="status"></span>
                    Operational
                </span>
            </div>
        </div>
    </div>

    <!-- Tenant & System Overview Row -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Tenant Information -->
        <div class="col-xxl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all" style="background: linear-gradient(135deg, #1e3a8a, #0f172a);">
                <div class="card-body p-4 text-white position-relative overflow-hidden">
                    <div class="position-absolute end-0 bottom-0 opacity-10" style="font-size: 120px; line-height: 1; transform: translate(20px, 20px);">
                        <i class="feather-globe"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar-text avatar-md bg-black rounded-3 text-white">
                            <i class="feather-home fs-20"></i>
                        </div>
                        <span class="badge bg-white-20 text-white rounded-pill px-2.5 py-1 fs-11">Tenant Profile</span>
                    </div>
                    <h6 class="text-white-50 mb-1 fs-12 text-uppercase fw-bold tracking-wider">Active Workspace</h6>
                    <h3 class="fw-bold mb-2 text-white">{{ ucfirst($tenant->id) }}</h3>
                    <p class="fs-12 text-white-70 mb-0 d-flex align-items-center gap-1">
                        <i class="feather-external-link"></i>
                        <a href="/saas-erp/public" target="_blank" class="text-white text-decoration-none hover-underline">profile</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Total System Users -->
        <div class="col-xxl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all bg-white">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <div class="position-absolute end-0 bottom-0 opacity-5 text-primary" style="font-size: 120px; line-height: 1; transform: translate(20px, 20px);">
                        <i class="feather-users"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar-text avatar-md bg-primary-soft text-primary rounded-3">
                            <i class="feather-users fs-20"></i>
                        </div>
                        <span class="badge bg-primary-soft text-primary rounded-pill px-2.5 py-1 fs-11">User Count</span>
                    </div>
                    <h6 class="text-muted mb-1 fs-12 text-uppercase fw-bold tracking-wider">Registered Users</h6>
                    <h3 class="fw-bold mb-1 text-dark">{{ $totalUsers }} Total</h3>
                    <p class="fs-12 text-success mb-0 d-flex align-items-center gap-1">
                        <i class="feather-user-check"></i>
                        <span>{{ $activeUsersCount }} Active Accounts</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 3: Active Live Sessions -->
        <div class="col-xxl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all bg-white">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <div class="position-absolute end-0 bottom-0 opacity-5 text-warning" style="font-size: 120px; line-height: 1; transform: translate(20px, 20px);">
                        <i class="feather-activity"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar-text avatar-md bg-warning-soft text-warning rounded-3">
                            <i class="feather-activity fs-20"></i>
                        </div>
                        <span class="badge bg-warning-soft text-warning rounded-pill px-2.5 py-1 fs-11">Live Traffic</span>
                    </div>
                    <h6 class="text-muted mb-1 fs-12 text-uppercase fw-bold tracking-wider">Live Sessions</h6>
                    <h3 class="fw-bold mb-1 text-dark">{{ $activeSessions }} Active</h3>
                    <p class="fs-12 text-muted mb-0 d-flex align-items-center gap-1">
                        <i class="feather-clock"></i>
                        <span>Real-time authenticated state</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 4: Tenant Creation and Status -->
        <div class="col-xxl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all bg-white">
                <div class="card-body p-4 position-relative overflow-hidden">
                    <div class="position-absolute end-0 bottom-0 opacity-5 text-success" style="font-size: 120px; line-height: 1; transform: translate(20px, 20px);">
                        <i class="feather-shield-check"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="avatar-text avatar-md bg-success-soft text-success rounded-3">
                            <i class="feather-shield fs-20"></i>
                        </div>
                        <span class="badge bg-success-soft text-success rounded-pill px-2.5 py-1 fs-11">Subscription</span>
                    </div>
                    <h6 class="text-muted mb-1 fs-12 text-uppercase fw-bold tracking-wider">Operational Status</h6>
                    <h3 class="fw-bold mb-1 text-success d-flex align-items-center gap-2">
                        <span>Active SaaS</span>
                        <span class="d-inline-block bg-success rounded-circle spinner-grow spinner-grow-sm" style="width: 8px; height: 8px; animation-duration: 1.5s;" role="status"></span>
                    </h3>
                    <p class="fs-12 text-muted mb-0 d-flex align-items-center gap-1">
                        <i class="feather-calendar"></i>
                        <span>Since: {{ $tenant->created_at ? $tenant->created_at->format('M d, Y') : now()->format('M d, Y') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Active ERP Modules Grid -->
    <h5 class="fw-bold mb-3 text-dark mt-4 d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;">
        <i class="feather-package text-primary"></i> ERP Module License Configuration
    </h5>
    <div class="row g-4 mb-4">
        <!-- CRM Module Card -->
        <div class="col-lg-4 col-md-6">
            @if($isCrmEnabled)
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all position-relative" style="border-top: 4px solid #2563eb !important;">
                    <div class="card-body p-4 d-flex flex-column h-100">
                        <div class="hstack justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-text avatar-sm bg-primary-soft text-primary rounded-3">
                                    <i class="feather-users fs-16"></i>
                                </div>
                                <h5 class="fw-bold mb-0 text-dark">Customer Relations</h5>
                            </div>
                            <span class="badge bg-success-soft text-success border border-success-dashed px-2 py-1 fs-10 rounded-pill">
                                <span class="d-inline-block bg-success rounded-circle me-1" style="width: 5px; height: 5px;"></span> Enabled
                            </span>
                        </div>
                        <p class="text-muted fs-12 mb-4">Centralized client onboarding, interactive pipelines, lead buckets, lead history, and communication logs.</p>
                        
                        <div class="border border-dashed border-gray-2 px-3 py-2 rounded-3 bg-light-soft mb-4 mt-auto">
                            <div class="hstack justify-content-between">
                                <span class="fs-12 text-muted fw-medium">Active Leads</span>
                                <span class="fs-13 fw-bold text-dark">{{ $crmLeadsCount }} Leads</span>
                            </div>
                        </div>

                        <a href="{{ route('crm.dashboard') }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1 py-2 font-medium transition-all shadow-sm">
                            <span>Open CRM Suite</span>
                            <i class="feather-arrow-right fs-14"></i>
                        </a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light-soft opacity-75" style="border-top: 4px solid #94a3b8 !important;">
                    <div class="card-body p-4 d-flex flex-column h-100 text-center">
                        <div class="hstack justify-content-center mb-3">
                            <div class="avatar-text avatar-lg bg-secondary-soft text-secondary rounded-circle">
                                <i class="feather-lock fs-24"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-muted mb-1">CRM Module</h5>
                        <span class="badge bg-secondary-soft text-secondary mx-auto mb-3">Licensed Plan: Inactive</span>
                        <p class="text-muted fs-12 mb-4 mt-auto">Enable Lead management, Status control, university/subject profiling by activating CRM package.</p>
                        <button class="btn btn-outline-secondary w-100 disabled py-2 fs-12 fw-medium">Unlock License</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Inventory Module Card -->
        <div class="col-lg-4 col-md-6">
            @if($isInventoryEnabled)
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all position-relative" style="border-top: 4px solid #9333ea !important;">
                    <div class="card-body p-4 d-flex flex-column h-100">
                        <div class="hstack justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-text avatar-sm bg-purple-soft text-purple rounded-3" style="color: #9333ea; background: rgba(147, 51, 234, 0.1);">
                                    <i class="feather-box fs-16"></i>
                                </div>
                                <h5 class="fw-bold mb-0 text-dark">Inventory Control</h5>
                            </div>
                            <span class="badge bg-success-soft text-success border border-success-dashed px-2 py-1 fs-10 rounded-pill">
                                <span class="d-inline-block bg-success rounded-circle me-1" style="width: 5px; height: 5px;"></span> Enabled
                            </span>
                        </div>
                        <p class="text-muted fs-12 mb-4">Stock control, item catalogs, multiple warehouse logistics, purchase pipelines, and real-time alerts.</p>
                        
                        <div class="border border-dashed border-gray-2 px-3 py-2 rounded-3 bg-light-soft mb-4 mt-auto">
                            <div class="hstack justify-content-between">
                                <span class="fs-12 text-muted fw-medium">Stock Catalog</span>
                                <span class="fs-13 fw-bold text-dark">142 Items</span>
                            </div>
                        </div>

                        <a href="{{ url('/inventory') }}" class="btn w-100 d-flex align-items-center justify-content-center gap-1 py-2 font-medium text-white transition-all shadow-sm" style="background: #9333ea;">
                            <span>Manage Stock</span>
                            <i class="feather-arrow-right fs-14"></i>
                        </a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light-soft opacity-75" style="border-top: 4px solid #94a3b8 !important;">
                    <div class="card-body p-4 d-flex flex-column h-100 text-center">
                        <div class="hstack justify-content-center mb-3">
                            <div class="avatar-text avatar-lg bg-secondary-soft text-secondary rounded-circle">
                                <i class="feather-lock fs-24"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-muted mb-1">Inventory Module</h5>
                        <span class="badge bg-secondary-soft text-secondary mx-auto mb-3">Licensed Plan: Inactive</span>
                        <p class="text-muted fs-12 mb-4 mt-auto">Enable stock controls, warehouse logs, tracking SKU codes, and suppliers registry.</p>
                        <button class="btn btn-outline-secondary w-100 disabled py-2 fs-12 fw-medium">Unlock License</button>
                    </div>
                </div>
            @endif
        </div>

        <!-- HRMS Module Card -->
        <div class="col-lg-4 col-md-6">
            @if($isHrmsEnabled)
                <div class="card border-0 shadow-sm rounded-4 h-100 hover-shadow transition-all position-relative" style="border-top: 4px solid #0d9488 !important;">
                    <div class="card-body p-4 d-flex flex-column h-100">
                        <div class="hstack justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-text avatar-sm text-teal rounded-3" style="color: #0d9488; background: rgba(13, 148, 136, 0.1);">
                                    <i class="feather-briefcase fs-16"></i>
                                </div>
                                <h5 class="fw-bold mb-0 text-dark">HR Management</h5>
                            </div>
                            <span class="badge bg-success-soft text-success border border-success-dashed px-2 py-1 fs-10 rounded-pill">
                                <span class="d-inline-block bg-success rounded-circle me-1" style="width: 5px; height: 5px;"></span> Enabled
                            </span>
                        </div>
                        <p class="text-muted fs-12 mb-4">Employee tracking, department configurations, attendance records, smart payroll, and leave allocations.</p>
                        
                        <div class="border border-dashed border-gray-2 px-3 py-2 rounded-3 bg-light-soft mb-4 mt-auto">
                            <div class="hstack justify-content-between">
                                <span class="fs-12 text-muted fw-medium">Staff Profiles</span>
                                <span class="fs-13 fw-bold text-dark">24 Active</span>
                            </div>
                        </div>

                        <a href="{{ url('/hrms') }}" class="btn w-100 d-flex align-items-center justify-content-center gap-1 py-2 font-medium text-white transition-all shadow-sm" style="background: #0d9488;">
                            <span>Manage HR Suite</span>
                            <i class="feather-arrow-right fs-14"></i>
                        </a>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-light-soft opacity-75" style="border-top: 4px solid #94a3b8 !important;">
                    <div class="card-body p-4 d-flex flex-column h-100 text-center">
                        <div class="hstack justify-content-center mb-3">
                            <div class="avatar-text avatar-lg bg-secondary-soft text-secondary rounded-circle">
                                <i class="feather-lock fs-24"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-muted mb-1">HRMS Module</h5>
                        <span class="badge bg-secondary-soft text-secondary mx-auto mb-3">Licensed Plan: Inactive</span>
                        <p class="text-muted fs-12 mb-4 mt-auto">Enable staff listings, check-in duty slots, timesheets, and basic payroll models.</p>
                        <button class="btn btn-outline-secondary w-100 disabled py-2 fs-12 fw-medium">Unlock License</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Middle Split Section: Performance Chart and Recent Activity -->
    <div class="row g-4 mb-4">
        <!-- Interactive Chart Widget -->
        <div class="col-xxl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 bg-transparent px-4 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">System Activity Chart</h5>
                        <p class="text-muted fs-12 mb-0">Overview of user active sessions and work log performance</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon btn-light-soft rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="feather-more-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item fs-12" href="javascript:void(0);"><i class="feather-refresh-cw me-2"></i>Reload Data</a></li>
                            <li><a class="dropdown-item fs-12" href="javascript:void(0);"><i class="feather-download me-2"></i>Export Excel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="system-activity-apex-chart" style="min-height: 310px;"></div>
                </div>
            </div>
        </div>

        <!-- Recent Active Users List -->
        <div class="col-xxl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header border-0 bg-transparent px-4 pt-4 pb-0">
                    <h5 class="card-title fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">System Administrators</h5>
                    <p class="text-muted fs-12 mb-0">Active control delegates on this tenant</p>
                </div>
                <div class="card-body p-4">
                    <div class="vstack gap-3">
                        @forelse($recentUsers as $usr)
                            <div class="d-flex align-items-center justify-content-between border border-dashed border-gray-3 p-3 rounded-3 hover-shadow-sm transition-all">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-image avatar-md bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-14 border border-primary-dashed">
                                        @if($usr->image)
                                            <img src="{{ asset('storage/' . $usr->image) }}" class="rounded-circle w-100 h-100 object-fit-cover" alt="">
                                        @else
                                            {{ strtoupper(substr($usr->name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark fs-13">{{ $usr->name }}</h6>
                                        <span class="fs-11 text-muted">{{ $usr->email }}</span>
                                    </div>
                                </div>
                                <span class="badge rounded-pill px-2.5 py-1 fs-10 fw-semibold {{ $usr->role_id == 1 ? 'bg-danger-soft text-danger' : 'bg-primary-soft text-primary' }}">
                                    {{ $usr->role ? $usr->role->name : 'Staff' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="feather-users fs-40 text-muted mb-2"></i>
                                <p class="text-muted mb-0 fs-12">No active users logged</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Logins Table -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header border-0 bg-transparent px-4 pt-4 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">Live Login Logs</h5>
                        <p class="text-muted fs-12 mb-0">Monitoring active network endpoints and authentication traces</p>
                    </div>
                    <a href="{{ route('users.session') }}" class="btn btn-light-soft text-primary fs-12 py-1.5 px-3 rounded-3 d-flex align-items-center gap-1 hover-shadow-sm transition-all fw-medium">
                        <span>All Sessions</span>
                        <i class="feather-chevron-right fs-14"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="border-color: #f1f5f9;">
                            <thead class="bg-light-soft text-secondary uppercase border-0">
                                <tr>
                                    <th class="ps-4 border-0 fs-11 text-start fw-bold uppercase tracking-wider">User</th>
                                    <th class="border-0 fs-11 fw-bold uppercase tracking-wider">IP Address</th>
                                    <th class="border-0 fs-11 fw-bold uppercase tracking-wider">Platform / Browser</th>
                                    <th class="border-0 fs-11 fw-bold uppercase tracking-wider">Login Timestamp</th>
                                    <th class="pe-4 border-0 fs-11 fw-bold uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogins as $login)
                                    <tr>
                                        <td class="ps-4 text-start">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-text avatar-sm bg-light-primary text-primary rounded-circle fw-bold fs-12 d-flex align-items-center justify-content-center">
                                                    {{ strtoupper(substr(optional($login->user)->name ?? 'US', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark fs-13">{{ optional($login->user)->name ?? 'Unknown User' }}</h6>
                                                    <span class="fs-11 text-muted">{{ optional($login->user)->email ?? 'no-email@localhost' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-monospace fs-12 text-secondary">{{ $login->ip_address }}</td>
                                        <td class="text-center text-muted fs-12">
                                            @php
                                                $agent = $login->user_agent;
                                                $browser = 'Browser';
                                                if (str_contains($agent, 'Chrome')) $browser = 'Google Chrome';
                                                elseif (str_contains($agent, 'Firefox')) $browser = 'Mozilla Firefox';
                                                elseif (str_contains($agent, 'Safari')) $browser = 'Apple Safari';
                                                elseif (str_contains($agent, 'Edge')) $browser = 'Microsoft Edge';

                                                $os = 'OS';
                                                if (str_contains($agent, 'Windows')) $os = 'Windows';
                                                elseif (str_contains($agent, 'Macintosh')) $os = 'Mac OS';
                                                elseif (str_contains($agent, 'Linux')) $os = 'Linux';
                                                elseif (str_contains($agent, 'Android')) $os = 'Android';
                                                elseif (str_contains($agent, 'iPhone')) $os = 'iOS';
                                            @endphp
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="fw-medium text-dark">{{ $browser }}</span>
                                                <span class="fs-10 text-muted">{{ $os }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center fs-12 text-secondary">
                                            {{ $login->created_at ? $login->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                        </td>
                                        <td class="pe-4 text-center">
                                            @if(!$login->logout_at)
                                                <span class="badge bg-success-soft text-success px-2.5 py-1 fs-11 rounded-pill d-inline-flex align-items-center gap-1">
                                                    <span class="d-inline-block bg-success rounded-circle spinner-grow spinner-grow-sm" style="width: 5px; height: 5px;" role="status"></span>
                                                    Active Session
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-soft text-secondary px-2.5 py-1 fs-11 rounded-pill">
                                                    Logged Out
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted fs-12">
                                            <i class="feather-activity fs-30 text-muted mb-2"></i>
                                            <p class="mb-0">No active login signatures found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                height: 310,
                type: 'area',
                toolbar: {
                    show: false
                },
                sparkline: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [{
                name: 'System Logins',
                data: [15, 30, 20, 45, 38, 55, 70]
            }, {
                name: 'Active Duty Hours',
                data: [10, 22, 18, 35, 30, 42, 50]
            }],
            xaxis: {
                categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: '11px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }
            },
            colors: ['#2563eb', '#10b981'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: {
                    colors: '#64748b',
                    useSeriesColors: false
                },
                markers: {
                    width: 8,
                    height: 8,
                    radius: 12
                }
            },
            tooltip: {
                theme: 'light',
                x: {
                    show: true
                }
            }
        };

        var chartEl = document.querySelector("#system-activity-apex-chart");
        if (chartEl) {
            var chart = new ApexCharts(chartEl, options);
            chart.render();
        }
    });
</script>
@endpush