<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'ERP SaaS') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f5f7fb;
            font-family: Inter, sans-serif;
            color: #1f2937;
        }

        .hero-section {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: white;
            padding: 100px 0;
            border-radius: 0 0 40px 40px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: #d1d5db;
            margin-top: 20px;
        }

        .btn-create {
            background: #2563eb;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .btn-create:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .service-card {
            border: none;
            border-radius: 20px;
            padding: 30px;
            transition: 0.3s;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-8px);
        }

        .service-icon {
            width: 70px;
            height: 70px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 30px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 15px;
        }

        .feature-box {
            background: white;
            border-radius: 18px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        }

        .stats-box {
            background: white;
            border-radius: 18px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04);
        }

        .footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 30px 0;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent position-absolute top-0 w-100 py-4">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3" href="#">
                ERP SaaS
            </a>

            <div class="d-flex gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-light rounded-pill px-4">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light rounded-pill px-4">
                            Login
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center gy-5">

                <div class="col-lg-6">
                    <h1 class="hero-title">
                        Smart ERP & CRM Software for Modern Businesses
                    </h1>

                    <p class="hero-subtitle">
                        Manage your entire business from one powerful platform.
                        CRM, HRMS, Inventory, Sales, Finance, Employee Management,
                        and Operations — all in one SaaS ERP solution.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ url('/create-company') }}"
                           class="btn btn-create text-white">
                            <i class="bi bi-building-add me-2"></i>
                            Create Company
                        </a>

                        <a href="#services"
                           class="btn btn-outline-light px-4 py-3 rounded-3">
                            Explore Services
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="bg-white p-4 rounded-4 shadow-lg">
                        <img
                            src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=1200&auto=format&fit=crop"
                            class="img-fluid rounded-4"
                            alt="ERP Dashboard">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Services -->
    <section id="services" class="py-5 my-5">
        <div class="container">

            <div class="text-center mb-5">
                <h2 class="section-title">
                    Complete Business Management Suite
                </h2>

                <p class="text-muted">
                    Everything your business needs in one integrated platform.
                </p>
            </div>

            <div class="row g-4">

                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-people"></i>
                        </div>

                        <h4>CRM System</h4>

                        <p class="text-muted mb-0">
                            Manage leads, customers, sales pipelines,
                            and communication efficiently.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>

                        <h4>HRMS</h4>

                        <p class="text-muted mb-0">
                            Employee management, payroll, attendance,
                            leave tracking, and HR automation.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <h4>Inventory</h4>

                        <p class="text-muted mb-0">
                            Real-time inventory tracking, stock management,
                            warehouse control, and purchase flow.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>

                        <h4>Analytics</h4>

                        <p class="text-muted mb-0">
                            Powerful dashboards and reports to help
                            you make smarter business decisions.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5">
        <div class="container">

            <div class="row g-4 align-items-center">

                <div class="col-lg-6">
                    <h2 class="section-title">
                        Why Choose Our ERP SaaS?
                    </h2>

                    <p class="text-muted mb-4">
                        Built for startups, SMEs, and enterprises with
                        modern cloud technology and scalable architecture.
                    </p>

                    <div class="feature-box mb-3">
                        <h5>
                            <i class="bi bi-cloud-check text-primary me-2"></i>
                            Cloud Based Platform
                        </h5>

                        <p class="text-muted mb-0">
                            Access your business anytime, anywhere.
                        </p>
                    </div>

                    <div class="feature-box mb-3">
                        <h5>
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            Secure & Reliable
                        </h5>

                        <p class="text-muted mb-0">
                            Enterprise-grade security and data protection.
                        </p>
                    </div>

                    <div class="feature-box">
                        <h5>
                            <i class="bi bi-lightning-charge text-primary me-2"></i>
                            Fast & Scalable
                        </h5>

                        <p class="text-muted mb-0">
                            Optimized performance for growing businesses.
                        </p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="row g-4">

                        <div class="col-6">
                            <div class="stats-box">
                                <h2 class="fw-bold text-primary">500+</h2>
                                <p class="text-muted mb-0">Companies</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stats-box">
                                <h2 class="fw-bold text-primary">99.9%</h2>
                                <p class="text-muted mb-0">Uptime</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stats-box">
                                <h2 class="fw-bold text-primary">24/7</h2>
                                <p class="text-muted mb-0">Support</p>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="stats-box">
                                <h2 class="fw-bold text-primary">All-In-One</h2>
                                <p class="text-muted mb-0">Business Suite</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- CTA -->
    <section class="py-5">
        <div class="container">
            <div class="bg-primary text-white rounded-4 p-5 text-center shadow-lg">
                <h2 class="fw-bold mb-3">
                    Start Managing Your Business Smarter
                </h2>

                <p class="mb-4">
                    Create your company workspace and streamline operations today.
                </p>

                <a href="{{ url('/create-company') }}"
                   class="btn btn-light btn-lg rounded-3 px-5">
                    <i class="bi bi-building-add me-2"></i>
                    Create Company
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container text-center">
            <p class="mb-0">
                © {{ date('Y') }} ERP SaaS Software. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>