@extends('shared::layouts.app')

@section('content')
<div class="container-fluid px-0" style="background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif;">
    <!-- Top Header -->
    <div class="px-4 py-3 bg-white border-bottom shadow-sm mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div>
                    <h5 class="fw-bold mb-0" style="color: #334155;">Leave Management</h5>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted small">Home</a></li>
                            <li class="breadcrumb-item active small fw-bold" style="color: #3858f9;" aria-current="page">Leave Balance List</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 pe-2">
                <!-- Standard Search Bar -->
                <div class="input-group d-none d-md-flex" style="width: 250px;">
                    <span class="input-group-text bg-light border-0"><i class="feather-search text-muted"></i></span>
                    <input type="text" id="balanceSearch" class="form-control bg-light border-0 shadow-none" placeholder="Search employee..." onkeyup="filterBalances()">
                </div>
                
                <a href="{{ route('leave.allotment') }}" class="btn btn-primary fw-bold small shadow-sm d-flex align-items-center" style="background: #3858f9; border: none; border-radius: 8px; height: 38px;">
                    <i class="feather-plus-circle me-2"></i> ADD ALLOTMENT
                </a>
            </div>
        </div>
    </div>

    <div class="px-4">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white; overflow: hidden;">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Employee Leave Balances</h6>
                        <p class="text-muted small mb-0">Summary of total leaves allotted and taken per employee.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="balanceTable">
                        <thead class="bg-light">
                            <tr class="small text-muted fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                <th class="ps-4 py-3" style="width: 80px;">Sr.No</th>
                                <th>Employee</th>
                                <th class="text-center">Leave Taken / Allotted</th>
                                <th class="text-center pe-4" style="width: 200px;">Current Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($balances as $index => $b)
                                <tr class="border-bottom">
                                    <td class="ps-4">
                                        <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar-text avatar-md bg-soft-primary text-primary fw-bold">
                                                {{ substr($b->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark">{{ $b->name }}</span>
                                                <span class="text-muted small" style="font-size: 11px;">#EC{{ str_pad($b->id, 4, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <span class="fw-bold text-danger">{{ number_format($b->total_taken, 1) }}</span>
                                            <span class="text-muted">/</span>
                                            <span class="fw-bold text-primary">{{ number_format($b->total_allotted, 1) }}</span>
                                        </div>
                                        <div class="progress mt-2" style="height: 4px; border-radius: 10px; width: 100px; margin: 0 auto;">
                                            @php 
                                                $percent = $b->total_allotted > 0 ? min(($b->total_taken / $b->total_allotted) * 100, 100) : 0;
                                            @endphp
                                            <div class="progress-bar {{ $percent > 80 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="badge {{ $b->balance >= 0 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} fw-bold px-3 py-2" style="font-size: 13px; border-radius: 8px;">
                                            {{ number_format($b->balance, 1) }} Days
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="feather-info fs-1 opacity-25"></i>
                                        <p class="mt-2 fw-bold">No leave balances found.</p>
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

<script>
    function filterBalances() {
        const input = document.getElementById('balanceSearch');
        const filter = input.value.toLowerCase();
        const rows = document.getElementById('balanceTable').getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            const text = rows[i].getElementsByTagName('td')[1].innerText.toLowerCase();
            rows[i].style.display = text.indexOf(filter) > -1 ? '' : 'none';
        }
    }
</script>

<style>
    .bg-soft-primary { background-color: rgba(56, 88, 249, 0.08); }
    .bg-soft-success { background-color: rgba(34, 197, 94, 0.08); }
    .bg-soft-danger { background-color: rgba(239, 68, 68, 0.08); }
    .avatar-md { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
</style>
@endsection
