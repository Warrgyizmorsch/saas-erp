<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tenant Profile</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: Inter, sans-serif;
            color: #1f2937;
        }

        .profile-header {
            background: linear-gradient(135deg, #0f172a, #1e40af);
            color: white;
            border-radius: 0 0 30px 30px;
            padding: 60px 0;
        }

        .tenant-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
            margin-top: -50px;
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .info-label {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .domain-badge {
            background: #eff6ff;
            color: #2563eb;
            padding: 10px 18px;
            border-radius: 12px;
            display: inline-block;
            font-weight: 500;
        }

        .dashboard-btn {
            background: #2563eb;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            transition: 0.3s;
        }

        .dashboard-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
        }

        .navbar-custom {
            background: transparent;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <section class="profile-header">

        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container">
                <a class="navbar-brand fw-bold fs-3" href="#">
                    ERP SaaS
                </a>

                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light rounded-pill px-4">
                        Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <div class="container mt-5">

            <div class="row align-items-center">

                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">
                        Welcome, {{ $tenant->id }}
                    </h1>

                    <p class="lead text-light opacity-75">
                        Your ERP SaaS tenant workspace is active and ready to use.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <span class="status-badge">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Active Tenant
                    </span>
                </div>

            </div>

        </div>
    </section>

    <!-- Main Content -->
    <div class="container mb-5">

        <!-- Tenant Card -->
        <div class="tenant-card">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Tenant Profile
                    </h2>

                    <p class="text-muted mb-0">
                        Detailed information about your tenant account.
                    </p>
                </div>

                <a href="{{ route('dashboard') }}" class="btn dashboard-btn text-white">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Go To Dashboard
                </a>

            </div>

            <div class="row g-4">

                <!-- Tenant ID -->
                <div class="col-md-6">
                    <div class="info-card">

                        <div class="info-label">
                            Tenant ID
                        </div>

                        <div class="info-value">
                            {{ $tenant->id }}
                        </div>

                    </div>
                </div>

                <!-- Created At -->
                <div class="col-md-6">
                    <div class="info-card">

                        <div class="info-label">
                            Created At
                        </div>

                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($tenant->created_at)->format('d M Y, h:i A') }}
                        </div>

                    </div>
                </div>

                <!-- Updated At -->
                <div class="col-md-6">
                    <div class="info-card">

                        <div class="info-label">
                            Last Updated
                        </div>

                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($tenant->updated_at)->format('d M Y, h:i A') }}
                        </div>

                    </div>
                </div>

                <!-- Domains -->
                <div class="col-md-6">
                    <div class="info-card">

                        <div class="info-label mb-3">
                            Tenant Domains
                        </div>

                        @foreach($tenant->domains as $domain)
                            <div class="domain-badge mb-2">
                                <i class="bi bi-globe2 me-2"></i>
                                {{ $domain->domain }}
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>

        </div>

        <!-- Services Section -->
        <div class="row g-4 mt-4">

            <div class="col-md-4">
                <div class="info-card text-center">

                    <div class="mb-3">
                        <i class="bi bi-people-fill text-primary fs-1"></i>
                    </div>

                    <h4>CRM Module</h4>

                    <p class="text-muted mb-0">
                        Manage customers, sales pipelines,
                        leads, and communication.
                    </p>

                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card text-center">

                    <div class="mb-3">
                        <i class="bi bi-person-workspace text-primary fs-1"></i>
                    </div>

                    <h4>HRMS Module</h4>

                    <p class="text-muted mb-0">
                        Employee management, attendance,
                        payroll, and HR automation.
                    </p>

                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card text-center">

                    <div class="mb-3">
                        <i class="bi bi-box-seam text-primary fs-1"></i>
                    </div>

                    <h4>Inventory System</h4>

                    <p class="text-muted mb-0">
                        Track stock, warehouse,
                        inventory, and purchase management.
                    </p>

                </div>
            </div>

        </div>

    </div>

</body>

</html>