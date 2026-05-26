@extends('shared::layouts.app')

@section('content')
    <!-- Feather Icons CDN for redundancy -->
    <script src="https://unpkg.com/feather-icons"></script>

    <div class="container-fluid px-0" style="background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif;">
        <!-- Top Header -->
        <div class="px-4 py-3 bg-white border-bottom shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0" style="color: #334155;">Leave Management</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#"
                                        class="text-decoration-none text-muted small">Home</a></li>
                                <li class="breadcrumb-item active small fw-bold" style="color: #3858f9;"
                                    aria-current="page">Leave Allotment</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="wghrm-search-container d-flex align-items-center"
                    style="width: 250px; background: #f1f5f9; border-radius: 10px; border: 1px solid #e2e8f0; height: 40px; padding: 0 15px; transition: all 0.3s ease;">
                    <i class="feather-search text-muted" style="font-size: 14px;"></i>
                    <input type="text" id="teamBalanceSearch" onkeyup="filterEmployees()" placeholder="Search team member..."
                        style="background: transparent !important; border: none !important; box-shadow: none !important; outline: none !important; width: 100%; height: 100%; padding-left: 10px; font-size: 13px; font-weight: 500; color: #334155;">
                </div>
            </div>
        </div>

        <div class="row g-4 m-2" id="teamBalanceGrid">

            @forelse($balances as $employee)
            <div class="col-xl-3 col-lg-4 col-md-6 team-member-card" data-employee-name="{{ strtolower($employee->name) }}">

                <div class="card border-0 shadow-sm h-100 leave-card">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex align-items-center mb-4">

                            <div
                                class="avatar-text rounded-circle bg-soft-primary text-primary fw-bold me-3"
                                style="width:55px;height:55px;font-size:20px;display:flex;align-items:center;justify-content:center;">

                                {{ strtoupper(substr($employee->name,0,1)) }}

                            </div>

                            <div>
                                <h5 class="mb-1 fw-semibold">
                                    {{ $employee->name }}
                                </h5>

                                <small class="text-muted">
                                    Team Member
                                </small>
                            </div>

                        </div>

                        <!-- Leave Stats -->
                        <div class="row g-2 text-center">

                            <div class="col-4">
                                <div class="stat-box total-box">
                                    <small>Total</small>
                                    <h5>{{ number_format($employee->total_allotted, 1) }}</h5>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="stat-box used-box">
                                    <small>Used</small>
                                    <h5>{{ number_format($employee->total_taken, 1) }}</h5>
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="stat-box available-box">
                                    <small>Available</small>
                                    <h5>{{ number_format($employee->balance, 1) }}</h5>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <p class="mb-0 fw-semibold">No team leave balances found.</p>
                    </div>
                </div>
            </div>
            @endforelse

            <div class="col-12 d-none" id="teamBalanceEmptyState">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        <p class="mb-0 fw-semibold">No matching team members found.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        function filterEmployees() {
            const searchInput = document.getElementById('teamBalanceSearch');
            const cards = document.querySelectorAll('.team-member-card');
            const emptyState = document.getElementById('teamBalanceEmptyState');
            const keyword = (searchInput.value || '').trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                const employeeName = card.dataset.employeeName || '';
                const isMatch = employeeName.includes(keyword);

                card.classList.toggle('d-none', !isMatch);
                if (isMatch) {
                    visibleCount++;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount !== 0);
            }
        }
    </script>

    <style>
        .leave-card{
            border-radius:18px;
            transition:.3s;
        }

        .leave-card:hover{
            transform:translateY(-5px);
        }

        .stat-box{
            padding:12px 8px;
            border-radius:12px;
        }

        .stat-box small{
            color:#6c757d;
            display:block;
            margin-bottom:6px;
        }

        .stat-box h5{
            margin:0;
            font-weight:700;
        }

        .total-box{
            background:#eef2ff;
        }

        .used-box{
            background:#fff1f1;
        }

        .used-box h5{
            color:#dc3545;
        }

        .available-box{
            background:#eefbf3;
        }

        .available-box h5{
            color:#198754;
        }
    </style>
@endsection
