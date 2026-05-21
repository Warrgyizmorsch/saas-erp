@extends('shared::layouts.app')

@section('content')
    <main>
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Activity Timeline of {{ $user->name }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('users.session') }}">Active Users</a></li>
                    <li class="breadcrumb-item">Lead History</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="action-links d-flex gap-2">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        Filter
                    </a>
                </div>
            </div>
        </div>

        <!-- 🔍 Filter Section -->
        <div id="collapseOne" class="accordion-collapse collapse page-header-collapse {{ request('date') ? 'show' : '' }}">
            <div class="accordion-body pb-2">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" value="{{ request('date') }}"
                                max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>

                            <a href="{{ route('users.leadHistory', $user->id) }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <div class="crm-page-container">
        <div class="row">
            <div class="col-sm-12">

                @forelse($sessions as $session)
                    <div class="card shadow-sm mb-4 border border-1">
                        <!-- Login Header -->
                        <div class="card-header bg-primary text-white" style="padding: 10px;">
                            <strong>Logged In -> {{ $session->created_at->format('d M Y h:i A') }}</strong>
                        </div>

                        <!-- Session Body -->
                        <div class="card-body">
                            @php
                                $tasks = $leadHistories->filter(function ($lead) use ($session) {
                                    return $lead->created_at->between(
                                        $session->created_at,
                                        $session->logout_at ?? now()
                                    );
                                });
                            @endphp

                            @if($tasks->isNotEmpty())
                                <ul class="list-group">
                                    @foreach($tasks as $task)
                                        <li class="list-group-item" style="padding: 7px;">
                                            <strong>-></strong>
                                            <strong>{{ $task->action }}</strong> on
                                            <strong>Lead #{{ $task->lead_id }}</strong> at
                                            <strong>{{ $task->created_at->format('h:i A') }}</strong>

                                            @if(is_array($task->changes) && count($task->changes))
                                                <ul class="mt-1 small text-secondary">
                                                    @if (isset($task->changes['old'], $task->changes['new']))
                                                        {{-- Case: changes is directly {"old": "...", "new": "..."} --}}
                                                        <li>
                                                            <span class="text-danger">"{{ $task->changes['old'] }}"</span>
                                                            →
                                                            <span class="text-success">"{{ $task->changes['new'] }}"</span>
                                                        </li>
                                                    @else
                                                        {{-- Case: changes has multiple keys --}}
                                                        @foreach ($task->changes as $key => $value)
                                                            @if (is_array($value) && isset($value['old'], $value['new']))
                                                                <li>
                                                                    <strong>{{ ucfirst($key) }}</strong>:
                                                                    <span class="text-danger">"{{ $value['old'] }}"</span>
                                                                    →
                                                                    <span class="text-success">"{{ $value['new'] }}"</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0 text-center">No lead changes in this session.</p>
                            @endif
                        </div>

                        <!-- Logout Footer -->
                        <div class="card-footer bg-secondary text-white" style="padding: 10px;">
                            <strong>Logged Out ->
                                {{ $session->logout_at ? $session->logout_at->format('d M Y h:i A') : '-' }}</strong>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No activity found for this date.</p>
                @endforelse

            </div>
        </div>
    </div>
@endsection