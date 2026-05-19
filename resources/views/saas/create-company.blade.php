<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Company | ERP SaaS</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f7fb;
            font-family: Inter, sans-serif;
            min-height: 100vh;
        }

        .create-company-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 50px 0;
        }

        .company-card {
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .left-panel {
            background: linear-gradient(135deg, #0f172a, #1e40af);
            color: white;
            padding: 60px;
            height: 100%;
        }

        .left-panel h1 {
            font-weight: 700;
            font-size: 3rem;
            line-height: 1.2;
        }

        .left-panel p {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 20px;
            font-size: 1.05rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-top: 25px;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .form-section {
            padding: 60px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
        }

        .form-subtitle {
            color: #6b7280;
            margin-bottom: 35px;
        }

        .form-control {
            border-radius: 14px;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .module-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 20px;
            transition: 0.3s;
            cursor: pointer;
            height: 100%;
        }

        .module-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
        }

        .module-card input {
            width: 20px;
            height: 20px;
        }

        .module-icon {
            width: 55px;
            height: 55px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .btn-create {
            background: #2563eb;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-weight: 600;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn-create:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .top-logo {
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 50px;
        }

        @media(max-width: 991px) {
            .left-panel,
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

    <section class="create-company-section">

        <div class="container">

            <div class="company-card">

                <div class="row g-0">

                    <!-- Left Side -->
                    <div class="col-lg-5">

                        <div class="left-panel">

                            <div class="top-logo">
                                ERP SaaS
                            </div>

                            <h1>
                                Build Your Company Workspace
                            </h1>

                            <p>
                                Create your ERP company account and start managing CRM,
                                HRMS, Inventory, and business operations from one platform.
                            </p>

                            <div class="mt-5">

                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="bi bi-people"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1">CRM Management</h6>
                                        <small>Sales, Leads & Customers</small>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="bi bi-person-workspace"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1">HRMS System</h6>
                                        <small>Employees & Payroll</small>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1">Inventory Control</h6>
                                        <small>Stock & Warehouse Management</small>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Right Side -->
                    <div class="col-lg-7">

                        <div class="form-section">

                            <h2 class="form-title">
                                Create Company
                            </h2>

                            <p class="form-subtitle">
                                Setup your SaaS ERP workspace in a few simple steps.
                            </p>

                            <form method="POST" action="/create-company">

                                @csrf

                                <!-- Company Name -->
                                <div class="mb-4">

                                    <label class="form-label fw-semibold">
                                        Company Name
                                    </label>

                                    <input type="text"
                                        name="company_name"
                                        class="form-control"
                                        placeholder="Enter company name"
                                        required>

                                </div>

                                <!-- Subdomain -->
                                <div class="mb-5">

                                    <label class="form-label fw-semibold">
                                        Company Subdomain
                                    </label>

                                    <div class="input-group">

                                        <input type="text"
                                            name="subdomain"
                                            class="form-control"
                                            placeholder="yourcompany"
                                            required>

                                        <span class="input-group-text bg-light">
                                            .localhost
                                        </span>

                                    </div>

                                    <small class="text-muted">
                                        Example: yourcompany.localhost
                                    </small>

                                </div>

                                <!-- Modules -->
                                <div class="mb-4">

                                    <label class="form-label fw-semibold mb-3">
                                        Select ERP Modules
                                    </label>

                                    <div class="row g-4">

                                        <!-- CRM -->
                                        <div class="col-md-4">

                                            <label class="module-card w-100">

                                                <input type="checkbox"
                                                    name="modules[]"
                                                    value="CRM"
                                                    class="form-check-input mb-3">

                                                <div class="module-icon">
                                                    <i class="bi bi-people"></i>
                                                </div>

                                                <h5>CRM</h5>

                                                <p class="text-muted small mb-0">
                                                    Customer & Sales Management
                                                </p>

                                            </label>

                                        </div>

                                        <!-- HRMS -->
                                        <div class="col-md-4">

                                            <label class="module-card w-100">

                                                <input type="checkbox"
                                                    name="modules[]"
                                                    value="HRMS"
                                                    class="form-check-input mb-3">

                                                <div class="module-icon">
                                                    <i class="bi bi-person-workspace"></i>
                                                </div>

                                                <h5>HRMS</h5>

                                                <p class="text-muted small mb-0">
                                                    Employee & Payroll System
                                                </p>

                                            </label>

                                        </div>

                                        <!-- Inventory -->
                                        <div class="col-md-4">

                                            <label class="module-card w-100">

                                                <input type="checkbox"
                                                    name="modules[]"
                                                    value="Inventory"
                                                    class="form-check-input mb-3">

                                                <div class="module-icon">
                                                    <i class="bi bi-box-seam"></i>
                                                </div>

                                                <h5>Inventory</h5>

                                                <p class="text-muted small mb-0">
                                                    Stock & Warehouse Control
                                                </p>

                                            </label>

                                        </div>

                                    </div>

                                </div>

                                <!-- Submit -->
                                <div class="mt-5">

                                    <button type="submit"
                                        class="btn btn-create text-white w-100">

                                        <i class="bi bi-building-add me-2"></i>

                                        Create Company Workspace

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</body>

</html>