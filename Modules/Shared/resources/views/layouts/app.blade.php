<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Meta Information --}}
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="theme_ocean">

    <title>@yield('title', 'Custom CRM')</title>

    {{-- Favicon --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">

    {{-- Vendors CSS --}}
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/vendors.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/daterangepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/jquery-jvectormap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/select2-theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/vendors/css/jquery.time-to.min.css') }}">

    {{-- Custom Theme CSS --}}
    <link rel="stylesheet" href="{{ asset('crm-assets/assets/css/theme.min.css') }}">

    <!-- Phone Input Country Code  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" />

    {{-- Extra CSS from child views --}}
    @stack('styles')

    <style>
        .crm-page-container {
            max-width: 100%;
            margin: 0 auto;
            min-height: 100vh;
            padding: 30px;
        }

        .crm-page-heading {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #333;
        }

        .action-links {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            color: #3490dc;
            background: none;
            border: none;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-delete {
            background: none;
            border: none;
            color: #e3342f;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-history {
            background: none;
            border: none;
            color: orange;
            font-weight: 500;
            cursor: pointer;
        }

        .btn {
            border-radius: 8px;
            font-size: 13px;
            padding: 6px 12px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
        }

        th {
            font-size: small !important;
            text-transform: none !important;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #ccc;
            border-top: 5px solid #000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .crm-page-container {
                padding: 20px 15px;
            }
        }

        /* --- HRMS Specific Styles --- */
        .btn-soft-danger {
            background: rgba(239, 68, 68, 0.08) !important;
            color: #ef4444 !important;
            border: 1.5px solid rgba(239, 68, 68, 0.2) !important;
        }

        .btn-soft-danger:hover {
            background: #ef4444 !important;
            color: #fff !important;
            border-color: #ef4444 !important;
        }

        .btn-soft-primary {
            background: rgba(56, 88, 249, 0.08) !important;
            color: #3858f9 !important;
            border: 1.5px solid rgba(56, 88, 249, 0.2) !important;
        }

        .btn-soft-primary:hover {
            background: #3858f9 !important;
            color: #fff !important;
            border-color: #3858f9 !important;
        }

        .btn-soft-success {
            background: rgba(34, 197, 94, 0.08) !important;
            color: #22c55e !important;
            border: 1.5px solid rgba(34, 197, 94, 0.2) !important;
        }

        .btn-soft-success:hover {
            background: #22c55e !important;
            color: #fff !important;
            border-color: #22c55e !important;
        }

        .btn-soft-secondary {
            background: rgba(100, 116, 139, 0.08) !important;
            color: #64748b !important;
            border: 1.5px solid rgba(100, 116, 139, 0.2) !important;
        }

        .btn-soft-secondary:hover {
            background: #64748b !important;
            color: #fff !important;
            border-color: #64748b !important;
        }

        /* Premium Status & Priority UI */
        .premium-status-dropdown .btn-status,
        .priority-badge,
        .lead-select-btn {
            position: relative;
        }

        .premium-status-dropdown .btn-status.dropdown-toggle::after,
        .priority-badge.dropdown-toggle::after,
        .lead-select-btn.dropdown-toggle::after {
            display: none !important;
        }

        .premium-status-dropdown .btn-status {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        /* Status Colors */
        .status-pending {
            background: rgba(100, 116, 139, 0.1) !important;
            color: #64748b !important;
        }

        .status-in-process {
            background: rgba(56, 88, 249, 0.1) !important;
            color: #3858f9 !important;
        }

        .status-completed {
            background: rgba(34, 197, 94, 0.1) !important;
            color: #22c55e !important;
        }

        .status-on-hold {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
        }

        .status-review {
            background: rgba(6, 182, 212, 0.1) !important;
            color: #06b6d4 !important;
        }

        .status-rework {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }

        /* Priority UI */
        .priority-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.3px;
            cursor: pointer;
            border: none;
        }

        .priority-hard {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }

        .priority-medium {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
        }

        .priority-low {
            background: rgba(34, 197, 94, 0.1) !important;
            color: #22c55e !important;
        }

        .priority-normal {
            background: rgba(56, 88, 249, 0.1) !important;
            color: #3858f9 !important;
        }

        .premium-attachment-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 12px;
            background: rgba(56, 88, 249, 0.08);
            color: #3858f9;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 6px;
            transition: all 0.3s;
            text-decoration: none !important;
            border: 1px solid rgba(56, 88, 249, 0.15);
        }

        .premium-attachment-link:hover {
            background: #3858f9;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(56, 88, 249, 0.25);
            transform: translateY(-1px);
        }

        /* Searchable Dropdown Styles */
        .wghrm-search-dropdown {
            position: relative;
            width: 100%;
        }

        .wghrm-dropdown-trigger {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s;
        }

        .wghrm-dropdown-trigger:hover {
            border-color: #3858f9 !important;
            background-color: #fff !important;
        }

        .wghrm-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 100%;
            min-width: 280px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            display: none;
            padding: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .wghrm-dropdown-menu.show {
            display: block;
            animation: wghrmSlideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes wghrmSlideDown {
            from { opacity: 0; transform: translateY(-10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .wghrm-search-container {
            margin-bottom: 12px;
            position: relative;
        }

        .wghrm-search-input {
            width: 100%;
            height: 44px;
            padding: 0 12px 0 40px;
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            outline: none;
            transition: all 0.2s;
        }

        .wghrm-search-input:focus {
            border-color: #3858f9;
            background: white;
            box-shadow: 0 0 0 4px rgba(56, 88, 249, 0.1);
        }

        .wghrm-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            width: 16px;
            height: 16px;
        }

        .wghrm-items-list {
            max-height: 250px;
            overflow-y: auto !important;
            margin: 0 -4px;
            padding: 0 4px;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .wghrm-items-list::-webkit-scrollbar {
            width: 6px;
            display: block !important;
        }
        .wghrm-items-list::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .wghrm-items-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }
        .wghrm-items-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media (max-width: 768px) {
            .wghrm-mobile-card {
                background: white;
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 16px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }
            .wghrm-mobile-card-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 12px;
            }
            .wghrm-mobile-card-body {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .wghrm-mobile-label {
                font-size: 11px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }
            .wghrm-mobile-value {
                font-size: 13px;
                font-weight: 600;
                color: #1e293b;
            }
            .wghrm-mobile-full-width {
                grid-column: span 2;
            }
            .desktop-only { display: none !important; }
        }
        @media (min-width: 769px) {
            .mobile-only { display: none !important; }
        }

        .wghrm-item {
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            color: #334155;
            transition: all 0.15s;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
        }

        .wghrm-item:hover {
            background: #f0f4ff;
            color: #3858f9;
        }

        .wghrm-item.selected {
            background: #eef2ff;
            color: #3858f9;
            font-weight: 700;
        }

        .wghrm-item-text {
            flex: 1;
        }

        .wghrm-item-check {
            color: #3858f9;
            display: none;
        }

        .wghrm-item.selected .wghrm-item-check {
            display: block;
        }

        .wghrm-no-results {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    {{-- Sidebar --}}
    @include('shared::layouts.aside')

    {{-- Header --}}
    @include('shared::layouts.header')

    <main class="nxl-container">
        <div class="nxl-content">
            {{-- Main Content --}}
            @yield('content')
        </div>

        {{-- Footer --}}
        @include('shared::layouts.footer')
    </main>

    {{-- Customizer --}}
    @include('shared::layouts.customizer')

    {{-- Vendor Scripts --}}
    <script src="{{ asset('crm-assets/assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/jquery.time-to.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/circle-progress.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/vendors/js/select2-active.min.js') }}"></script>

    {{-- App Scripts --}}
    <script src="{{ asset('crm-assets/assets/js/common-init.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/js/leads-init.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/js/theme-customizer-init.min.js') }}"></script>
    <script src="{{ asset('crm-assets/assets/js/analytics-init.min.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Extra Scripts from child views --}}
    @stack('scripts')

    <!-- intlTelInput scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/modules/funnel.js"></script>

    <script>

        $(document).ready(function () {

            // AJAX loader
            $(document).ajaxStart(function () {

                $("#globalLoader").fadeIn();

            });

            $(document).ajaxStop(function () {

                $("#globalLoader").fadeOut();

            });

            // FORM submit loader
            document.addEventListener("submit", function (e) {

                let form = e.target;

                if (form.tagName === "FORM") {

                    document.getElementById("globalLoader").style.display = "flex";

                }

            });

            // =========================
            // SHOW ENTRIES DROPDOWN
            // =========================
            document.querySelectorAll('select[name="per_page"]').forEach(function (select) {

                select.addEventListener("change", function () {

                    document.getElementById("globalLoader").style.display = "flex";

                });

            });

        });

    </script>

    <script>

        // =========================
        // ALL LINK CLICKS
        // =========================
        document.addEventListener("click", function (e) {

            let link = e.target.closest("a");

            if (!link) return;

            // Ignore invalid links
            if (
                !link.href ||
                link.href.includes("javascript:") ||
                link.href.includes("#") ||
                link.hasAttribute("target") ||
                link.hasAttribute("download")
            ) {
                return;
            }

            // Ignore Bootstrap dropdown toggle
            if (
                link.getAttribute("data-bs-toggle") === "dropdown" ||
                link.classList.contains("dropdown-toggle")
            ) {
                return;
            }

            // Ignore collapse buttons
            if (
                link.getAttribute("data-bs-toggle") === "collapse"
            ) {
                return;
            }

            // SHOW LOADER
            document.getElementById("globalLoader").style.display = "flex";

        });

        // =========================
        // PAGE LOAD COMPLETE
        // =========================
        window.addEventListener("load", function () {

            document.getElementById("globalLoader").style.display = "none";

        });

        // =========================
        // BACK/FORWARD CACHE FIX
        // =========================
        window.addEventListener("pageshow", function () {

            document.getElementById("globalLoader").style.display = "none";

        });

        // =========================
        // HRMS SEARCHABLE DROPDOWNS & UTILITIES
        // =========================
        const originalFetch = window.fetch;
        window.fetch = function (url, options) {
            const base = document.querySelector('meta[name="base-url"]') ? document.querySelector('meta[name="base-url"]').content : "{{ url('/') }}";

            if (typeof url === "string" && url.startsWith("/")) {
                url = base + url;
            }

            return originalFetch(url, options);
        };

        // Toast UI configuration
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            window.HrmToast = Toast;

            // Success Alert
            @if(Session::has('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{{ Session::get('success') }}"
                });
            @endif

            // Error Alert
            @if(Session::has('error'))
                Toast.fire({
                    icon: 'error',
                    title: "{{ Session::get('error') }}"
                });
            @endif

            // Warning Alert
            @if(Session::has('warning'))
                Toast.fire({
                    icon: 'warning',
                    title: "{{ Session::get('warning') }}"
                });
            @endif

            // Info Alert
            @if(Session::has('info'))
                Toast.fire({
                    icon: 'info',
                    title: "{{ Session::get('info') }}"
                });
            @endif
        }

        // Global Delete Confirmation
        function deleteData(event) {
            event.preventDefault();
            const form = event.currentTarget;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3858f9',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'btn btn-primary px-4',
                        cancelButton: 'btn btn-light-brand px-4 me-3'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this item?')) {
                    form.submit();
                }
            }
            return false;
        }

        // Global Searchable Dropdown Initializer
        function initWghrmDropdown(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container || container.dataset.initialized) return;
            container.dataset.initialized = 'true';

            const trigger = container.querySelector('.wghrm-dropdown-trigger');
            const menu = container.querySelector('.wghrm-dropdown-menu');
            const searchInput = container.querySelector('.wghrm-search-input');
            const itemsList = container.querySelector('.wghrm-items-list');
            const hiddenInput = container.querySelector('input[type="hidden"]');
            const triggerText = trigger.querySelector('.wghrm-trigger-text');

            if (!trigger || !menu || !itemsList) return;

            // Toggle Menu
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other open dropdowns
                document.querySelectorAll('.wghrm-dropdown-menu.show').forEach(m => {
                    if (m !== menu) m.classList.remove('show');
                });

                const isOpen = menu.classList.contains('show');
                menu.classList.toggle('show');
                
                if (!isOpen) {
                    setTimeout(() => {
                        if (searchInput) {
                            searchInput.value = '';
                            searchInput.dispatchEvent(new Event('input'));
                            searchInput.focus();
                        }
                    }, 50);
                }
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    menu.classList.remove('show');
                }
            });

            // Search Logic
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const term = e.target.value.toLowerCase();
                    let hasResults = false;
                    const items = itemsList.querySelectorAll('.wghrm-item');

                    items.forEach(item => {
                        const dataText = item.getAttribute('data-text') || item.textContent;
                        const text = dataText.toLowerCase();
                        if (text.includes(term)) {
                            item.style.setProperty('display', 'flex', 'important');
                            hasResults = true;
                        } else {
                            item.style.setProperty('display', 'none', 'important');
                        }
                    });

                    let noResults = itemsList.querySelector('.wghrm-no-results');
                    if (!hasResults) {
                        if (!noResults) {
                            noResults = document.createElement('div');
                            noResults.className = 'wghrm-no-results';
                            noResults.style.padding = '20px';
                            noResults.style.textAlign = 'center';
                            noResults.style.color = '#94a3b8';
                            noResults.style.fontSize = '13px';
                            noResults.textContent = 'No results found';
                            itemsList.appendChild(noResults);
                        }
                    } else if (noResults) {
                        noResults.remove();
                    }
                });
            }

            // Item Selection
            itemsList.addEventListener('click', (e) => {
                const item = e.target.closest('.wghrm-item');
                if (!item) return;

                const val = item.getAttribute('data-value');
                const text = item.getAttribute('data-text') || item.textContent;

                // Update UI
                itemsList.querySelectorAll('.wghrm-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                if (triggerText) triggerText.textContent = text;
                
                if (hiddenInput) {
                    hiddenInput.value = val;
                    hiddenInput.dispatchEvent(new Event('change'));
                }
                
                menu.classList.remove('show');

                // Trigger callback
                if (options.onSelect) {
                    options.onSelect(val, text);
                }
            });
        }

        // Auto-initialize all dropdowns on the page
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.wghrm-search-dropdown').forEach(dropdown => {
                if (dropdown.id) initWghrmDropdown(dropdown.id);
            });
        });

        // Re-run auto-init periodically for dynamic content
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                document.querySelectorAll('.wghrm-search-dropdown').forEach(dropdown => {
                    if (dropdown.id) initWghrmDropdown(dropdown.id);
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }

    </script>

    <style>
        /* FIX: Disable blur on Bootstrap modal-open */
        body.modal-open .nxl-header,
        body.modal-open .nxl-navigation,
        body.modal-open .page-header,
        body.modal-open .nxl-container {
            filter: none !important;
        }

        /* Restore Bootstrap backdrop behavior */
        .modal-backdrop {
            position: fixed !important;
        }

        .modal-backdrop.show {
            opacity: 0.5;
        }
    </style>

    <div id="globalLoader" style="
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(255,255,255,0.7);
    z-index:99999;
    justify-content:center;
    align-items:center;
">
        <div class="spinner"></div>
    </div>
</body>

</html>