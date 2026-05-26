@extends('shared::layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
                <div class="card-header bg-white border-bottom py-3 text-center">
                    <h5 class="fw-bold mb-0" style="color: #334155;">Terms & Conditions</h5>
                </div>
                <div class="card-body p-5">
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 15px;">
                        <h6 class="fw-bold mb-3">1. Acceptance of Terms</h6>
                        <p class="text-muted small mb-4">By accessing and using this HRM System, you agree to be bound by these Terms and Conditions and all applicable laws and regulations.</p>

                        <h6 class="fw-bold mb-3">2. User License</h6>
                        <p class="text-muted small mb-4">This system is designed for internal organizational use. Unauthorized distribution, data scraping, or reproduction of the system's architecture is strictly prohibited.</p>

                        <h6 class="fw-bold mb-3">3. Data Accuracy</h6>
                        <p class="text-muted small mb-4">Administrators are responsible for ensuring the accuracy of employee data, payroll calculations, and attendance records submitted to the system.</p>

                        <h6 class="fw-bold mb-3">4. Account Security</h6>
                        <p class="text-muted small mb-4">Users are responsible for maintaining the confidentiality of their passwords and for all activities that occur under their account.</p>

                        <h6 class="fw-bold mb-3">5. Termination</h6>
                        <p class="text-muted small mb-4">Access to the system may be terminated or suspended at any time, without prior notice, for breach of these Terms or for administrative reasons.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
