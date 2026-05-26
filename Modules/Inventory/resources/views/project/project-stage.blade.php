@extends('shared::layouts.app')
@section('content')

<style>

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --white: #ffffff;
    --bg: #f7f8fc;
    --bg-card: #ffffff;
    --border: #e8edf5;
    --border-light: #f0f3f9;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --indigo: #4f46e5;
    --indigo-light: #6366f1;
    --indigo-soft: #eef2ff;
    --indigo-border: #c7d2fe;
    --green: #059669;
    --green-light: #10b981;
    --green-soft: #ecfdf5;
    --green-border: #a7f3d0;
    --amber: #d97706;
    --amber-soft: #fffbeb;
    --amber-border: #fde68a;
    --slate: #64748b;
    --slate-soft: #f8fafc;
    --slate-border: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.06), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-indigo: 0 4px 20px rgba(79,70,229,0.12);
    --shadow-green: 0 4px 16px rgba(16,185,129,0.1);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 20px;
}

body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text-primary); }

/* ══════════════════════════════════════
   MOBILE RESPONSIVE
══════════════════════════════════════ */
@media (max-width: 768px) {

    .pg-nav{
        padding: 0 14px;
        height: auto;
        min-height: 60px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .nav-right{
        width: 100%;
        justify-content: flex-start;
    }

    .flow-mode-switch{
        width: 100%;
        display: flex;
    }

    .flow-btn{
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 12px;
    }

    .pg-header{
        padding: 18px 14px;
    }

    .pg-title{
        font-size: 18px;
    }

    .pg-desc{
        font-size: 12px;
    }

    .pg-content{
        padding: 18px 12px 60px;
    }

    .pg-stats{
        gap: 10px;
    }

    .stat-chip{
        font-size: 11px;
        padding: 6px 10px;
    }

    /* Stage Layout */
    .stage-row{
        gap: 10px;
        align-items: flex-start;
    }

    .stage-num{
        width: 34px;
        height: 34px;
        font-size: 11px;
        margin-top: 10px;
    }

    .pipeline-connector{
        padding-left: 16px;
    }

    /* Card */
    .card-head{
        padding: 14px;
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }

    .card-title-row{
        align-items: flex-start;
        flex-direction: column;
        gap: 6px;
    }

    /* LONG NAME FIX */
    .card-title{
        white-space: normal;
        overflow: visible;
        text-overflow: unset;
        word-break: break-word;
        line-height: 1.4;
        font-size: 13px;
    }

    .card-stage-tag{
        align-self: flex-start;
    }

    .prog-wrap{
        gap: 8px;
    }

    .prog-label{
        min-width: 34px;
        font-size: 10px;
    }

    /* Controls */
    .card-controls{
        width: 100%;
        display: flex;
        align-items: stretch;
        gap: 8px;
    }

    .stage-select{
        flex: 1;
        min-width: 0;
        width: 100%;
        font-size: 11px;
    }

    .toggle-btn{
        width: 36px;
        height: 36px;
        flex-shrink: 0;
    }

    /* Sub Stage */
    .sub-header{
        padding: 10px 14px;
        flex-wrap: wrap;
    }

    .sub-list{
        padding: 10px;
    }

    .sub-item{
        flex-direction:column;
        align-items:stretch;
    }

    .sub-item-info{
        width:100%;
    }

    .sub-item-title{
        width:100%;
        margin-bottom:6px;
        word-break:break-word;
        line-height:1.4;
    }

   .sub-select{
        width:100%;
        min-width:100%;
        margin-top:8px;
    }
    
    .sub-badge{
        width: fit-content;
    }

    /* Toast */
    #toast-wrap{
        right: 10px;
        left: 10px;
        bottom: 14px;
    }

    .toast{
        width: 100%;
        justify-content: center;
    }
}

/* ══════════════════════════════════════
   PAGE SHELL
══════════════════════════════════════ */
.pg-shell {
    min-height: 100vh;
    background: var(--bg);
    padding: 0;
}

