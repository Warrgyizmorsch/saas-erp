<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Leave Application</title>
</head>

<body>

    <p>Dear Sir/Madam,</p>

    <p>
        I hope you are doing well.
    </p>

    <p>
        I would like to request leave for the following schedule:
    </p>

    <p>
        <strong>Employee Name:</strong> {{ $employee->name }} <br>

        <strong>Employee Email:</strong> {{ $employee->email }} <br>

        <strong>Leave Category:</strong> {{ $leave->leave_category }} <br>

        <strong>Leave Type:</strong> {{ $leave->leave_type }} <br>

        <strong>Leave Date:</strong>

        @php
            $start = \Carbon\Carbon::parse($leave->start_date)->format('d M Y');

            $end = $leave->end_date
                ? \Carbon\Carbon::parse($leave->end_date)->format('d M Y')
                : $start;
        @endphp

        @if($start == $end)
            {{ $start }}
        @else
            {{ $start }} to {{ $end }}
        @endif

        <br>

        <strong>Total Days:</strong> {{ $leave->total_days }}
    </p>

    @if($leave->start_time)
        <p>
            <strong>Time Duration:</strong>

            {{ $leave->start_time }}

            @if($leave->end_time)
                to {{ $leave->end_time }}
            @endif
        </p>
    @endif

    <p>
        <strong>Reason:</strong><br>
        {{ $leave->reason }}
    </p>

    @if($leave->message)
        <p>
            <strong>Additional Message:</strong><br>
            {{ $leave->message }}
        </p>
    @endif

    <p>
        Kindly approve my leave application.
    </p>

    <p>
        Thank you for your understanding.
    </p>

    <p>
        Best regards,<br>
        {{ $employee->name }}
    </p>

</body>

</html>