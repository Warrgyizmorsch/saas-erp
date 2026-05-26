@extends('shared::layouts.app')
@section('content')
  <div class="page-header d-flex justify-content-between align-items-center mb-4">

    <!-- LEFT -->
    <div class="page-header-left">

        <h4 style="
            font-size:20px;
            font-weight:500;
            margin:0 0 4px;
            color:#111827;
        ">
            Stage list
        </h4>

        <p style="
            font-size:13px;
            color:#6b7280;
            margin:0;
        ">
            All main stages & sub stages
        </p>

    </div>

    <!-- RIGHT -->
    <div class="d-flex gap-2">

        <a href="{{ route('stages.create') }}"
            class="btn btn-light border d-flex align-items-center gap-2">

            <svg width="14"
                height="14"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24">

                <path d="M12 5v14M5 12h14"/>

            </svg>

            Manage stages

        </a>

    </div>

</div>
<div class="nxl-content p-4">

{{-- Page Header --}}


{{-- Stage Grid --}}
<form method="GET" style="margin-bottom:16px;">

    <select name="workflow"
            onchange="this.form.submit()"
            style="
                padding:8px 14px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:13px;
                min-width:220px;
                background:#fff;
            ">

        <option value="">All Workflows</option>

        @foreach($workflows as $workflow)

            <option value="{{ $workflow }}"
                {{ request('workflow') == $workflow ? 'selected' : '' }}>

                {{ $workflow }}

            </option>

        @endforeach

    </select>

</form>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:12px">

@php
  $colors = [
    ['bg'=>'#E6F1FB','text'=>'#185FA5','dot'=>'#378ADD','cbg'=>'#B5D4F4','ctxt'=>'#0C447C'],
    ['bg'=>'#E1F5EE','text'=>'#0F6E56','dot'=>'#1D9E75','cbg'=>'#9FE1CB','ctxt'=>'#085041'],
    ['bg'=>'#EEEDFE','text'=>'#534AB7','dot'=>'#7F77DD','cbg'=>'#CECBF6','ctxt'=>'#3C3489'],
    ['bg'=>'#FAEEDA','text'=>'#854F0B','dot'=>'#BA7517','cbg'=>'#FAC775','ctxt'=>'#633806'],
    ['bg'=>'#FBEAF0','text'=>'#993556','dot'=>'#D4537E','cbg'=>'#F4C0D1','ctxt'=>'#72243E'],
  ];
@endphp

@foreach($stages as $i => $stage)
@php $c = $colors[$i % count($colors)]; @endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden"
     x-data="{ open: false }">

  {{-- Card Header (clickable) --}}
  <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;cursor:pointer"
       @click="open = !open">

    {{-- Initials Icon --}}
    <div style="width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;font-weight:500;background:{{ $c['bg'] }};color:{{ $c['text'] }}">
      {{ strtoupper(substr($stage->name, 0, 2)) }}
    </div>

    {{-- Stage Info --}}
    <div style="flex:1;min-width:0">
      <p style="font-size:14px;font-weight:500;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
        {{ $stage->name }}
      </p>
      <div style="display:flex;align-items:center;gap:8px">
        <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:100px;background:{{ $c['bg'] }};color:{{ $c['text'] }}">
          {{ $stage->present }}%
        </span>
        <span style="font-size:11px;color:#9ca3af">
          {{ $stage->children->count() }} sub-stage{{ $stage->children->count() != 1 ? 's' : '' }}
        </span>
      </div>
    </div>

    {{-- Chevron --}}
    @if($stage->children->count())
    <svg style="transition:transform .25s;flex-shrink:0;color:#9ca3af"
         :style="open ? 'transform:rotate(180deg)' : ''"
         width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M6 9l6 6 6-6"/>
    </svg>
    @endif

    {{-- Delete --}}
    <form action="{{ route('stages.delete', $stage->id) }}" method="POST"
          @click.stop style="flex-shrink:0">
      @csrf @method('DELETE')
      <button type="submit"
              style="width:28px;height:28px;border-radius:8px;border:1px solid #fca5a5;background:none;color:#dc2626;cursor:pointer;display:flex;align-items:center;justify-content:center">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
        </svg>
      </button>
    </form>
  </div>

  {{-- Children Panel --}}
  @if($stage->children->count())
  <div x-show="open" x-collapse
       style="border-top:1px solid #f3f4f6;padding:12px 16px 14px">
    <p style="font-size:11px;font-weight:500;color:#6b7280;letter-spacing:.05em;text-transform:uppercase;margin:0 0 8px">
      Sub stages
    </p>
    @foreach($stage->children as $child)
    <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;background:#f9fafb;margin-bottom:6px">
      <span style="width:6px;height:6px;border-radius:50%;background:{{ $c['dot'] }};flex-shrink:0"></span>
      <span style="flex:1;font-size:13px;color:#111">{{ $child->name }}</span>
      <span style="font-size:11px;font-weight:500;padding:2px 8px;border-radius:100px;background:{{ $c['cbg'] }};color:{{ $c['ctxt'] }}">
        {{ $child->present }}%
      </span>
    </div>
    @endforeach
  </div>
  @endif

</div>
@endforeach

</div>
</div>
@endsection