/* ══════════════════════════════════════
   TOP NAVBAR
══════════════════════════════════════ */
.pg-nav {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    padding: 0 32px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}
.nav-left { display: flex; align-items: center; gap: 10px; }
.nav-logo {
    width: 30px; height: 30px;
    background: linear-gradient(135deg, var(--indigo), #818cf8);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}
.nav-logo svg { width: 16px; height: 16px; color: #fff; }
.nav-brand {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.3px;
}
.nav-sep { width: 1px; height: 18px; background: var(--border); margin: 0 6px; }
.nav-project {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-muted);
}
.nav-right { display: flex; align-items: center; gap: 10px; }
.btn-nav-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px;
    border-radius: var(--radius-sm);
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    font-size: 13px; font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
    font-family: 'DM Sans', sans-serif;
}
.btn-nav-back:hover { background: var(--white); border-color: var(--indigo-border); color: var(--indigo); }
.btn-nav-back svg { width: 13px; height: 13px; }

/* ══════════════════════════════════════
   PAGE HEADER STRIP
══════════════════════════════════════ */
.pg-header {
    background: var(--white);
    border-bottom: 1px solid var(--border-light);
    padding: 28px 32px 24px;
}
.pg-header-inner { max-width: 900px; }
.pg-breadcrumb {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--text-muted); font-weight: 500;
    margin-bottom: 12px;
}
.pg-breadcrumb span { color: var(--text-muted); }
.pg-breadcrumb .bc-sep { color: var(--border); }
.pg-breadcrumb .bc-active { color: var(--text-secondary); }
.pg-title-row { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; }
.pg-title {
    font-size: 22px; font-weight: 700;
    color: var(--text-primary); letter-spacing: -0.4px;
    line-height: 1.2;
}
.pg-title span { color: var(--indigo-light); }
.pg-desc { font-size: 13px; color: var(--text-muted); margin-top: 5px; font-weight: 400; }

/* Stats row */
.pg-stats { display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; }
.stat-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.stat-chip .dot { width: 7px; height: 7px; border-radius: 50%; }
.stat-chip.chip-total  .dot { background: var(--slate); }
.stat-chip.chip-active .dot { background: var(--indigo-light); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
.stat-chip.chip-done   .dot { background: var(--green-light); }
.stat-chip.chip-locked .dot { background: var(--slate-border); }
.stat-chip.chip-total  { color: var(--text-secondary); }
.stat-chip.chip-active { color: var(--indigo); background: var(--indigo-soft); border-color: var(--indigo-border); }
.stat-chip.chip-done   { color: var(--green); background: var(--green-soft); border-color: var(--green-border); }
.stat-chip.chip-locked { color: var(--text-muted); }

/* ══════════════════════════════════════
   MAIN CONTENT
══════════════════════════════════════ */
.pg-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 32px 32px 80px;
}

/* ══════════════════════════════════════
   PIPELINE
══════════════════════════════════════ */
.pipeline { display: flex; flex-direction: column; }

