@extends('shared::layouts.app')

@section('content')

    <main>
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center;" class="page-header">
                <div class="page-header-left d-flex align-items-center">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Lead History</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                        <li class="breadcrumb-item">Lead History (ID: {{ $lead->id }})</li>
                    </ul>
                </div>
                <div class="">
                    <a href="{{ route('lead.index') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left" style="margin-right: 3px;"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </main>

    <div class="crm-page-container">
        <div class="card">
            <div class="card-body">
                @if($lead->histories->count())
                    <div class="timeline">
                        @foreach($lead->histories->sortByDesc('created_at') as $history)
                            <div class="timeline-item mb-4 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1 text-capitalize">
                                        <i class="fa fa-history text-primary me-1"></i>
                                        {{ str_replace('_', ' ', $history->action) }}
                                    </h6>
                                    <small class="text-muted">
                                        {{ $history->created_at->format('d M Y, H:i') }}
                                    </small>
                                </div>
                                <p class="mb-2 text-muted">
                                    <strong>By:</strong> {{ $history->user->name ?? 'System' }}
                                </p>

                                {{-- Display changes --}}
                                @if(!empty($history->changes))
                                    @if(in_array($history->action, ['lead_updated', 'user_updated']))
                                        <ul class="list-group small">
                                            @foreach($history->changes as $field => $change)
                                                <li class="list-group-item">
                                                    <span class="fw-bold text-capitalize">
                                                        {{ str_replace('_', ' ', $field) }}:
                                                    </span>
                                                    <span class="text-danger text-decoration-line-through">
                                                        {{ $change['old'] ?? 'NULL' }}
                                                    </span>
                                                    <i class="fa fa-arrow-right mx-1 text-secondary"></i>
                                                    <span class="text-success">
                                                        {{ $change['new'] ?? 'NULL' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif($history->action == 'bucket_changed')
                                        <p class="mb-0">
                                            <strong>Bucket changed:</strong>
                                            <span class="text-danger">{{ $history->changes['old'] ?? 'NULL' }}</span>
                                            → <span class="text-success">{{ $history->changes['new'] ?? 'NULL' }}</span>
                                        </p>
                                    @elseif($history->action == 'status_changed')
                                        <p class="mb-0">
                                            <strong>Status changed:</strong>
                                            <span class="text-danger">{{ $history->changes['old'] ?? 'NULL' }}</span>
                                            → <span class="text-success">{{ $history->changes['new'] ?? 'NULL' }}</span>
                                        </p>
                                    @endif
                                @else
                                    @if($history->action == 'created')
                                        <p class="text-success mb-0">Lead created successfully.</p>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No history available for this lead.</p>
                @endif
            </div>
        </div>

    </div>

@endsection