@extends('layouts.app')

@section('content')

<main>
    <div>
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">User History</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item">History for {{ $user->name }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('users.session') }}" class="btn btn-primary">← Back</a>
            </div>
        </div>

    </div>
</main>


    <div class="crm-page-container">
        <div class="card">

            <div class="card-body">
                @if ($user->loginHistories->isEmpty())
                    <p class="text-muted">No login/logout history found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                    <th>Login Time</th>
                                    <th>Logout Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
    $session = $sessions[$user->id] ?? null;
    $latestLogin = $user->loginHistories->last();

    $isLoggedIn = false;

    if ($session) {
        // session->last_activity is stored as UNIX timestamp
        $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
        $expiryTime = $lastActivity->copy()->addMinutes(config('session.lifetime'));

        // if not expired yet, user is still logged in
        $isLoggedIn = now()->lt($expiryTime);
    }
                                @endphp

                                @foreach ($user->loginHistories as $index => $history)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $history->ip_address ?? '-' }}</td>
                                        <td>{{ Str::limit($history->user_agent ?? '-', 60) }}</td>
                                        <td>{{ $history->created_at ? $history->created_at->format('d M Y, h:i A') : '-' }}</td>
                                        <td>
                                            @if ($history->logout_at)
                                                {{ $history->logout_at->format('d M Y, h:i A') }}
                                            @else
                                                @if ($loop->first)
                                                    {{ $isLoggedIn ? 'Currently Logged In' : 'Session Timed Out' }}
                                                @else
                                                    Session Timed Out
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection