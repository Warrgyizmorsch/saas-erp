@extends('shared::layouts.app')
@section('content')

    @php
    $statusClasses = [
    'new' => 'bg-soft-primary text-primary',
    'in_progress' => 'bg-soft-warning text-warning',
    'completed' => 'bg-soft-success text-success',
    'cancelled' => 'bg-soft-danger text-danger',
    'hold' => 'bg-soft-danger text-danger',
    ];

    $priorityClasses = [
    'HIGH' => 'bg-soft-danger text-danger',
    'LOW' => 'bg-soft-warning text-warning',
    'NORMAL' => 'bg-soft-success text-success',
    'URGENT' => 'bg-soft-primary text-primary',
    ];

    $machineProgress = [
    'Fabrication' => 25,
    'Assembly' => 50,
    'Machining' => 75,
    'Completed' => 100,
    ];
    @endphp

    {{-- ═══════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════ --}}
    <div class="ps-header">
        <div class="ps-header-left">

            <div class="ps-header-breadcrumb">
                <a href="{{ route('project.index') }}">Projects</a>
                <i class="feather feather-chevron-right"></i>
                <span>{{ $project->name }}</span>
            </div>

            <h1 class="ps-header-title">{{ $project->name }}</h1>

            <div class="ps-header-badges">

                <span class="ps-badge ps-badge--{{ $project->status }}">
                    <span class="ps-badge-dot"></span>
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>

                <span class="ps-badge ps-badge--priority-{{ strtolower($project->priority) }}">
                    {{ $project->priority }}
                </span>

                @if($project->refurbish)
                <span class="ps-badge ps-badge--refurb">
                    <i class="feather feather-refresh-cw"></i>
                    Refurbished
                </span>
                @endif

            </div>

        </div>

        <div class="ps-header-actions">

            <a href="{{ route('project.edit', $project->id) }}" class="ps-btn ps-btn--primary">
                <i class="feather feather-edit-3"></i>
                Edit Project
            </a>

            <a href="{{ route('project.index') }}" class="ps-btn ps-btn--ghost">
                <i class="feather feather-arrow-left"></i>
                Back
            </a>

        </div>
    </div>


    <div class="ps-wrap">

        {{-- ═══════════════════════════════════════════
         KPI STRIP
    ═══════════════════════════════════════════ --}}
        <div class="ps-kpi-strip">

            <div class="ps-kpi">
                <div class="ps-kpi-icon ps-kpi-icon--blue">
                    <i class="feather feather-dollar-sign"></i>
                </div>
                <div class="ps-kpi-body">
                    <div class="ps-kpi-label">Total Budget</div>
                    <div class="ps-kpi-value">₹ {{ number_format($project->budget) }}</div>
                </div>
            </div>

            <div class="ps-kpi ps-kpi--progress">
                <div class="ps-kpi-icon ps-kpi-icon--green">
                    <i class="feather feather-trending-up"></i>
                </div>
                <div class="ps-kpi-body">
                    <div class="ps-kpi-label">Overall Progress</div>
                    <div class="ps-kpi-value">{{ $project->progress }}<span class="ps-kpi-unit">%</span></div>
                    <div class="ps-kpi-bar-track">
                        <div class="ps-kpi-bar-fill" style="width:{{ $project->progress }}%"></div>
                    </div>
                </div>
            </div>

            <div class="ps-kpi">
                <div class="ps-kpi-icon ps-kpi-icon--amber">
                    <i class="feather feather-calendar"></i>
                </div>
                <div class="ps-kpi-body">
                    <div class="ps-kpi-label">Start Date</div>
                    <div class="ps-kpi-value ps-kpi-value--date">
                        {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                    </div>
                </div>
            </div>

            <div class="ps-kpi">
                <div class="ps-kpi-icon ps-kpi-icon--red">
                    <i class="feather feather-clock"></i>
                </div>
                <div class="ps-kpi-body">
                    <div class="ps-kpi-label">End Date</div>
                    <div class="ps-kpi-value ps-kpi-value--date">
                        {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}
                    </div>
                </div>
            </div>

        </div>

        {{-- =========================================
WORKFLOW ANALYTICS
========================================= --}}

        <div class="row g-4 mb-4">

            {{-- LEFT CHART --}}
            <div class="col-lg-5">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-4">

                            <div>
                                <h5 class="fw-bold mb-1">
                                    Workflow Analytics
                                </h5>

                                <p class="text-muted mb-0">
                                    Stage completion overview
                                </p>
                            </div>

                        </div>

                        <div class="workflow-chart-wrap">

                            <div id="workflowChart"></div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RIGHT STATUS --}}
            <div class="col-lg-7">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="mb-4">

                            <h5 class="fw-bold mb-1">
                                Current Workflow Status
                            </h5>

                            <p class="text-muted mb-0">
                                Running stages overview
                            </p>

                        </div>

                        @foreach($stageAnalytics as $stage)

                        <div class="mb-4">

                            <div class="d-flex justify-content-between mb-2">

                                <div class="d-flex align-items-center gap-2">

                                    <span
                                        style="
                                    width:12px;
                                    height:12px;
                                    border-radius:50%;
                                    display:inline-block;
                                    background:{{ $stage['color'] }};
                                ">
                                    </span>

                                    <strong>
                                        {{ $stage['stage_name'] }}
                                    </strong>

                                </div>

                                <strong>
                                    {{ $stage['percentage'] }}%
                                </strong>

                            </div>

                            <div class="small text-muted mb-2">

                                @if($stage['main_stage_running'])
                                <span class="badge bg-warning text-dark">
                                    Stage In Progress
                                </span>
                                @endif


                                @if(count($stage['running_sub_stages']) > 0)

                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    Current:

                                    @foreach($stage['running_sub_stages'] as $runningSub)

                                    <span class="badge bg-info text-dark">

                                        {{ $runningSub }}

                                    </span>

                                    @endforeach

                                </div>

                                @endif
                            </div>

                            <div class="ps-stage-segments">

                                {{-- Agar sub stages hain --}}
                                @if(count($stage['all_sub_stages']) > 0)

                                @foreach($stage['all_sub_stages'] as $subStage)

                                @php
                                $isCompleted = in_array(
                                $subStage['id'],
                                $stage['completed_stage_ids']
                                );
                                @endphp

                                <div
                                    class="ps-stage-segment {{ $isCompleted ? 'is-complete' : '' }}"
                                    style="
                    background:
                    {{ $isCompleted
                        ? $stage['color']
                        : '#e5e7eb' }};
                "
                                    title="{{ $subStage['name'] }}">
                                </div>

                                @endforeach

                                @else

                                {{-- Fallback single progress bar --}}
                               {{-- Fallback single progress bar --}}
                                <div class="ps-stage-segment-wrapper">
                                    
                                    <div
                                        class="ps-stage-segment"
                                        style="
                                            width: {{ $stage['percentage'] }}%;
                                            background: {{ $stage['percentage'] > 0
                                                ? $stage['color']
                                                : '#e5e7eb' }};
                                            height: 100%;
                                            border-radius: 999px;
                                        ">
                                    </div>
                                
                                </div>

                                @endif

                            </div>

                            <div class="small text-muted mt-2">

                                @if($stage['total_subs'] > 0)

                                {{ $stage['completed_subs'] }}
                                /
                                {{ $stage['total_subs'] }}
                                sub stages completed

                                @else

                                No sub stages available

                                @endif

                            </div>

                        </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

        {{-- ═══════════════════════════════════════════
         BODY GRID  (Overview left | Machines right)
    ═══════════════════════════════════════════ --}}
        <div class="ps-body-grid">

            {{-- LEFT — Project Overview --}}
            <div class="ps-panel">

                <div class="ps-panel-header">
                    <div>
                        <div class="ps-panel-title">Project Overview</div>
                        <div class="ps-panel-sub">Complete project information</div>
                    </div>
                    <span class="ps-active-badge">● Active</span>
                </div>

                <div class="ps-panel-body">

                    <div class="ps-overview-grid">

                        <div class="ps-overview-box">
                            <div class="ps-ov-label">Project Owner</div>
                            <div class="ps-ov-value">{{ $project->user->name ?? '—' }}</div>
                        </div>

                        <div class="ps-overview-box">
                            <div class="ps-ov-label">Total Machines</div>
                            <div class="ps-ov-value ps-ov-value--big">
                                {{ $project->projectProducts->count() }}
                            </div>
                        </div>

                        <div class="ps-overview-box ps-overview-box--success">
                            <div class="ps-ov-label">Completed</div>
                            <div class="ps-ov-value ps-ov-value--big text-success">
                                {{ $project->projectProducts->where('status','Completed')->count() }}
                            </div>
                        </div>

                        <div class="ps-overview-box ps-overview-box--warn">
                            <div class="ps-ov-label">In Progress</div>
                            <div class="ps-ov-value ps-ov-value--big text-warning">
                                {{ $project->projectProducts->whereNotIn('status',['Completed'])->count() }}
                            </div>
                        </div>

                    </div>

                    {{-- Note --}}
                    @if($project->comment)
                    <div class="ps-note">
                        <div class="ps-note-header">
                            <div class="ps-note-icon"><i class="feather feather-message-circle"></i></div>
                            <div>
                                <div class="ps-note-title">Project Note</div>
                                <div class="ps-note-sub">Latest update</div>
                            </div>
                        </div>
                        <div class="ps-note-body">{{ $project->comment }}</div>
                    </div>
                    @endif

                </div>

            </div>


            {{-- RIGHT — Machines Accordion --}}
            <div class="ps-panel ps-panel--machines">

                <div class="ps-panel-header">
                    <div>
                        <div class="ps-panel-title">Machines & Items</div>
                        <div class="ps-panel-sub">Click a machine to view installed items</div>
                    </div>
                    <div class="ps-machine-count-badge">
                        {{ $project->projectProducts->count() }}
                        <span>machines</span>
                    </div>
                </div>

                <div class="ps-panel-body ps-panel-body--accordion">

                    @forelse($project->projectProducts as $index => $machine)

                    @php
                    $totalItems = $machine->product->productItems->count();

                    $completedItems = $machine->product->productItems->filter(function($item){
                    return ($item->issued_qty ?? 0) >= ($item->quantity ?? 0) && ($item->quantity ?? 0) > 0;
                    })->count();

                    $progress = $totalItems > 0
                    ? round(($completedItems / $totalItems) * 100)
                    : 0;

                    $exceededItems = $machine->product->productItems
                    ->filter(fn($i) => ($i->issued_qty ?? 0) > ($i->quantity ?? 0))
                    ->count();

                    $progressColor = $progress == 100
                    ? '#10b981'
                    : ($progress >= 50 ? '#f59e0b' : '#6366f1');
                    @endphp

                    <div class="ps-machine {{ $exceededItems > 0 ? 'ps-machine--alert' : '' }}"
                        id="pm-{{ $index }}">

                        {{-- Accordion Trigger --}}
                        <button class="ps-machine-trigger"
                            onclick="toggleMachine({{ $index }})"
                            aria-expanded="false"
                            aria-controls="pmc-{{ $index }}">

                            {{-- Machine icon --}}
                            <div class="ps-mtrig-icon">
                                <i class="feather feather-cpu"></i>
                            </div>

                            {{-- Machine info --}}
                            <div class="ps-mtrig-info">
                                <div class="ps-mtrig-name">{{ $machine->product->name ?? '—' }}</div>
                                <div class="ps-mtrig-meta">
                                    <span class="ps-mtrig-code">{{ $machine->product->code ?? '—' }}</span>
                                    <span class="ps-mtrig-sep">·</span>
                                    <span class="ps-mstatus ps-mstatus--{{ strtolower(str_replace(' ','_',$machine->status ?? '')) }}">
                                        {{ $machine->status ?? '—' }}
                                    </span>
                                    <span class="ps-mtrig-sep">·</span>

                                </div>
                            </div>

                            {{-- Right side --}}
                            <div class="ps-mtrig-right">

                                @if($exceededItems > 0)
                                <div class="ps-exceed-pill">
                                    <i class="feather feather-alert-triangle"></i>
                                    {{ $exceededItems }} exceeded
                                </div>
                                @endif

                                <div class="ps-mprogress">
                                    <div class="ps-mprogress-val" style="color:{{ $progressColor }}">
                                        {{ $progress }}%
                                    </div>
                                    <div class="ps-mprogress-track">
                                        <div class="ps-mprogress-fill"
                                            style="width:{{ $progress }}%; background:{{ $progressColor }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="ps-mtrig-items-count">
                                    {{ $completedItems }}/{{ $totalItems }} items
                                </div>

                                <div class="ps-mtrig-chevron">
                                    <i class="feather feather-chevron-down"></i>
                                </div>

                            </div>

                        </button>

                        {{-- Accordion Body --}}
                        <div class="ps-machine-body" id="pmc-{{ $index }}">
                            <div class="ps-machine-body-inner">

                                <div class="ps-items-grid">

                                    @forelse($machine->product->productItems as $item)

                                    @php
                                    $issued = $item->issued_qty ?? 0;
                                    $required = $item->quantity ?? 0;
                                    $exceeded = $issued >= $required && $required > 0;
                                    $exceedAmt = max(0, $issued - $required);
                                    $remaining = max(0, $required - $issued);
                                    $fillPct = $required > 0 ? min(100, round(($issued / $required) * 100)) : 0;
                                    @endphp

                                    <div class="ps-item {{ $exceeded ? 'ps-item--exceeded' : '' }}">

                                        <div class="ps-item-main">

                                            <div class="ps-item-name">
                                                {{ $item->inventory->name ?? '—' }}
                                            </div>

                                            {{-- mini fill bar --}}
                                            <div class="ps-item-fill-track">
                                                <div class="ps-item-fill-bar {{ $exceeded ? 'ps-item-fill-bar--over' : '' }}"
                                                    style="width: {{ $fillPct }}%">
                                                </div>
                                            </div>

                                            <div class="ps-item-chips">
                                                <span class="ps-chip ps-chip--req">Req {{ $required }}</span>
                                                <span class="ps-chip ps-chip--issued">Issued {{ $issued }}</span>
                                                @if($exceeded)
                                                <span class="ps-chip ps-chip--over">+{{ $exceedAmt }} over</span>
                                                @else
                                                <span class="ps-chip ps-chip--left">{{ $remaining }} left</span>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="ps-item-icon">
                                            @if($exceeded)
                                            <div class="ps-iicon ps-iicon--warn" title="Qty Exceeded">
                                                <i class="feather feather-arrow-right-circle"></i>
                                            </div>
                                            @else
                                            <div class="ps-iicon ps-iicon--ok">
                                                <i class="feather feather-check-circle"></i>
                                            </div>
                                            @endif
                                        </div>

                                    </div>

                                    @empty
                                    <div class="ps-items-empty">No items found for this machine.</div>
                                    @endforelse

                                </div>

                            </div>
                        </div>

                    </div>

                    @empty
                    <div class="ps-machines-empty">
                        <i class="feather feather-inbox"></i>
                        <p>No machines assigned to this project yet.</p>
                    </div>
                    @endforelse

                </div>

            </div>

        </div>

    </div>


    {{-- ═══════════════════════════════════════════
     STYLES
═══════════════════════════════════════════ --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap');

        /* ── Root Variables ──────────────────────────────── */
        :root {
            --ps-bg: #f3f4f8;
            --ps-surface: #ffffff;
            --ps-border: #e8eaf0;
            --ps-border-soft: #f0f1f6;
            --ps-text: #111827;
            --ps-text-muted: #6b7280;
            --ps-text-light: #9ca3af;
            --ps-primary: #4f46e5;
            --ps-primary-lt: #eef2ff;
            --ps-green: #10b981;
            --ps-green-lt: #ecfdf5;
            --ps-amber: #f59e0b;
            --ps-amber-lt: #fffbeb;
            --ps-red: #ef4444;
            --ps-red-lt: #fef2f2;
            --ps-blue: #3b82f6;
            --ps-blue-lt: #eff6ff;
            --ps-radius-sm: 10px;
            --ps-radius: 16px;
            --ps-radius-lg: 22px;
            --ps-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            --ps-shadow-md: 0 8px 32px rgba(0, 0, 0, 0.09);
            --ps-font: 'Sora', sans-serif;
            --ps-font-body: 'DM Sans', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        /* ── Layout ──────────────────────────────────────── */
        .ps-wrap {
            padding: 0 28px 40px;
            font-family: var(--ps-font-body);
        }

        .ps-stage-segment-wrapper{
    width: 100%;
    height: 10px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}

        /* ── Page Header ─────────────────────────────────── */
        .ps-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 28px 28px 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .ps-header-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--ps-text-muted);
            margin-bottom: 8px;
            font-family: var(--ps-font-body);
        }

        .ps-header-breadcrumb a {
            color: var(--ps-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .ps-header-breadcrumb a:hover {
            text-decoration: underline;
        }

        .ps-header-breadcrumb i {
            font-size: 11px;
        }

        .ps-header-title {
            font-family: var(--ps-font);
            font-size: 26px;
            font-weight: 800;
            color: var(--ps-text);
            margin: 0 0 12px;
            letter-spacing: -0.5px;
        }

        .ps-header-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Badges */
        .ps-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            font-family: var(--ps-font);
        }

        .ps-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .ps-badge--new {
            background: var(--ps-primary-lt);
            color: var(--ps-primary);
        }

        .ps-badge--new .ps-badge-dot {
            background: var(--ps-primary);
        }

        .ps-badge--in_progress {
            background: var(--ps-amber-lt);
            color: var(--ps-amber);
        }

        .ps-badge--in_progress .ps-badge-dot {
            background: var(--ps-amber);
        }

        .ps-badge--completed {
            background: var(--ps-green-lt);
            color: var(--ps-green);
        }

        .ps-badge--completed .ps-badge-dot {
            background: var(--ps-green);
        }

        .ps-badge--cancelled,
        .ps-badge--hold {
            background: var(--ps-red-lt);
            color: var(--ps-red);
        }

        .ps-badge--cancelled .ps-badge-dot,
        .ps-badge--hold .ps-badge-dot {
            background: var(--ps-red);
        }

        .ps-badge--priority-high {
            background: #fef2f2;
            color: #b91c1c;
        }

        .ps-badge--priority-low {
            background: #fffbeb;
            color: #92400e;
        }

        .ps-badge--priority-normal {
            background: #ecfdf5;
            color: #065f46;
        }

        .ps-badge--priority-urgent {
            background: #eef2ff;
            color: #3730a3;
        }

        .ps-badge--refurb {
            background: #fefce8;
            color: #713f12;
            border: 1px solid #fef08a;
        }

        /* Header Buttons */
        .ps-header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .ps-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--ps-radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            font-family: var(--ps-font);
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .ps-btn--primary {
            background: var(--ps-primary);
            color: #fff;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .ps-btn--primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .ps-btn--ghost {
            background: var(--ps-surface);
            color: var(--ps-text-muted);
            border: 1.5px solid var(--ps-border);
        }

        .ps-btn--ghost:hover {
            background: var(--ps-bg);
            color: var(--ps-text);
        }

        /* ── KPI Strip ───────────────────────────────────── */
        .ps-kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width:1024px) {
            .ps-kpi-strip {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:580px) {
            .ps-kpi-strip {
                grid-template-columns: 1fr;
            }
        }

        .ps-kpi {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: box-shadow 0.2s, transform 0.2s;
            box-shadow: var(--ps-shadow);
        }

        .ps-kpi:hover {
            transform: translateY(-2px);
            box-shadow: var(--ps-shadow-md);
        }

        .ps-kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .ps-kpi-icon--blue {
            background: var(--ps-blue-lt);
            color: var(--ps-blue);
        }

        .ps-kpi-icon--green {
            background: var(--ps-green-lt);
            color: var(--ps-green);
        }

        .ps-kpi-icon--amber {
            background: var(--ps-amber-lt);
            color: var(--ps-amber);
        }

        .ps-kpi-icon--red {
            background: var(--ps-red-lt);
            color: var(--ps-red);
        }

        .ps-kpi-body {
            flex: 1;
            min-width: 0;
        }

        .ps-kpi-label {
            font-size: 12px;
            color: var(--ps-text-muted);
            font-weight: 500;
            margin-bottom: 4px;
            font-family: var(--ps-font-body);
        }

        .ps-kpi-value {
            font-family: var(--ps-font);
            font-size: 22px;
            font-weight: 800;
            color: var(--ps-text);
            line-height: 1.1;
        }

        .ps-kpi-value--date {
            font-size: 16px;
            font-weight: 700;
        }

        .ps-kpi-unit {
            font-size: 14px;
            font-weight: 500;
            color: var(--ps-text-muted);
        }

        .ps-kpi-bar-track {
            height: 5px;
            background: #e5e7eb;
            border-radius: 50px;
            overflow: hidden;
            margin-top: 8px;
        }

        .ps-kpi-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--ps-green), #34d399);
            border-radius: 50px;
            transition: width 0.6s ease;
        }

        /* ── Body Grid ───────────────────────────────────── */
        .ps-body-grid {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 20px;
            align-items: start;
        }

        @media (max-width:1100px) {
            .ps-body-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── Panel Card ──────────────────────────────────── */
        .ps-panel {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: var(--ps-radius-lg);
            overflow: hidden;
            box-shadow: var(--ps-shadow);
        }

        .ps-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 24px 18px;
            border-bottom: 1px solid var(--ps-border-soft);
            gap: 12px;
        }

        .ps-panel-title {
            font-family: var(--ps-font);
            font-size: 16px;
            font-weight: 700;
            color: var(--ps-text);
            margin-bottom: 3px;
        }

        .ps-panel-sub {
            font-size: 12.5px;
            color: var(--ps-text-muted);
        }

        .ps-active-badge {
            background: #ecfdf5;
            color: #065f46;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 13px;
            border-radius: 30px;
            white-space: nowrap;
        }

        .ps-machine-count-badge {
            background: var(--ps-primary-lt);
            color: var(--ps-primary);
            font-family: var(--ps-font);
            font-size: 20px;
            font-weight: 800;
            padding: 8px 16px;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1;
            gap: 2px;
        }

        .ps-machine-count-badge span {
            font-size: 10px;
            font-weight: 500;
            opacity: 0.7;
        }

        .ps-panel-body {
            padding: 20px 24px 24px;
        }

        .ps-panel-body--accordion {
            padding: 16px;
        }

        /* ── Overview Boxes ──────────────────────────────── */
        .ps-overview-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .ps-overview-box {
            background: var(--ps-bg);
            border-radius: var(--ps-radius-sm);
            padding: 16px;
            border: 1px solid var(--ps-border-soft);
        }

        .ps-overview-box--success {
            background: var(--ps-green-lt);
            border-color: #a7f3d0;
        }

        .ps-overview-box--warn {
            background: var(--ps-amber-lt);
            border-color: #fde68a;
        }

        .ps-ov-label {
            font-size: 11.5px;
            color: var(--ps-text-muted);
            font-weight: 500;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .ps-ov-value {
            font-family: var(--ps-font);
            font-size: 15px;
            font-weight: 700;
            color: var(--ps-text);
        }

        .ps-ov-value--big {
            font-size: 28px;
            font-weight: 800;
        }

        /* ── Project Note ────────────────────────────────── */
        .ps-note {
            background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
            border: 1px solid #dde4ff;
            border-radius: var(--ps-radius);
            padding: 18px;
        }

        .ps-note-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ps-note-icon {
            width: 38px;
            height: 38px;
            background: var(--ps-primary-lt);
            color: var(--ps-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .ps-note-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--ps-text);
        }

        .ps-note-sub {
            font-size: 11px;
            color: var(--ps-text-muted);
        }

        .ps-note-body {
            font-size: 13.5px;
            color: #374151;
            line-height: 1.75;
        }

        /* ── Machine Accordion ───────────────────────────── */
        .ps-machine {
            border: 1.5px solid var(--ps-border);
            border-radius: var(--ps-radius);
            overflow: hidden;
            margin-bottom: 10px;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .ps-machine--alert {
            border-color: #fca5a5;
        }

        .ps-machine:last-child {
            margin-bottom: 0;
        }

        .ps-machine.is-open {
            border-color: var(--ps-primary);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.1);
        }

        .ps-machine.is-open.ps-machine--alert {
            border-color: var(--ps-red);
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.12);
        }

        .ps-machine-trigger {
            width: 100%;
            background: none;
            border: none;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s;
        }

        .ps-machine-trigger:hover {
            background: #fafbff;
        }

        .ps-machine.is-open .ps-machine-trigger {
            background: #fafbff;
        }

        .ps-mtrig-icon {
            width: 40px;
            height: 40px;
            background: var(--ps-primary-lt);
            color: var(--ps-primary);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .ps-machine--alert .ps-mtrig-icon {
            background: var(--ps-red-lt);
            color: var(--ps-red);
        }

        .ps-mtrig-info {
            flex: 1;
            min-width: 0;
        }

        .ps-mtrig-name {
            font-family: var(--ps-font);
            font-size: 14px;
            font-weight: 700;
            color: var(--ps-text);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ps-mtrig-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .ps-mtrig-code {
            font-size: 11.5px;
            color: var(--ps-text-muted);
            font-family: monospace;
            background: var(--ps-bg);
            padding: 1px 7px;
            border-radius: 5px;
        }

        .ps-mtrig-sep {
            color: var(--ps-border);
            font-size: 13px;
        }

        .ps-mstatus {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .ps-mstatus--fabrication {
            background: #ede9fe;
            color: #5b21b6;
        }

        .ps-mstatus--assembly {
            background: #fff7ed;
            color: #c2410c;
        }

        .ps-mstatus--machining {
            background: #fffbeb;
            color: #b45309;
        }

        .ps-mstatus--completed {
            background: var(--ps-green-lt);
            color: #065f46;
        }

        .ps-minstalled {
            font-size: 11px;
            font-weight: 600;
        }

        .ps-minstalled--yes {
            color: var(--ps-green);
        }

        .ps-minstalled--no {
            color: var(--ps-red);
        }

        .ps-mtrig-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .ps-exceed-pill {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--ps-red-lt);
            color: var(--ps-red);
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            white-space: nowrap;
            animation: pulseWarn 2s ease-in-out infinite;
        }

        @keyframes pulseWarn {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.2);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
            }
        }

        .ps-mprogress {
            text-align: right;
            min-width: 60px;
        }

        .ps-mprogress-val {
            font-family: var(--ps-font);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .ps-mprogress-track {
            width: 60px;
            height: 5px;
            background: #e5e7eb;
            border-radius: 50px;
            overflow: hidden;
        }

        .ps-mprogress-fill {
            height: 100%;
            border-radius: 50px;
            transition: width 0.5s ease;
        }

        .ps-mtrig-items-count {
            font-size: 11.5px;
            color: var(--ps-text-muted);
            font-weight: 500;
            white-space: nowrap;
        }

        .ps-mtrig-chevron {
            color: var(--ps-text-light);
            font-size: 16px;
            transition: transform 0.3s;
        }

        .ps-machine.is-open .ps-mtrig-chevron {
            transform: rotate(180deg);
        }

        /* ── Machine Body (collapsible) ──────────────────── */
        .ps-machine-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ps-machine-body.is-open {
            max-height: 2000px;
        }

        .ps-machine-body-inner {
            padding: 4px 14px 16px;
            border-top: 1px dashed var(--ps-border);
        }

        /* ── Items Grid ──────────────────────────────────── */
        .ps-items-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding-top: 12px;
        }

        @media (max-width:700px) {
            .ps-items-grid {
                grid-template-columns: 1fr;
            }
        }

        .ps-item {
            background: var(--ps-bg);
            border: 1.5px solid var(--ps-border-soft);
            border-radius: var(--ps-radius-sm);
            padding: 12px 14px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            transition: border-color 0.2s, background 0.2s;
        }

        .ps-item:hover {
            background: #f0f1f6;
        }

        .ps-item--exceeded {
            background: #fff8f8 !important;
            border-color: #fca5a5 !important;
            opacity: 0.88;
        }

        .ps-item--exceeded:hover {
            background: #fff1f1 !important;
        }

        .ps-item-main {
            flex: 1;
            min-width: 0;
        }

        .ps-item-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ps-text);
            margin-bottom: 7px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ps-item--exceeded .ps-item-name {
            color: var(--ps-text-muted);
        }

        .ps-item-fill-track {
            height: 4px;
            background: #e5e7eb;
            border-radius: 50px;
            overflow: hidden;
            margin-bottom: 7px;
        }

        .ps-item-fill-bar {
            height: 100%;
            background: var(--ps-green);
            border-radius: 50px;
            transition: width 0.5s;
        }

        .ps-item-fill-bar--over {
            background: var(--ps-red);
        }

        .ps-item-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .ps-chip {
            font-size: 10.5px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .ps-chip--req {
            background: #f1f5f9;
            color: #475569;
        }

        .ps-chip--issued {
            background: var(--ps-green-lt);
            color: #065f46;
        }

        .ps-chip--left {
            background: var(--ps-blue-lt);
            color: #1d4ed8;
        }

        .ps-chip--over {
            background: var(--ps-red-lt);
            color: #b91c1c;
        }

        .ps-iicon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .ps-iicon--warn {
            color: var(--ps-red);
            background: var(--ps-red-lt);
        }

        .ps-iicon--ok {
            color: #d1d5db;
            background: transparent;
        }

        /* ── Empty States ─────────────────────────────────── */
        .ps-items-empty {
            grid-column: 1/-1;
            text-align: center;
            color: var(--ps-text-muted);
            padding: 20px;
            font-size: 13px;
        }

        .ps-machines-empty {
            text-align: center;
            padding: 48px 24px;
            color: var(--ps-text-muted);
        }

        .ps-machines-empty i {
            font-size: 36px;
            display: block;
            margin-bottom: 10px;
            opacity: 0.4;
        }

        .ps-machines-empty p {
            font-size: 14px;
        }

        /* =========================================
SEGMENT PROGRESS
========================================= */

        .ps-stage-segments {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .ps-stage-segment {
            flex: 1;
            height: 8px;
            border-radius: 50px;
            background: #e5e7eb;
            position: relative;
            overflow: hidden;
            transition: all .3s ease;
        }

        /* completed segment */
        .ps-stage-segment.is-complete {
            box-shadow: 0 0 10px rgba(0, 0, 0, .06);
        }

        /* optional hover */
        .ps-stage-segment:hover {
            transform: scaleY(1.2);
        }
    </style>


    {{-- ═══════════════════════════════════════════
     SCRIPT — accordion toggle
═══════════════════════════════════════════ --}}
    <script>
        function toggleMachine(index) {
            const machine = document.getElementById('pm-' + index);
            const body = document.getElementById('pmc-' + index);
            const trigger = machine.querySelector('.ps-machine-trigger');
            const isOpen = machine.classList.contains('is-open');

            // close all
            document.querySelectorAll('.ps-machine.is-open').forEach(m => {
                m.classList.remove('is-open');
                m.querySelector('.ps-machine-body').classList.remove('is-open');
                m.querySelector('.ps-machine-trigger').setAttribute('aria-expanded', 'false');
            });

            // open clicked if it was closed
            if (!isOpen) {
                machine.classList.add('is-open');
                body.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                // smooth scroll into view on mobile
                setTimeout(() => {
                    machine.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 50);
            }
        }

        // Auto-open first machine that has exceeded items (if any)
        document.addEventListener('DOMContentLoaded', () => {
            const firstAlert = document.querySelector('.ps-machine.ps-machine--alert');
            if (firstAlert) {
                const id = firstAlert.id.replace('pm-', '');
                toggleMachine(parseInt(id));
            }
        });
    </script>

    @push('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // destroy old chart if exists
            if (window.workflowChartObj) {
                window.workflowChartObj.destroy();
            }

            const chartElement = document.querySelector("#workflowChart");

            if (!chartElement) {
                console.log("Chart div not found");
                return;
            }

            const stageSeries = @json(
                collect($chartStages)->pluck('percentage')->values()
            );

            const stageLabels = @json(
                collect($chartStages)->pluck('stage_name')->values()
            );

            const stageColors = @json(
                collect($chartStages)->pluck('color')->values()
            );

            console.log(stageSeries);

            const options = {

                series: stageSeries,

                chart: {
                    type: 'donut',
                    height: 380,

                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 1000
                    }
                },

                labels: stageLabels,

                colors: stageColors,

                dataLabels: {
                    enabled: false
                },

                stroke: {
                    width: 5,
                    colors: ['#fff']
                },

                legend: {
                    position: 'bottom'
                },

                plotOptions: {

                    pie: {

                        donut: {

                            size: '72%',

                            labels: {

                                show: true,

                                total: {

                                    show: true,

                                    label: 'Overall',

                                    formatter: function() {
                                        return "{{ $project->progress }}%";
                                    }
                                }
                            }
                        }
                    }
                }
            };

            window.workflowChartObj =
                new ApexCharts(chartElement, options);

            window.workflowChartObj.render();

        });
    </script>

    @endpush

@endsection