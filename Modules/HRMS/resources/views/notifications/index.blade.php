@extends('shared::layouts.app')

@section('title', 'All Notifications')

@section('content')
<div class="main-content pt-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4 mt-1">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-icon bg-soft-primary text-primary me-3" style="width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="feather-bell fs-5"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0">Notifications Center</h3>
                <p class="text-muted small mb-0">Manage and view all your system alerts</p>
            </div>
        </div>
        <div class="page-header-right">
            <a href="javascript:void(0);" class="btn btn-soft-primary fw-bold px-4 py-2 d-flex align-items-center shadow-sm" style="border-radius: 10px;">
                <i class="feather-check-square me-2"></i> Mark All as Read
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($notifications as $item)
                    @php
                        $isPayroll = isset($item->remarks);
                        // Hide other employees' payroll comments for non-admin
                        if ($isPayroll && !$isAdmin) {
                            if ($item->employee_id != auth()->user()->employee_id) {
                                continue; // skip this item
                            }
                        }
                        $emp = (!$isAdmin) ? $item->employee : auth()->user()->employee;
                        $photo = ($emp && $emp->photo) ? asset('storage/' . $emp->photo) : null;
                    @endphp
                    <div class="list-group-item p-4 border-bottom border-light">
                        <div class="d-flex align-items-start">
                            @if($photo)
                                <img src="{{ $photo }}" alt="" class="rounded-circle me-3 border shadow-sm" style="width: 45px; height: 45px; object-fit: cover;" />
                            @else
                                <div class="avatar-text avatar-md bg-soft-primary text-primary rounded-circle me-3 border shadow-sm d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px; font-size: 16px;">
                                    {{ substr($emp->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold text-dark mb-0">
                                        @if($isPayroll)
                                            Payroll Comment
                                        @else
                                            @if($isAdmin)
                                                New Leave Application
                                            @else
                                                Leave Status Updated
                                            @endif
                                        @endif
                                    </h6>
                                    <!-- <span class="text-muted small fw-medium">
                                        <i class="feather-clock me-1"></i>
                                        {{ $item->updated_at->diffForHumans() }}
                                    </span> -->
                                    <div>
                                        @if($isPayroll)
                                            <i class="feather-message-square me-1"></i>
                                            {{ $item->updated_at->diffForHumans() }}
                                        @else
                                            <i class="feather-clock me-1"></i>
                                            {{ $item->updated_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                                <p class="text-muted mb-3" style="font-size: 14px; line-height: 1.6;">
                                    @if($isPayroll)
                                        <span class="fw-semibold text-dark">{{ $item->employee->name ?? 'Someone' }}</span>
                                        commented:
                                        <span class="text-muted">"{{ $item->remarks }}"</span>
                                    @else
                                        @if($isAdmin)
                                            <span class="fw-semibold text-dark">{{ $emp->name ?? 'Someone' }}</span> has applied for a 
                                            <span class="badge bg-soft-info text-info">{{ $item->leave_type }}</span> leave starting from 
                                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($item->start_date)->format('d M, Y') }}</span>. 
                                            Reason: "{{ $item->reason }}".
                                        @else
                                            Your leave application for <span class="badge bg-soft-info text-info">{{ $item->leave_type }}</span> has been 
                                            <span class="badge rounded-pill fw-bold {{ in_array(strtolower($item->status), ['approved', 'Approved']) ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}" style="font-size: 10px;">
                                                {{ strtoupper($item->status) }}
                                            </span>.
                                        @endif
                                    @endif
                                </p>
                                <div class="hstack gap-2">
                                    <a href="{{ $isPayroll ? route('payroll.index') : route('leave.history') }}" 
                                    class="btn btn-sm btn-primary border-0 rounded-pill px-3 fw-bold" 
                                    style="font-size: 11px;">
                                        View Details
                                    </a>
                                    @if($isAdmin)
                                        <button class="btn btn-sm btn-soft-success border-0 rounded-pill px-3 fw-bold" style="font-size: 11px;">Quick Approve</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-5 text-center">
                        <div class="bg-soft-light text-muted d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px;">
                            <i class="feather-bell-off fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark">No Notifications Found</h5>
                        <p class="text-muted mx-auto" style="max-width: 300px;">When you have new updates and system alerts, they will appear here.</p>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4 fw-bold mt-2 shadow-sm">Back to Dashboard</a>
                    </div>
                @endforelse
            </div>
            
            
        </div>
    </div>
</div>
@endsection

