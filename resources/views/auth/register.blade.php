<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Account | Enterprise SaaS ERP</title>

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
            --primary-hsl: 224, 76%, 48%;
            --primary: hsl(var(--primary-hsl));
            --primary-hover: hsl(224, 76%, 40%);
            --secondary-hsl: 262, 83%, 58%;
            --secondary: hsl(var(--secondary-hsl));
            --bg-dark: #090d16;
            --bg-glass: rgba(255, 255, 255, 0.85);
            --border-glass: rgba(255, 255, 255, 0.4);
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background: radial-gradient(circle at 10% 20%, rgba(239, 246, 255, 0.85) 0%, rgba(245, 243, 255, 0.75) 100%), #f8fafc;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 40px 15px;
        }

        /* Abstract glowing spheres in background */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(37, 99, 235, 0) 70%);
            top: -150px;
            right: -150px;
            z-index: -1;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0) 70%);
            bottom: -150px;
            left: -150px;
            z-index: -1;
            border-radius: 50%;
        }

        .auth-card {
            background: var(--bg-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.1), 0 0 40px rgba(99, 102, 241, 0.02);
            width: 100%;
            max-width: 520px;
            padding: 40px;
            position: relative;
            z-index: 10;
        }

        .top-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 26px;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #2563eb, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            text-decoration: none;
        }

        .auth-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 26px;
            color: #0f172a;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* Beautiful floating-style modern form controls */
        .form-label-custom {
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            margin-bottom: 6px;
            display: block;
        }

        .form-control-custom, .form-select-custom {
            background: rgba(255, 255, 255, 0.6) !important;
            border: 1.5px solid rgba(226, 232, 240, 0.8) !important;
            border-radius: 12px !important;
            padding: 10px 16px !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            transition: all 0.25s ease-in-out !important;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            background: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
            outline: none !important;
        }

        .btn-custom {
            background: linear-gradient(135deg, #2563eb 0%, #8b5cf6 100%);
            color: white !important;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
            width: 100%;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
            background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 100%);
        }

        .btn-custom:active {
            transform: translateY(0);
        }

        .link-custom {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .link-custom:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .error-banner {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            font-size: 13px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>

<body>
    <div class="auth-card">
        <div class="text-center">
            <a href="/" class="top-logo">
                <i class="bi bi-rocket-takeoff-fill"></i>
                <span>Warr ERP</span>
            </a>
            <h1 class="auth-title">Create your account</h1>
            <p class="auth-subtitle">Get started with your multi-tenant workspace</p>
        </div>

        @if ($errors->any())
            <div class="error-banner shadow-sm">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <div class="fw-semibold">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label-custom">Full Name</label>
                <input id="name" type="text" name="name" class="form-control form-control-custom" 
                       placeholder="John Doe" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label-custom">Email Address</label>
                <input id="email" type="email" name="email" class="form-control form-control-custom" 
                       placeholder="name@company.com" value="{{ old('email') }}" required autocomplete="username">
            </div>

            @if(tenant('id'))
                <!-- Role Selection -->
                <div class="mb-3">
                    <label for="role_id" class="form-label-custom">Select Role</label>
                    <select id="role_id" name="role_id" class="form-select form-select-custom" required>
                        <option value="" disabled selected>Choose your workspace role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Phone fields (Country Code + Contact No) -->
                <div class="mb-3">
                    <div class="row g-2">
                        <div class="col-4">
                            <label for="country_code" class="form-label-custom">Country Code</label>
                            <input id="country_code" type="text" name="country_code" class="form-control form-control-custom text-center" 
                                   placeholder="+91" value="{{ old('country_code', '+91') }}" required>
                        </div>
                        <div class="col-8">
                            <label for="contact_no" class="form-label-custom">Contact Number</label>
                            <input id="contact_no" type="text" name="contact_no" class="form-control form-control-custom" 
                                   placeholder="9876543210" value="{{ old('contact_no') }}" required>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label-custom">Password</label>
                <input id="password" type="password" name="password" class="form-control form-control-custom" 
                       placeholder="••••••••" required autocomplete="new-password">
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label-custom">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control form-control-custom" 
                       placeholder="••••••••" required autocomplete="new-password">
            </div>

            <!-- Submit Button -->
            <div class="mb-4">
                <button type="submit" class="btn btn-custom">
                    Create Account & Login <i class="bi bi-person-plus-fill ms-1"></i>
                </button>
            </div>

            <div class="text-center text-muted fs-14 mt-4" style="font-size: 14px;">
                Already have an account? 
                <a href="{{ route('login') }}" class="link-custom">Log in</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
