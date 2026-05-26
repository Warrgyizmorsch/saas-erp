@extends('shared::layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: white;">
                <div class="card-header bg-white border-bottom py-3 text-center">
                    <h5 class="fw-bold mb-0" style="color: #334155;">Privacy Policy</h5>
                </div>
                <div class="card-body p-5">
                    <div style="max-height: 500px; overflow-y: auto; padding-right: 15px;">
                        <h6 class="fw-bold mb-3">1. Information Collection</h6>
                        <p class="text-muted small mb-4">We collect personal information such as name, contact details, bank accounts, and employment history solely for the purpose of HR management and payroll processing.</p>

                        <h6 class="fw-bold mb-3">2. Data Security</h6>
                        <p class="text-muted small mb-4">We implement industry-standard security measures, including encryption and secure servers, to protect sensitive employee data from unauthorized access.</p>

                        <h6 class="fw-bold mb-3">3. Third-party Sharing</h6>
                        <p class="text-muted small mb-4">Employee data is never shared with third parties except for legal requirements, insurance enrollment, or bank-related payroll transfers.</p>

                        <h6 class="fw-bold mb-3">4. Your Rights</h6>
                        <p class="text-muted small mb-4">Employees have the right to request access to their personal data stored in the system and to correct any inaccuracies through the HR department.</p>

                        <h6 class="fw-bold mb-3">5. Updates</h6>
                        <p class="text-muted small mb-4">This Privacy Policy may be updated periodically to reflect changes in legal requirements or system functionality.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
