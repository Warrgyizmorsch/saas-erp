@extends('shared::layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0" style="color: #334155;">System Help & Support</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Getting Started</h6>
                            <p class="text-muted small">Welcome to Warrgyizmorsch HRM. To start managing your employees, navigate to the "Employee List" and use the "Add Employee" button.</p>
                            
                            <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-briefcase me-2"></i>Projects & Tasks</h6>
                            <p class="text-muted small">Use the Projects module to track project progress and the Daily Task Master to monitor team productivity accurately.</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-2"></i>Payroll & Attendance</h6>
                            <p class="text-muted small">Generate payslips and manage attendance records seamlessly through the Payroll dashboard. Attendance can be imported via Excel or entered manually.</p>
                            
                            <h6 class="fw-bold text-primary mb-3 mt-4"><i class="bi bi-headset me-2"></i>Contact Support</h6>
                            <p class="text-muted small">If you encounter any technical issues, please contact our administrator at <a href="mailto:info@warrgyizmorsch.com">info@warrgyizmorsch.com</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