.pipeline-connector {
    display: flex;
    padding-left: 31px;
    height: 20px;
    align-items: center;
}
.connector-line { width: 2px; height: 100%; border-radius: 2px; }
.connector-line.done-line   { background: linear-gradient(180deg, #10b981 0%, #10b98140 100%); }
.connector-line.active-line { background: linear-gradient(180deg, #6366f1 0%, #6366f140 100%); }
.connector-line.locked-line { background: var(--border); }

/* ── STAGE ROW ── */
.stage-row { display: flex; gap: 16px; align-items: flex-start; }

/* Bullet */
.stage-num {
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
    flex-shrink: 0;
    position: relative;
    transition: all 0.25s;
    margin-top: 14px;
}
.stage-num.sn-active {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(79,70,229,0.1), 0 4px 14px rgba(79,70,229,0.3);
}
.stage-num.sn-done {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(5,150,105,0.1), 0 4px 12px rgba(5,150,105,0.25);
}
.stage-num.sn-locked {
    background: var(--bg);
    color: var(--text-muted);
    border: 2px solid var(--border);
}
.stage-num.sn-active::after {
    content: '';
    position: absolute; inset: -6px;
    border-radius: 50%;
    border: 1.5px solid rgba(79,70,229,0.2);
    animation: pulse 2.4s ease-out infinite;
}
@keyframes pulse {
    0%   { transform: scale(1); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}

/* ── STAGE CARD ── */
.stage-card {
    flex: 1;
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.25s;
    box-shadow: var(--shadow-sm);
}
.stage-card.sc-active {
    border-color: var(--indigo-border);
    box-shadow: var(--shadow-indigo);
}
.stage-card.sc-done {
    border-color: var(--green-border);
    box-shadow: var(--shadow-green);
}
.stage-card.sc-locked {
    opacity: 0.55;
    box-shadow: none;
}

/* Top accent stripe */
.card-stripe {
    height: 3px;
    width: 100%;
}
.sc-active .card-stripe { background: linear-gradient(90deg, #4f46e5 0%, #a78bfa 60%, transparent 100%); }
.sc-done   .card-stripe { background: linear-gradient(90deg, #059669 0%, #6ee7b7 60%, transparent 100%); }
.sc-locked .card-stripe { background: var(--border-light); }

/* Card header */
.card-head {
    padding: 16px 20px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.card-info { flex: 1; min-width: 0; }

.card-title-row {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 10px;
}
.card-title {
    font-size: 14px; font-weight: 700;
    color: var(--text-primary);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sc-done   .card-title { color: var(--text-secondary); }
.sc-locked .card-title { color: var(--text-muted); }

.card-stage-tag {
    font-size: 10px; font-weight: 600; letter-spacing: 0.8px;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}
.sc-active .card-stage-tag { background: var(--indigo-soft); color: var(--indigo); }
.sc-done   .card-stage-tag { background: var(--green-soft); color: var(--green); }
.sc-locked .card-stage-tag { background: var(--bg); color: var(--text-muted); }

/* Progress */
.prog-wrap { display: flex; align-items: center; gap: 12px; }
.prog-bar-bg {
    flex: 1; height: 4px;
    background: var(--border-light);
    border-radius: 10px; overflow: hidden;
}
.prog-bar-fill {
    height: 100%; border-radius: 10px;
    transition: width 0.5s cubic-bezier(0.4,0,0.2,1);
}
.sc-active .prog-bar-fill { background: linear-gradient(90deg, #4f46e5, #818cf8); }
.sc-done   .prog-bar-fill { background: linear-gradient(90deg, #059669, #34d399); }
.sc-locked .prog-bar-fill { background: var(--border); }
.prog-label {
    font-family: 'Inter', monospace;
    font-size: 11px; font-weight: 600;
    min-width: 30px; text-align: right;
    letter-spacing: 0.2px;
}
.sc-active .prog-label { color: var(--indigo); }
.sc-done   .prog-label { color: var(--green); }
.sc-locked .prog-label { color: var(--text-muted); }

/* Card right controls */
.card-controls { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

/* Status badges */
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px; font-weight: 600;
    white-space: nowrap;
}
.badge-completed {
    background: var(--green-soft);
    border: 1px solid var(--green-border);
    color: var(--green);
}
.badge-locked {
    background: var(--slate-soft);
    border: 1px solid var(--slate-border);
    color: var(--text-muted);
}
.badge-completed svg, .badge-locked svg { width: 11px; height: 11px; }

/* Select */
.stage-select {
    appearance: none;
    background: var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 10px center;
    border: 1px solid var(--indigo-border);
    border-radius: var(--radius-sm);
    color: var(--indigo);
    font-family: 'DM Sans', sans-serif;
    font-size: 12px; font-weight: 600;
    padding: 6px 28px 6px 11px;
    cursor: pointer;
    min-width: 136px;
    transition: all 0.15s;
    background-color: var(--indigo-soft);
}
.stage-select:hover { border-color: var(--indigo); background-color: #e8edff; }
.stage-select:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.stage-select option { background: #fff; color: var(--text-primary); font-weight: 500; }

/* Toggle */
.toggle-btn {
    width: 30px; height: 30px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--indigo-border);
    background: var(--indigo-soft);
    color: var(--indigo);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
    flex-shrink: 0;
}
.toggle-btn:hover { background: #e0e7ff; }
.toggle-btn svg { width: 12px; height: 12px; transition: transform 0.25s; }
.toggle-btn.open svg { transform: rotate(180deg); }

/* ══════════════════════════════════════
   SUB STAGES
══════════════════════════════════════ */
.sub-collapse {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.38s cubic-bezier(0.4,0,0.2,1);
}
.sub-collapse.open { max-height: 3000px; }

.sub-header {
    padding: 10px 20px 8px;
    background: var(--bg);
    border-top: 1px solid var(--border-light);
    display: flex; align-items: center; gap: 7px;
}
.sub-header-label {
    font-size: 10px; font-weight: 700;
    letter-spacing: 1px; text-transform: uppercase;
    color: var(--text-muted);
}
.sub-header-count {
    font-size: 10px; font-weight: 600;
    background: var(--white);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 1px 7px;
    border-radius: 10px;
}

.sub-list { padding: 10px 14px 14px; display: flex; flex-direction: column; gap: 7px; background: var(--bg); }

.sub-item {
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    padding: 11px 15px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px;
    position: relative;
    overflow: hidden;
    transition: all 0.15s;
}
.sub-item:hover { box-shadow: var(--shadow-sm); }

/* Left accent */
.sub-item::before {
    content: '';
    position: absolute;
    left: 0; top: 8px; bottom: 8px;
    width: 3px;
    border-radius: 0 2px 2px 0;
}
.si-active { border-color: #ddd6fe; background: #faf9ff; }
.si-active::before { background: var(--indigo-light); }
.si-active:hover  { border-color: var(--indigo-border); }

.si-done  { border-color: var(--green-border); background: #fafffe; }
.si-done::before  { background: var(--green-light); }

.si-locked { opacity: 0.5; }
.si-locked::before { background: var(--border); }

.sub-item-info { flex: 1; min-width: 0; }

.sub-item-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600;
    margin-bottom: 8px;
}
.si-active .sub-item-title { color: #3730a3; }
.si-done   .sub-item-title { color: var(--text-secondary); }
.si-locked .sub-item-title { color: var(--text-muted); }

.sub-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.si-active .sub-dot { background: var(--indigo-light); }
.si-done   .sub-dot { background: var(--green-light); }
.si-locked .sub-dot { background: var(--border); }

/* Sub select */
.sub-select {
    appearance: none;
    background: var(--bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 9px center;
    border: 1px solid var(--indigo-border);
    border-radius: var(--radius-sm);
    color: var(--indigo);
    font-family: 'DM Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    padding: 5px 26px 5px 10px;
    cursor: pointer;
    min-width: 128px;
    transition: all 0.15s;
    background-color: var(--indigo-soft);
}
.sub-select:hover { border-color: var(--indigo); }
.sub-select:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.sub-select option { background: #fff; color: var(--text-primary); }

/* Sub badges */
.sub-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px; font-weight: 600;
    flex-shrink: 0; white-space: nowrap;
}
.sub-badge-done   { background: var(--green-soft); border: 1px solid var(--green-border); color: var(--green); }
.sub-badge-locked { background: var(--slate-soft); border: 1px solid var(--slate-border); color: var(--text-muted); }
.sub-badge svg { width: 9px; height: 9px; }

.flow-mode-switch{
    display:flex;
    align-items:center;
    gap:10px;
}

.flow-btn{
    padding:8px 16px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    border:1px solid var(--border);
    background:#fff;
    color:var(--text-secondary);
    transition:.2s;
}

.flow-btn:hover{
    border-color:var(--indigo-border);
    color:var(--indigo);
}

.flow-btn.active{
    background:var(--indigo);
    color:#fff;
    border-color:var(--indigo);
    box-shadow:0 4px 12px rgba(79,70,229,.18);
}

/* ══════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════ */
.empty-wrap {
    text-align: center; padding: 80px 20px;
    display: flex; flex-direction: column; align-items: center;
}
.empty-icon-wrap {
    width: 72px; height: 72px;
    background: var(--indigo-soft);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
}
.empty-icon-wrap svg { width: 32px; height: 32px; color: var(--indigo-light); }
.empty-title { font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
.empty-desc  { font-size: 14px; color: var(--text-muted); max-width: 300px; line-height: 1.6; }

/* ══════════════════════════════════════
   TOAST
══════════════════════════════════════ */
#toast-wrap {
    position: fixed; bottom: 24px; right: 24px;
    z-index: 9999;
    display: flex; flex-direction: column; gap: 8px; align-items: flex-end;
}
.toast {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 16px;
    border-radius: var(--radius-md);
    font-size: 13px; font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    animation: toastSlide 0.3s cubic-bezier(0.34,1.56,0.64,1) forwards;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.toast-ok  { background: var(--green-soft); border: 1px solid var(--green-border); color: var(--green); }
.toast-err { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
.toast svg { width: 14px; height: 14px; }
@keyframes toastSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.94); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

@php
    $PARENT_DONE_ID = 6;
    $SUB_DONE_ID    = 7;
$forceUnlockAll = ($project->flow_type == 1);

    $flowModeLabel = $forceUnlockAll
        ? 'Free Flow'
        : 'Sequential Flow';

    // Pre-compute counts for stats chips
    $totalStages  = $parentStages->count();
    $doneCount    = $parentStages->where('current_status_id', $PARENT_DONE_ID)->count();
    $lockedCount  = 0;
    $activeCount  = 0;
    foreach ($parentStages as $idx => $st) {
        $isDone   = $st->current_status_id == $PARENT_DONE_ID;
        $prevSt   = $idx > 0 ? $parentStages[$idx - 1] : null;
        $isLocked = !$forceUnlockAll && $idx > 0 && (!$prevSt || $prevSt->current_status_id != $PARENT_DONE_ID);
        if ($isLocked) $lockedCount++;
        elseif (!$isDone) $activeCount++;
    }
@endphp

<div class="pg-shell">

    {{-- ── NAVBAR ── --}}
    <nav class="pg-nav">
        <div class="nav-left">
            <div class="nav-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="nav-brand">ProjectFlow</span>
            <div class="nav-sep"></div>
        </div>
        <div class="nav-right">
            <div class="flow-mode-switch">

    <form method="POST" action="{{ route('project.updateFlow', $project->id) }}">
    @csrf

    <div class="flow-toggle">

        <button type="submit"
                name="flow_type"
                value="0"
                class="flow-btn {{ $project->flow_type == 0 ? 'active' : '' }}">
            Sequential Flow
        </button>

        <button type="submit"
                name="flow_type"
                value="1"
                class="flow-btn {{ $project->flow_type == 1 ? 'active' : '' }}">
            Free Flow
        </button>

    </div>
</form>

</div>
           
            
        </div>
    </nav>

    {{-- ── PAGE HEADER ── --}}
    <div class="pg-header">
        <div class="pg-header-inner">
            <div class="pg-breadcrumb">
                <span>Projects</span>
                <span class="bc-sep">›</span>
                <span>{{ $project->project_name ?? 'Project' }}</span>
                <span class="bc-sep">›</span>
                <span class="bc-active">Stages</span>
            </div>
            <div class="pg-title-row">
                <div>
                    <h1 class="pg-title">Project <span>Stages</span></h1>
                    <p class="pg-desc">Complete each stage in order to unlock the next one</p>
                </div>
            </div>
            <div class="pg-stats">
                <div class="stat-chip chip-total">
                    <span class="dot"></span>
                    {{ $totalStages }} Total Stages
                </div>
                @if($activeCount > 0)
                <div class="stat-chip chip-active">
                    <span class="dot"></span>
                    {{ $activeCount }} In Progress
                </div>
                @endif
                @if($doneCount > 0)
                <div class="stat-chip chip-done">
                    <span class="dot"></span>
                    {{ $doneCount }} Completed
                </div>
                @endif
                @if($lockedCount > 0)
                <div class="stat-chip chip-locked">
                    <span class="dot"></span>
                    {{ $lockedCount }} Locked
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT ── --}}
    <div class="pg-content">

        @if($parentStages->isEmpty())
        <div class="empty-wrap">
            <div class="empty-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="empty-title">No Stages Assigned</div>
            <div class="empty-desc">This project doesn't have any stages configured yet.</div>
        </div>
        @else

        <div class="pipeline">
        @foreach($parentStages as $idx => $stage)
            @php
                $subs = $subStages
                ->where('parent_id', $stage->id)
                ->values();
                $isDone    = $stage->current_status_id == $PARENT_DONE_ID;
                $prevStage = $idx > 0 ? $parentStages[$idx - 1] : null;
                $isLocked  = !$forceUnlockAll && $idx > 0 && (!$prevStage || $prevStage->current_status_id != $PARENT_DONE_ID);
                $numKey    = $isDone ? 'sn-done' : ($isLocked ? 'sn-locked' : 'sn-active');
                $cardKey   = $isDone ? 'sc-done' : ($isLocked ? 'sc-locked' : 'sc-active');
                $tagLabel  = $isDone ? 'Completed' : ($isLocked ? 'Locked' : 'In Progress');
                $connClass = $isDone ? 'done-line' : ($isLocked ? 'locked-line' : 'active-line');
                $pct       = $stage->present ?? 0;
                $subCount  = $subs->count();
                $subDoneCount = $subs->where('current_status_id', $SUB_DONE_ID)->count();
            @endphp

            @if(!$loop->first)
            <div class="pipeline-connector">
                <div class="connector-line {{ $connClass }}"></div>
            </div>
            @endif

            <div class="stage-row">

                {{-- Number bullet --}}
                <div class="stage-num {{ $numKey }}">
                    @if($isDone)
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @elseif($isLocked)
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    @else
                        {{ $idx + 1 }}
                    @endif
                </div>

                {{-- Card --}}
                <div class="stage-card {{ $cardKey }}">
                    <div class="card-stripe"></div>

                    <div class="card-head">
                        <div class="card-info">
                            <div class="card-title-row">
                                <div class="card-title">{{ $stage->name }}</div>
                                <span class="card-stage-tag">{{ $tagLabel }}</span>
                            </div>
                            <div class="prog-wrap">
                                <div class="prog-bar-bg">
                                    <div class="prog-bar-fill" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="prog-label">{{ $pct }}%</span>
                            </div>
                        </div>

                        <div class="card-controls">
                           @if($isLocked)

                             <span class="status-badge badge-locked">
                                 Locked
                             </span>
                     
                         @else
                     
                            

                        {{-- DROPDOWN ALWAYS SHOW --}}
                                       <select class="stage-select parent-status"
                                           data-stage-id="{{ $stage->id }}">
                               
                                           @foreach($parentStatuses as $status)
                               
                                           <option value="{{ $status->id }}"
                                               {{ $stage->current_status_id == $status->id ? 'selected' : '' }}>
                               
                                               {{ $status->name }}
                               
                                           </option>
                               
                                           @endforeach
                               
                                       </select>
                               
                                   @endif

                            @if(!$isLocked && $subCount > 0)
                            <button class="toggle-btn" id="tbtn-{{ $stage->id }}" onclick="toggleSub('{{ $stage->id }}')">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Sub Stages --}}
                    @if($subCount > 0 && !$isLocked)
                    <div class="sub-collapse {{ !$isDone ? 'open' : '' }}" id="sub-{{ $stage->id }}">
                        <div class="sub-header">
                            <span class="sub-header-label">Sub-stages</span>
                            <span class="sub-header-count">{{ $subDoneCount }}/{{ $subCount }} done</span>
                        </div>
                        <div class="sub-list">
                            @foreach($subs as $si => $sub)
                            @php
                                $subDone   = $sub->current_status_id == $SUB_DONE_ID;
                                $prevSub   = $si > 0 ? $subs->values()[$si - 1] : null;
                                $subLocked = !$forceUnlockAll && $si > 0 && (!$prevSub || $prevSub->current_status_id != $SUB_DONE_ID);
                                $siClass   = $subDone ? 'si-done' : ($subLocked ? 'si-locked' : 'si-active');
                                $sp        = $sub->present ?? 0;
                            @endphp
                            <div class="sub-item {{ $siClass }}">
                                <div class="sub-item-info">
                                    <div class="sub-item-title">
                                        <span class="sub-dot"></span>
                                        {{ $sub->name }}
                                    </div>
                                    <div class="prog-wrap">
                                        <div class="prog-bar-bg">
                                            <div class="prog-bar-fill" style="width:{{ $sp }}%"></div>
                                        </div>
                                        <span class="prog-label">{{ $sp }}%</span>
                                    </div>
                                </div>

                                @if($subLocked)
                                    <span class="sub-badge sub-badge-locked">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                                        Locked
                                    </span>
                               @else                               
                                   <select class="sub-select sub-status"
                                       data-sub-id="{{ $sub->id }}"
                                       data-parent-id="{{ $stage->id }}">
                               
                                       @foreach($subStatuses as $status)
                               
                                       <option value="{{ $status->id }}"
                                           {{ $sub->current_status_id == $status->id ? 'selected' : '' }}>
                               
                                           {{ $status->name }}
                               
                                       </option>
                               
                                       @endforeach
                               
                                   </select>
                               
                               @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>{{-- /stage-card --}}
            </div>{{-- /stage-row --}}

        @endforeach
        </div>{{-- /pipeline --}}
        @endif

    </div>{{-- /pg-content --}}
</div>{{-- /pg-shell --}}

<div id="toast-wrap"></div>

<script>
    function toggleSub(id) {
        const col = document.getElementById('sub-' + id);
        const btn = document.getElementById('tbtn-' + id);
        if (!col) return;
        col.classList.toggle('open');
        btn.classList.toggle('open');
    }

    function toast(msg, ok = true) {
        const wrap = document.getElementById('toast-wrap');
        const el   = document.createElement('div');
        el.className = 'toast ' + (ok ? 'toast-ok' : 'toast-err');
        el.innerHTML = ok
            ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>${msg}`
            : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>${msg}`;
        wrap.appendChild(el);
        setTimeout(() => { el.style.opacity='0'; el.style.transform='translateY(8px)'; el.style.transition='all .25s'; setTimeout(()=>el.remove(),250); }, 2800);
    }

    document.addEventListener('change', function(e) {

        // Parent status
        if (e.target.classList.contains('parent-status')) {
             e.preventDefault();
            fetch('/stage/update-parent-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    parent_stage_id:  e.target.dataset.stageId,
                    parent_status_id: e.target.value,
                    project_id: '{{ $project->id }}'
                })
            })
            .then(r => r.json())
            .then((res) => {toast('Stage status updated'); setTimeout(() => location.reload(), 700); })
            .catch((err) => {
        toast('Update failed', false);
    });
        }

        // Sub status
        if (e.target.classList.contains('sub-status')) {
            const pid = e.target.dataset.parentId;
            const ps  = document.querySelector(`.parent-status[data-stage-id="${pid}"]`);
            fetch('/stage/update-sub-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    sub_stage_id:     e.target.dataset.subId,
                    sub_status_id:    e.target.value,
                    parent_stage_id:  pid,
                    parent_status_id: ps ? ps.value : null,
                    project_id: '{{ $project->id }}'
                })
            })
            .then(r => r.json())
            .then(() => { toast('Sub-stage status updated'); setTimeout(() => location.reload(), 700); })
            .catch(() => toast('Update failed', false));
        }
    });
</script>

@endsection