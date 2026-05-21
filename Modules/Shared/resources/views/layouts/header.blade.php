<header class="nxl-header">
    <div class="header-wrapper">
        <div class="header-left d-flex align-items-center gap-4">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>

            {{-- nxl-navigation-toggle --}}
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">

                @php
                // Aaj ka purana time DB se nikal rahe hain
                $todayLog = \Modules\Shared\App\Models\UserWorkLog::where('user_id', auth()->id())
                ->whereDate('date', now()->toDateString())
                ->first();
                $existingSeconds = $todayLog ? $todayLog->active_seconds : 0;
                @endphp

                <div class="d-flex align-items-center bg-light text-dark px-3 py-2 rounded border">
                    <i class="fas fa-clock text-primary me-2"></i>
                    <span id="activeWorkTimer" class="fw-bold fs-5">00:00:00</span>
                    <span id="timerStatus" class="badge bg-success ms-3">Active</span>
                </div>

                {{-- Dark/Light Theme Toggle --}}
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>

                @php
                $leadsDate = \Modules\CRM\App\Models\CallBack::with('lead.user')->where('created_by', auth()->id())
                ->whereNotNull('next_followup_date')
                ->where('next_followup_date', '>', \Carbon\Carbon::now())
                ->where('is_done',0)
                ->orderBy('next_followup_date', 'asc')
                ->take(10)
                ->get();
                @endphp

                {{-- Timesheets --}}
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <i class="feather-clock"></i>
                        <span class="badge bg-success nxl-h-badge">{{ $leadsDate->count() }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-timesheets-menu">
                        <div class="d-flex justify-content-between align-items-center timesheets-head">
                            <h6 class="fw-bold text-dark mb-0">Timesheets</h6>
                            <a href="javascript:void(0);" class="fs-11 text-success ms-auto" data-bs-toggle="tooltip" title="Upcoming Timers">
                                <i class="feather-clock"></i>
                                <span>{{ $leadsDate->count() }} Upcoming</span>
                            </a>
                        </div>
                        <div class="d-flex justify-content-center align-items-center flex-column p-2">
                            <!-- <i class="feather-clock fs-1 mb-4"></i> -->
                            <div class="" style="max-height:300px; overflow:auto; width:100%;">

                                @forelse($leadsDate as $lead)

                                <div class="d-flex justify-content-between align-items-center border-bottom py-2  w-100">

                                    <!-- Left: Lead Info -->
                                    <div class="fw-semibold fs-12  p-2" style="max-width: 70%;">
                                        <a href="{{ route('lead.followUpData')}}">{{ $lead->lead->user->name}} - {{ \Illuminate\Support\Str::limit($lead->message, 80, '...') }} </a>
                                    </div>

                                    <!-- Right: Timer -->
                                    <div class="countdown-timer text-success fs-11 text-end"
                                        style="min-width: 90px;"
                                        data-time="{{ $lead->next_followup_date }}">
                                    </div>

                                </div>

                                @empty
                                <div class="text-center py-3 w-100">
                                    <p class="text-muted mb-0">No upcoming followups</p>
                                </div>
                                @endforelse

                            </div>
                            <!-- <a href="javascript:void(0);" class="btn btn-sm btn-primary">Start Timer</a> -->
                        </div>
                        <div class="text-center timesheets-footer">
                            <a href="{{ route('lead.followUpData') }}" class="fs-13 fw-semibold text-dark">All FollowUp</a>
                        </div>
                    </div>
                </div>

                {{-- Notifications (Dynamic Follow-ups) --}}
                <div class="dropdown nxl-h-item">
                    <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        @if(isset($pendingNotifications) && $pendingNotifications->count() > 0)
                        <span class="badge bg-danger nxl-h-badge">{{ $pendingNotifications->count() }}</span>
                        @endif
                    </a>

                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                        <div class="d-flex justify-content-between align-items-center notifications-head">
                            <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                        </div>

                        <div style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                            @forelse($pendingNotifications ?? [] as $notification)
                            <div class="notifications-item d-flex p-3 border-bottom hover-bg-light">
                                <div class="rounded me-3 border bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                    <i class="feather-user text-primary fs-5"></i>
                                </div>

                                <div class="notifications-desc flex-grow-1">
                                    <a href="javascript:void(0);" class="font-body text-truncate-2-line text-decoration-none">
                                        <span class="fw-semibold text-dark">{{ $notification->lead->user->name ?? 'Unknown Lead' }}</span>
                                        <br>
                                        <span class="text-muted" style="font-size: 12px;">{{ $notification->message }}</span>
                                    </a>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="notifications-date text-danger fw-medium" style="font-size: 11px;">
                                            <i class="feather-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($notification->next_followup_date)->format('d M Y, h:i A') }}
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <form action="{{ route('followup.done', $notification->id) }}" method="POST" class="m-0 p-0">
                                                @csrf
                                                <button type="submit" class="btn btn-link text-success p-0 m-0" data-bs-toggle="tooltip" title="Mark as Done" style="border:none; text-decoration:none;">
                                                    <i class="feather-check-circle fs-5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center p-4 text-muted">
                                <i class="feather-bell-off fs-1 mb-2 d-block opacity-50"></i>
                                <span class="fs-13">No pending notifications</span>
                            </div>
                            @endforelse
                        </div>

                        <div class="text-center notifications-footer border-top">
                            <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">All Notifications</a>
                        </div>
                    </div>
                </div>

                {{-- User Menu --}}
                @auth
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside" style="height: 40px; width: 40px;">
                        @if(Auth::user()->image)
                        <img src="{{ asset('storage/' . Auth::user()->image) }}" alt="profile-img" class="img-fluid user-avtar me-0" style="height: 100%; width: 100%; object-fit: cover;">
                        @else
                        <img src="/images/blank.jpeg" alt="default_Img" class="img-fluid user-avtar me-0" style="height: 100%; width: 100%; object-fit: cover;" />
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div style="height: 45px; width: 45px; margin-right: 5px;">
                                    @if(Auth::user()->image)
                                    <img src="{{ asset('storage/' . Auth::user()->image) }}" alt="profile-img" class="img-fluid user-avtar" style="height: 100%; width: 100%; object-fit: cover;">
                                    @else
                                    <img src="/images/blank.jpeg" alt="default_Img" class="img-fluid user-avtar" style="height: 100%; width: 100%; object-fit: cover;" />
                                    @endif
                                </div>
                                <div>
                                    <h6 class="text-dark mb-0">{{ Auth::user()->name }} <span class="badge bg-soft-success text-success ms-1">{{ Auth::user()->role?->name ?? 'No Role'}}</span></h6>
                                    <span class="fs-12 fw-medium text-muted">{{ Auth::user()->email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/profile" class="dropdown-item">
                            <i class="feather-user"></i> Profile Details
                        </a>
                        <a href="{{ route('user.activity') }}" class="dropdown-item">
                            <i class="feather-activity"></i> Activity Feed
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-bell"></i> Notifications
                        </a>
                        <div class="dropdown-divider"></div>
                        {{-- Logout --}}
                        <form action="{{ route('logout') }}" method="POST" class="dropdown-item p-0 m-0">
                            @csrf
                            <button type="submit" class="btn w-100 text-start">
                                <i class="feather-log-out"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
                @endauth

            </div>
        </div>
    </div>
    <style>
        .blink {
            animation: blink-animation 1s infinite;
        }

        @keyframes blink-animation {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Server se aaj ki date aur time lein
            let dbSeconds = parseInt("{{ $existingSeconds ?? 0 }}") || 0;
            let serverDate = "{{ now('Asia/Kolkata')->toDateString() }}";

            // 2. LocalStorage Check (Naya din shuru hone par refresh)
            if (localStorage.getItem('crm_work_date') !== serverDate) {
                localStorage.setItem('crm_work_seconds', dbSeconds);
                localStorage.setItem('crm_work_date', serverDate);
            }

            // 3. Time dono me se jo zyada ho wo set karein (DB vs Local Tab Data)
            let localSeconds = parseInt(localStorage.getItem('crm_work_seconds')) || 0;
            let workSeconds = Math.max(dbSeconds, localSeconds);
            localStorage.setItem('crm_work_seconds', workSeconds);

            const IDLE_LIMIT = 30; // 30 seconds
            const timerDisplay = document.getElementById('activeWorkTimer');
            const timerStatus = document.getElementById('timerStatus');

            function formatTime(totalSeconds) {
                let h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
                let m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
                let s = (totalSeconds % 60).toString().padStart(2, '0');
                return `${h}:${m}:${s}`;
            }

            if (timerDisplay) timerDisplay.innerText = formatTime(workSeconds);

            function updateUIStatus(text, className) {
                if (timerStatus && timerStatus.innerText !== text) {
                    timerStatus.innerText = text;
                    timerStatus.className = className;
                }
            }

            function saveTimeToDatabase(timeInSeconds) {
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfMeta) return;

                fetch("{{ route('save.work.time') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
                    },
                    body: JSON.stringify({
                        active_time_seconds: timeInSeconds
                    })
                }).catch(e => console.log(e));
            }

            // 🟢 4. CROSS-TAB ACTIVITY SYNC
            // Kisi bhi tab me activity ho toh localStorage update karo
            function markActive() {
                localStorage.setItem('crm_last_activity', Date.now());
            }
            if (!localStorage.getItem('crm_last_activity')) markActive();

            ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll'].forEach(event => {
                document.addEventListener(event, markActive, {
                    passive: true
                });
            });

            // 🟢 5. MAIN TIMER LOOP (Har 1 second me chalta hai)
            setInterval(() => {
                let currentHour = new Date().getHours();
                let isWorkingHour = (currentHour >= 10 && currentHour < 19);

                // Kisi bhi tab me aakhiri activity kab hui thi?
                let lastActivity = parseInt(localStorage.getItem('crm_last_activity')) || 0;
                let isUserActive = (Date.now() - lastActivity) < (IDLE_LIMIT * 1000);

                if (!isWorkingHour) {
                    updateUIStatus("Off Duty", "badge bg-secondary ms-3");
                    return;
                }

                if (!isUserActive) {
                    updateUIStatus("Paused", "badge bg-warning ms-3");
                    return;
                }

                updateUIStatus("Active", "badge bg-success ms-3");

                // 🟢 CROSS-TAB TIMER SYNC (Sirf ek tab count karega, baaki sirf copy karenge)
                let lastTick = parseInt(localStorage.getItem('crm_last_tick')) || 0;
                let now = Date.now();

                if (now - lastTick >= 900) {
                    // Main tab hu, main +1 karunga
                    workSeconds++;
                    localStorage.setItem('crm_work_seconds', workSeconds);
                    localStorage.setItem('crm_last_tick', now);
                } else {
                    // Dusra tab count kar chuka hai, main sirf copy karunga
                    workSeconds = parseInt(localStorage.getItem('crm_work_seconds')) || workSeconds;
                }

                if (timerDisplay) timerDisplay.innerText = formatTime(workSeconds);

            }, 1000);

            // 6. DB Backup Save (Har 15 seconds me)
            setInterval(() => {
                let currentSeconds = parseInt(localStorage.getItem('crm_work_seconds')) || workSeconds;
                saveTimeToDatabase(currentSeconds);
            }, 15000);

            window.addEventListener('beforeunload', function() {
                saveTimeToDatabase(workSeconds);
            });

            setInterval(() => {
                document.querySelectorAll('.countdown-timer').forEach(el => {

                    let time = el.getAttribute('data-time');
                    if (!time) return;

                    let diff = new Date(time).getTime() - new Date().getTime();

                    if (diff <= 0) {
                        el.innerHTML = "⛔ Overdue";
                        el.classList.remove('text-success');
                        el.classList.add('text-danger');
                        return;
                    }
                    let minutesLeft = diff / (1000 * 60);

                    let h = Math.floor(diff / (1000 * 60 * 60));
                    let m = Math.floor((diff / (1000 * 60)) % 60);
                    let s = Math.floor((diff / 1000) % 60);

                    el.innerHTML = `${h}h ${m}m ${s}s`;

                    if (minutesLeft <= 15) {
                        el.classList.remove('text-success');
                        el.classList.add('text-danger', 'blink');
                    } else {
                        el.classList.remove('text-danger', 'blink');
                        el.classList.add('text-success');
                    }

                });
            }, 1000);
        });
    </script>
    @endpush
</header>