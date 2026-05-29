<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Workspace | Enterprise SaaS ERP</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-hsl: 224, 76%, 48%; /* Sleek Royal Blue */
            --primary: hsl(var(--primary-hsl));
            --primary-hover: hsl(224, 76%, 40%);
            --secondary-hsl: 262, 83%, 58%; /* Elegant Violet */
            --secondary: hsl(var(--secondary-hsl));
            --bg-dark: #090d16;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --border-glass: rgba(255, 255, 255, 0.4);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background: radial-gradient(circle at 10% 20%, rgba(239, 246, 255, 0.8) 0%, rgba(245, 243, 255, 0.7) 100%), #f8fafc;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 40px 0;
        }

        /* Abstract Premium Gradients in Background */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(37, 99, 235, 0) 70%);
            top: -200px;
            right: -200px;
            z-index: -1;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0) 70%);
            bottom: -200px;
            left: -200px;
            z-index: -1;
            border-radius: 50%;
        }

        .company-card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.1), 0 0 40px rgba(99, 102, 241, 0.03);
            width: 100%;
            max-width: 1180px;
        }

        .left-panel {
            background: linear-gradient(135deg, #090e1a 0%, #1e293b 100%);
            color: white;
            padding: 55px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 50%);
            top: 0;
            left: 0;
            pointer-events: none;
        }

        .top-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 28px;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .left-panel h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 2.8rem;
            line-height: 1.25;
            letter-spacing: -1px;
            color: #f8fafc;
        }

        .left-panel p {
            color: #94a3b8;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-top: 15px;
        }

        .feature-list {
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            gap: 15px;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #a855f7;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .feature-item:hover .feature-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .feature-text h6 {
            font-weight: 600;
            margin-bottom: 4px;
            color: #f1f5f9;
        }

        .feature-text small {
            color: #94a3b8;
            display: block;
        }

        .form-section {
            padding: 55px;
        }

        .form-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .form-subtitle {
            color: var(--text-muted);
            margin-bottom: 35px;
            font-size: 1rem;
        }

        /* Section dividers */
        .section-header {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header i {
            color: var(--primary);
        }

        /* Custom input elements */
        .form-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            background-color: #f8fafc;
        }

        .form-control:focus, .form-select:focus {
            background-color: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .input-group-text {
            border-radius: 0 12px 12px 0;
            border: 1px solid #cbd5e1;
            border-left: none;
            background-color: #e2e8f0;
            font-weight: 600;
            color: #475569;
            padding: 0 18px;
        }

        /* Custom Interactive Package Selector */
        .package-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 22px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            background: white;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.05);
            border-color: #94a3b8;
        }

        .package-card.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px var(--primary), 0 15px 30px rgba(37, 99, 235, 0.1);
            background: linear-gradient(180deg, white 0%, rgba(239, 246, 255, 0.3) 100%);
        }

        .package-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
        }

        .package-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .package-price {
            font-family: 'Outfit', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .package-price small {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .package-limit {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .package-features {
            list-style: none;
            padding: 0;
            margin: 0;
            border-top: 1px dashed #e2e8f0;
            padding-top: 15px;
        }

        .package-features li {
            font-size: 0.82rem;
            color: #475569;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .package-features li i {
            color: #10b981;
            font-size: 14px;
        }

        /* Custom Module Cards */
        .module-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .module-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .module-card input {
            width: 18px;
            height: 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .module-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .module-card:hover .module-icon-wrap {
            background: var(--primary);
            color: white;
        }

        .module-card h6 {
            font-weight: 600;
            margin-bottom: 2px;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .module-card small {
            color: var(--text-muted);
            display: block;
            font-size: 0.78rem;
        }

        .btn-create {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--secondary) 100%);
        }

        .btn-create:active {
            transform: translateY(0);
        }

        .alert-premium {
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: 16px;
            padding: 16px 20px;
            color: #991b1b;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(153, 27, 27, 0.05);
        }

        .alert-premium i {
            font-size: 22px;
        }

        @media(max-width: 991px) {
            .left-panel {
                padding: 40px;
                min-height: auto;
            }
            .form-section {
                padding: 40px;
            }
            .left-panel h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>

<body>

    <section class="container py-4">
        <div class="company-card mx-auto">
            <div class="row g-0">
                <!-- Left Decorative Panel -->
                <div class="col-lg-5">
                    <div class="left-panel">
                        <div>
                            <div class="top-logo">
                                <i class="bi bi-box-seam-fill"></i>
                                <span>ERP SaaS</span>
                            </div>

                            <h1>Deploy Your Workspace</h1>
                            <p>
                                Initialize a high-performance ERP environment dynamically scaled for your company. Provision instant CRM pipelines, workforce trackers, and inventory warehouses with a single onboarding flow.
                            </p>
                        </div>

                        <div class="feature-list">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-briefcase"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Complete CRM Pipeline</h6>
                                    <small>Sales tracking, callbacks, and leads auditing.</small>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>HRMS Infrastructure</h6>
                                    <small>Scale roles, track attendance, and log activity.</small>
                                </div>
                            </div>

                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="feature-text">
                                    <h6>Multi-Tenant Isolation</h6>
                                    <small>Isolated database connections and files security.</small>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-top border-secondary border-opacity-25">
                            <small class="text-secondary">&copy; {{ date('Y') }} ERP SaaS Inc. All rights reserved.</small>
                        </div>
                    </div>
                </div>

                <!-- Right Form Panel -->
                <div class="col-lg-7">
                    <div class="form-section">
                        <h2 class="form-title">Create Company</h2>
                        <p class="form-subtitle">Provision your secure cloud ERP environment.</p>

                        <!-- Error Banner -->
                        @if ($errors->any())
                            <div class="mb-4">
                                <div class="alert-premium">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Configuration Failed</h6>
                                        <ul class="mb-0 ps-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="/create-company">
                            @csrf

                            <!-- Section 1: Company Profile -->
                            <div class="mb-5">
                                <div class="section-header">
                                    <i class="bi bi-building"></i>
                                    <span>1. Company Profile</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" name="company_name" class="form-control" placeholder="Acme Corporation" value="{{ old('company_name') }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" name="phone" class="form-control" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Company Subdomain</label>
                                        @php
                                            $host = request()->getHost();
                                            $suffix = filter_var($host, FILTER_VALIDATE_IP) ? '.' . $host . '.nip.io' : '.' . $host;
                                        @endphp
                                        <div class="input-group">
                                            <input type="text" name="subdomain" class="form-control text-lowercase" placeholder="acme" value="{{ old('subdomain') }}" required>
                                            <span class="input-group-text">{{ $suffix }}</span>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Creates a dedicated tenant domain: e.g. <span class="fw-semibold">acme{{ $suffix }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Subscription Package -->
                            <div class="mb-5">
                                <div class="section-header">
                                    <i class="bi bi-credit-card"></i>
                                    <span>2. Select Package Plan</span>
                                </div>

                                <input type="hidden" name="package_id" id="selected_package_id" value="{{ old('package_id', $packages->first()?->id) }}">

                                <div class="row g-3">
                                    @foreach($packages as $package)
                                        <div class="col-md-4">
                                            <div class="package-card {{ old('package_id', $packages->first()?->id) == $package->id ? 'active' : '' }}" 
                                                 data-package-id="{{ $package->id }}" 
                                                 onclick="selectPackage(this)">
                                                
                                                @if($package->code == 'grower')
                                                    <span class="package-badge">Popular</span>
                                                @endif
                                                
                                                <div>
                                                    <div class="package-title">{{ $package->name }}</div>
                                                    <div class="package-price">
                                                        ${{ number_format($package->price, 0) }}
                                                        <small>/mo</small>
                                                    </div>
                                                    <div class="package-limit">
                                                        <i class="bi bi-person-check-fill"></i>
                                                        <span>
                                                            @if($package->max_users >= 9999)
                                                                Unlimited Users
                                                            @else
                                                                Max {{ $package->max_users }} Users
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>

                                                <ul class="package-features">
                                                    @if(is_array($package->features))
                                                        @foreach($package->features as $feature)
                                                            <li><i class="bi bi-patch-check-fill"></i> {{ $feature }}</li>
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Section 3: Business Metadata -->
                            <div class="mb-5">
                                <div class="section-header">
                                    <i class="bi bi-sliders"></i>
                                    <span>3. Business Classification</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Industry Sector</label>
                                        <select name="industry" class="form-select" required>
                                            <option value="">Choose Industry...</option>
                                            @foreach(['Technology', 'Retail', 'Manufacturing', 'Healthcare', 'Education', 'Financial Services', 'Consulting', 'Real Estate'] as $ind)
                                                <option value="{{ $ind }}" {{ old('industry') == $ind ? 'selected' : '' }}>{{ $ind }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Company Size</label>
                                        <select name="company_size" class="form-select" required>
                                            <option value="">Select Size...</option>
                                            @foreach(['1-10 employees', '11-50 employees', '51-200 employees', '201-500 employees', '500+ employees'] as $size)
                                                <option value="{{ $size }}" {{ old('company_size') == $size ? 'selected' : '' }}>{{ $size }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Operational Country</label>
                                        <select name="country" class="form-select" required>
                                            <option value="">Select Country...</option>
                                            @foreach(['United States', 'United Kingdom', 'Canada', 'Australia', 'India', 'Germany', 'United Arab Emirates', 'Singapore'] as $coun)
                                                <option value="{{ $coun }}" {{ old('country') == $coun ? 'selected' : '' }}>{{ $coun }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Tax ID / Business Registration Number <span class="text-muted fw-normal">(Optional)</span></label>
                                        <input type="text" name="tax_id" class="form-control" placeholder="VAT-1234567 or GSTIN-987654" value="{{ old('tax_id') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Module Subsystem selection -->
                            <div class="mb-5">
                                <div class="section-header">
                                    <i class="bi bi-grid-fill"></i>
                                    <span>4. Enable Subsystems</span>
                                </div>

                                <div class="row g-3">
                                    <!-- CRM -->
                                    <div class="col-md-4">
                                        <label class="module-card w-100" for="module_crm">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" id="module_crm" name="modules[]" value="CRM" class="form-check-input" checked>
                                                <div class="module-icon-wrap">
                                                    <i class="bi bi-people-fill"></i>
                                                </div>
                                                <div>
                                                    <h6>CRM Suite</h6>
                                                    <small>Leads & Sales</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- HRMS -->
                                    <div class="col-md-4">
                                        <label class="module-card w-100" for="module_hrms">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" id="module_hrms" name="modules[]" value="HRMS" class="form-check-input" checked>
                                                <div class="module-icon-wrap">
                                                    <i class="bi bi-person-workspace"></i>
                                                </div>
                                                <div>
                                                    <h6>HRMS Core</h6>
                                                    <small>Staff & Roles</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Inventory -->
                                    <div class="col-md-4">
                                        <label class="module-card w-100" for="module_inv">
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="checkbox" id="module_inv" name="modules[]" value="Inventory" class="form-check-input" checked>
                                                <div class="module-icon-wrap">
                                                    <i class="bi bi-box-seam-fill"></i>
                                                </div>
                                                <div>
                                                    <h6>Inventory</h6>
                                                    <small>Stock & Warehouses</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Workspace Creation -->
                            <div class="mt-5">
                                <button type="submit" class="btn btn-create w-100">
                                    <i class="bi bi-hdd-network-fill me-2"></i>
                                    Provision Workspace Server
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- jQuery or Vanilla JS for Interactive Selection Cards -->
    <script>
        function selectPackage(card) {
            // Remove active classes from all cards
            document.querySelectorAll('.package-card').forEach(c => {
                c.classList.remove('active');
            });
            
            // Add active class to clicked card
            card.classList.add('active');
            
            // Set hidden package_id input
            const packageId = card.getAttribute('data-package-id');
            document.getElementById('selected_package_id').value = packageId;
        }
    </script>
</body>

</html>