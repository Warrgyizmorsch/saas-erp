@extends('shared::layouts.app')

@section('content')
<style>
    :root {
        --premium-blue: #3858f9;
        --premium-dark: #1e293b;
        --premium-light: #f8fafc;
    }
    .employee-profile-wrapper {
        background: var(--premium-light);
        min-height: calc(100vh - 70px);
        padding: 40px 20px;
    }
    .profile-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
        border: none;
    }
    .profile-header-banner {
        background: linear-gradient(135deg, #3858f9 0%, #1e293b 100%);
        height: 160px;
        position: relative;
    }
    .profile-content-header {
        margin-top: -60px;
        padding: 0 40px 30px;
        display: flex;
        align-items: flex-end;
        gap: 30px;
    }
    .profile-photo-container {
        width: 150px;
        height: 150px;
        border-radius: 20px;
        border: 4px solid #fff;
        background: #f1f5f9;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        overflow: hidden;
        flex-shrink: 0;
    }
    .profile-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-title-area {
        padding-bottom: 10px;
        flex-grow: 1;
    }
    .profile-name {
        font-size: 28px;
        font-weight: 800;
        color: var(--premium-dark);
        margin-top: 5px;
    }
    .profile-nav-tabs {
        padding: 0 40px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        gap: 40px;
    }
    .profile-nav-link {
        padding: 20px 0;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        cursor: pointer;
        position: relative;
        border: none;
        background: none;
    }
    .profile-nav-link.active {
        color: var(--premium-blue);
    }
    .profile-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--premium-blue);
        border-radius: 3px 3px 0 0;
    }
    .profile-body {
        padding: 40px;
    }
    .detail-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 20px;
        height: 100%;
        transition: all 0.3s ease;
    }
    .detail-card:hover {
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }
    .detail-label {
        font-size: 11px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .detail-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 15px;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .profile-header-banner {
            height: 120px;
        }
        .profile-content-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 20px 20px;
            margin-top: -60px;
            gap: 15px;
        }
        .profile-photo-container {
            width: 120px;
            height: 120px;
        }
        .profile-title-area {
            width: 100%;
        }
        .profile-title-area .d-flex {
            justify-content: center;
            flex-wrap: wrap;
        }
        .profile-nav-tabs {
            flex-wrap: wrap;
            padding: 0 15px;
            gap: 10px;
            justify-content: center;
        }
        .profile-nav-link {
            padding: 12px 10px;
            font-size: 12px;
            flex-grow: 1;
            text-align: center;
        }
        .profile-body {
            padding: 20px;
        }
        .detail-card {
            margin-bottom: 10px;
        }
        .employee-profile-wrapper {
            padding: 20px 10px;
        }
    }
</style>

<div class="employee-profile-wrapper">
    <div class="container">
        <div class="profile-card">
            <!-- Banner -->
            <div class="profile-header-banner">
                <div class="p-4 d-flex justify-content-end">
                    <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                        <i class="feather-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Content Header -->
            <div class="profile-content-header">
                <div class="profile-photo-container">
                    @if($employee->photo)
                        <img src="{{ asset('storage/' . $employee->photo) }}" alt="">
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center bg-soft-primary text-primary fw-bold fs-1">
                            {{ substr($employee->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="profile-title-area">
                    <span class="badge bg-soft-primary text-primary px-3 rounded-pill fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 1px;">
                        EC-{{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    <h1 class="profile-name">{{ $employee->name }}</h1>
                    <div class="d-flex gap-4 mt-2">
                        <span class="text-muted fw-bold small"><i class="feather-briefcase me-2 text-primary"></i>{{ $employee->designation }}</span>
                        <span class="text-muted fw-bold small"><i class="feather-map-pin me-2 text-primary"></i>{{ ucwords($employee->department) }}</span>
                    </div>
                </div>
                <div class="pb-3 d-flex gap-2">
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">Edit Employee</a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="profile-nav-tabs">
                <button class="profile-nav-link active" onclick="switchAdminTab('personal', this)">Personal Info</button>
                <button class="profile-nav-link" onclick="switchAdminTab('bank', this)">Bank Details</button>
                <button class="profile-nav-link" onclick="switchAdminTab('salary', this)">Salary Package</button>
                <button class="profile-nav-link" onclick="switchAdminTab('security', this)">Login & Security</button>
            </div>

            <!-- Body -->
            <div class="profile-body">
                <!-- PERSONAL INFO -->
                <div id="tab-personal" class="tab-pane-custom active">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Full Name</div>
                                <div class="detail-value">{{ $employee->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Email Address</div>
                                <div class="detail-value">{{ $employee->email ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Mobile Number</div>
                                <div class="detail-value">{{ $employee->mobile_number }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Employee Code</div>
                                <div class="detail-value text-primary">EC-{{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Aadhaar Number</div>
                                <div class="detail-value">{{ $employee->aadhaar_number ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">PAN Number</div>
                                <div class="detail-value">{{ $employee->pan_number ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="detail-card">
                                <div class="detail-label">Residential Address</div>
                                <div class="detail-value">{{ $employee->address ?? 'No address provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BANK DETAILS -->
                <div id="tab-bank" class="tab-pane-custom d-none">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Bank Name</div>
                                <div class="detail-value">{{ $employee->bank_name ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Account Number</div>
                                <div class="detail-value">{{ $employee->account_number ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">IFSC Code</div>
                                <div class="detail-value">{{ $employee->ifsc_code ?? '--' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SALARY PACKAGE -->
                <div id="tab-salary" class="tab-pane-custom d-none">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">Gross Salary</div>
                                <div class="detail-value text-primary fs-4">₹ {{ number_format($employee->basic_salary, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">HRA</div>
                                <div class="detail-value">₹ {{ number_format($employee->hra ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">ESI Status</div>
                                <div class="detail-value">
                                    <span class="badge {{ $employee->esi ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill px-3">
                                        {{ $employee->esi ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-card">
                                <div class="detail-label">PF Status</div>
                                <div class="detail-value">
                                    <span class="badge {{ $employee->pf ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill px-3">
                                        {{ $employee->pf ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECURITY TAB -->
                <div id="tab-security" class="tab-pane-custom d-none">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="detail-card border-danger bg-soft-light">
                                <div class="detail-label text-danger">Current Login Password</div>
                                <div class="detail-value fw-bold" style="letter-spacing: 2px;">{{ $employee->password ?? '********' }}</div>
                                <p class="mt-2 text-muted small">Update this in the edit section to change the employee's login credentials.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchAdminTab(tabId, btn) {
        document.querySelectorAll('.profile-nav-link').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.add('d-none'));
        document.getElementById('tab-' + tabId).classList.remove('d-none');
    }
</script>
@endsection