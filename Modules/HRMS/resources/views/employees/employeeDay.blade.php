@extends('shared::layouts.app')

@section('content')

<div class="celebrations-container">

    <!-- Hero Header -->
    <div class="celebrations-hero">
        <div class="hero-content">
            <h1 class="hero-title">Team <span class="gradient-text">Milestones</span></h1>
            <p class="hero-subtitle">Celebrating the people who make our company great.</p>
        </div>

        <div class="celebration-switcher">
            <button class="switcher-btn active" onclick="switchCelebrationTab('birthday', this)">
                <span class="icon">🎂</span> Birthdays
            </button>
            <button class="switcher-btn" onclick="switchCelebrationTab('anniversary', this)">
                <span class="icon">🎖️</span> Anniversaries
            </button>
        </div>
    </div>

    <!-- Birthday Section -->
    <div id="tab-birthday" class="celebration-section">
        <div class="premium-grid">
            @php $birthdayCount = 0; @endphp
            @foreach ($employees as $employee)
                @php
                    $birthday = \Carbon\Carbon::parse($employee->date_of_birth)->year(now()->year);
                    if ($birthday->isPast() && !$birthday->isToday()) {
                        $birthday->addYear();
                    }
                    $today = now()->startOfDay();
                    $daysRemaining = $today->diffInDays($birthday->startOfDay(), false);
                @endphp

                @if ($daysRemaining >= 0 && $daysRemaining <= 3)
                    @php $birthdayCount++; @endphp
                    <div class="premium-card-wrapper animate-card">
                        <div class="premium-card birthday-theme">
                            <div class="card-glow"></div>
                            <div class="premium-card-body">
                                <div class="premium-profile-section">
                                    <div class="premium-avatar-container">
                                        @if($employee->photo)
                                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" class="avatar-img">
                                        @else
                                            <div class="avatar-initials">
                                                {{ substr($employee->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="floating-icon birthday-icon">🎂</div>
                                    </div>
                                </div>
                                
                                <div class="premium-info-section">
                                    <h3 class="emp-name">{{ $employee->name }}</h3>
                                    <p class="emp-label">Birthday Celebration</p>
                                    
                                    <div class="premium-card-footer">
                                        <div class="date-info">
                                            <span class="date-text">{{ $birthday->format('d M, Y') }}</span>
                                        </div>
                                        <div class="status-indicator {{ $daysRemaining == 0 ? 'is-today' : 'is-upcoming' }}">
                                            @if($daysRemaining == 0)
                                                Today! 🎉
                                            @else
                                                {{ $daysRemaining }} Days Left
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if($birthdayCount == 0)
            <div class="empty-state-premium">
                <div class="empty-icon">🎂</div>
                <h3>No Birthdays Soon</h3>
                <p>No team members have birthdays in the next 3 days.</p>
            </div>
        @endif
    </div>

    <!-- Anniversary Section -->
    <div id="tab-anniversary" class="celebration-section d-none">
        <div class="premium-grid">
            @php $anniversaryCount = 0; @endphp
            @foreach ($employees as $employee)
                @php
                    $joiningDate = \Carbon\Carbon::parse($employee->date_of_joining);
                    $anniversary = $joiningDate->copy()->year(now()->year);
                    if ($anniversary->isPast() && !$anniversary->isToday()) {
                        $anniversary->addYear();
                    }
                    $today = now()->startOfDay();
                    $daysRemaining = $today->diffInDays($anniversary->startOfDay(), false);
                    $years = $joiningDate->diffInYears($anniversary);
                @endphp

                @if($years > 0 && $daysRemaining >= 0 && $daysRemaining <= 3)
                    @php $anniversaryCount++; @endphp
                    <div class="premium-card-wrapper animate-card">
                        <div class="premium-card anniversary-theme">
                            <div class="card-glow"></div>
                            <div class="premium-card-body">
                                <div class="premium-profile-section">
                                    <div class="premium-avatar-container">
                                        @if($employee->photo)
                                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}" class="avatar-img">
                                        @else
                                            <div class="avatar-initials">
                                                {{ substr($employee->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="floating-icon anniversary-icon">🏆</div>
                                    </div>
                                </div>
                                
                                <div class="premium-info-section">
                                    <h3 class="emp-name">{{ $employee->name }}</h3>
                                    <p class="emp-label">{{ $years }}{{ $years == 1 ? 'st' : ($years == 2 ? 'nd' : ($years == 3 ? 'rd' : 'th')) }} Work Anniversary</p>
                                    
                                    <div class="premium-card-footer">
                                        <div class="date-info">
                                            <span class="date-text">{{ $anniversary->format('d M, Y') }}</span>
                                        </div>
                                        <div class="status-indicator {{ $daysRemaining == 0 ? 'is-today' : 'is-upcoming' }}">
                                            @if($daysRemaining == 0)
                                                Today! 🎊
                                            @else
                                                {{ $daysRemaining }} Days Left
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if($anniversaryCount == 0)
            <div class="empty-state-premium">
                <div class="empty-icon">🏆</div>
                <h3>No Anniversaries Soon</h3>
                <p>No work anniversaries in the next 3 days.</p>
            </div>
        @endif
    </div>

</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');

    .celebrations-container {
        --bg-main: #f0f4f9;
        --card-bg: #ffffff;
        --primary: #6366f1;
        --secondary: #a855f7;
        --text-dark: #0f172a;
        --text-light: #64748b;
        --birthday-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --anniversary-gradient: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.08);
        --shadow-strong: 0 20px 50px -12px rgba(99, 102, 241, 0.15);
        
        padding: 30px;
        font-family: 'Outfit', sans-serif;
        box-sizing: border-box;
    }

    /* Fix for squashed layout on mobile */
    @media (max-width: 1024px) {
        .nxl-container {
            margin-left: 0 !important;
            padding-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .nxl-content {
            padding: 0 !important;
            margin: 0 !important;
        }
    }

    .celebrations-container * {
        box-sizing: border-box;
    }

    /* Hero Header */
    .celebrations-hero {
        text-align: center;
        margin-bottom: 60px;
    }

    .hero-title {
        font-size: 48px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 12px;
        letter-spacing: -0.02em;
    }

    .gradient-text {
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: 18px;
        color: var(--text-light);
        margin-bottom: 40px;
    }

    /* Switcher */
    .celebration-switcher {
        display: inline-flex;
        background: #fff;
        padding: 8px;
        border-radius: 24px;
        box-shadow: var(--shadow-soft);
        gap: 8px;
    }

    .switcher-btn {
        border: none;
        background: transparent;
        padding: 12px 32px;
        border-radius: 18px;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-light);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .switcher-btn.active {
        background: var(--text-dark);
        color: #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    /* Grid - 3 per row */
    .premium-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Premium Card */
    .premium-card-wrapper {
        perspective: 2000px;
    }

    .premium-card {
        background: var(--card-bg);
        border-radius: 32px;
        padding: 32px;
        position: relative;
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: var(--shadow-soft);
        border: 1px solid rgba(255,255,255,0.8);
        min-height: 200px;
    }

    .premium-card:hover {
        transform: translateY(-15px) rotateX(5deg) rotateY(-5deg);
        box-shadow: var(--shadow-strong);
    }

    .card-glow {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
        pointer-events: none;
        transition: all 0.5s ease;
    }

    .premium-card:hover .card-glow {
        background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
    }

    .premium-card-body {
        display: flex;
        align-items: center;
        gap: 28px;
        position: relative;
        z-index: 1;
    }

    /* Profile Section */
    .premium-profile-section {
        flex-shrink: 0;
    }

    .premium-avatar-container {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        padding: 6px;
        background: #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        position: relative;
    }

    .avatar-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .avatar-initials {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: var(--primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
    }

    .floating-icon {
        position: absolute;
        bottom: -2px;
        right: -2px;
        background: #fff;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        border: 3px solid #fff;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .premium-card:hover .floating-icon {
        transform: scale(1.2) rotate(15deg);
        box-shadow: 0 12px 20px rgba(0,0,0,0.2);
    }

    /* Info Section */
    .premium-info-section {
        flex: 1;
    }

    .emp-name {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
        letter-spacing: -0.01em;
    }

    .emp-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 24px;
    }

    .premium-card-footer {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .date-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-light);
        font-weight: 500;
    }

    .status-indicator {
        display: inline-flex;
        padding: 6px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        width: fit-content;
    }

    .is-today {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
        animation: pulse-border 2s infinite;
    }

    .is-upcoming {
        background: #f0f9ff;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }

    .is-passed {
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
    }

    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(225, 29, 72, 0); }
        100% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
    }

    /* Animations */
    .animate-card {
        animation: slideUp 0.8s cubic-bezier(0.2, 1, 0.3, 1) both;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 1400px) {
        .premium-grid {
            max-width: 100%;
            gap: 24px;
        }
    }

    @media (max-width: 1200px) {
        .premium-grid { grid-template-columns: repeat(2, 1fr); }
        .hero-title { font-size: 40px; }
    }

    @media (max-width: 992px) {
        .celebrations-container { padding: 25px; }
        .hero-title { font-size: 36px; }
        .hero-subtitle { font-size: 16px; }
    }

    @media (max-width: 768px) {
        .premium-grid { 
            grid-template-columns: 1fr; 
            gap: 20px; 
            width: 100%;
        }
        .celebrations-container { padding: 20px 15px; }
        .celebrations-hero { margin-bottom: 40px; }
        .hero-title { font-size: 32px; }
        .hero-subtitle { font-size: 14px; margin-bottom: 30px; }
        
        .celebration-switcher {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        
        .switcher-btn {
            padding: 10px 16px;
            font-size: 14px;
            flex: 1;
            justify-content: center;
        }

        .premium-card {
            padding: 24px;
            border-radius: 24px;
            width: 100%;
            min-height: auto;
        }

        .premium-card-body {
            gap: 24px;
        }

        .premium-avatar-container {
            width: 100px;
            height: 100px;
        }

        .avatar-initials {
            font-size: 36px;
        }

        .floating-icon {
            width: 36px;
            height: 36px;
            font-size: 20px;
        }

        .emp-name {
            font-size: 22px;
        }

        .emp-label {
            font-size: 12px;
            margin-bottom: 16px;
        }
    }

    @media (max-width: 600px) {
        .hero-title { font-size: 28px; }
        
        .celebration-switcher {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            max-width: 300px !important;
            margin: 0 auto !important;
            border-radius: 20px !important;
            padding: 6px !important;
            gap: 8px !important;
        }
        
        .switcher-btn {
            width: 100%;
            border-radius: 14px;
            padding: 12px;
        }

        .premium-card-body { 
            display: flex !important;
            flex-direction: column !important; 
            text-align: center !important; 
            gap: 20px !important;
            align-items: center !important;
        }
        
        .premium-avatar-container {
            margin: 0 auto;
        }

        .premium-card-footer {
            align-items: center;
        }

        .status-indicator {
            margin: 0 auto;
        }
        
        .empty-state-premium {
            padding: 40px 20px;
            border-radius: 24px;
        }

        .empty-icon {
            font-size: 60px;
        }

        .empty-state-premium h3 {
            font-size: 22px;
        }
    }

    .d-none { display: none; }

    /* Empty State */
    .empty-state-premium {
        background: white;
        border-radius: 40px;
        padding: 80px 40px;
        text-align: center;
        border: 3px dashed #e2e8f0;
        margin-top: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        grid-column: 1 / -1; /* Span full grid width */
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 24px;
        animation: float-emoji 3s ease-in-out infinite;
    }

    @keyframes float-emoji {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-10px) scale(1.1); }
    }

    .empty-state-premium h3 {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 12px;
    }

    .empty-state-premium p {
        font-size: 16px;
        color: var(--text-light);
        max-width: 400px;
    }

</style>

<script>
    function switchCelebrationTab(tabId, el) {
        document.querySelectorAll('.switcher-btn').forEach(btn => btn.classList.remove('active'));
        el.classList.add('active');

        document.querySelectorAll('.celebration-section').forEach(sec => sec.classList.add('d-none'));
        const target = document.getElementById('tab-' + tabId);
        target.classList.remove('d-none');

        // Re-trigger animations
        const cards = target.querySelectorAll('.animate-card');
        cards.forEach((card, index) => {
            card.style.animation = 'none';
            card.offsetHeight; // reflow
            card.style.animation = null;
            card.style.animationDelay = (index * 0.1) + 's';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialCards = document.querySelectorAll('#tab-birthday .animate-card');
        initialCards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
        });
    });
</script>

@endsection