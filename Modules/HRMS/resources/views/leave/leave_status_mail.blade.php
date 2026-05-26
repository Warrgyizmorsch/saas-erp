@if($leave->status == 'approved')
    <p style="color: green; font-weight: bold;">
        Your leave has been approved.
    </p>
@elseif($leave->status == 'rejected')
    <p style="color: red; font-weight: bold;">
        Your leave has been rejected.
    </p>
@elseif($leave->status == 'on_hold')
    <p style="color: orange; font-weight: bold;">
        Your leave is currently on hold.
    </p>
@elseif($leave->status == 'unauthorised')
    <p style="color: red; font-weight: bold;">
        Your leave has been marked as unauthorised.
    </p>
@elseif($leave->status == 'unpaid')
    <p style="color: brown; font-weight: bold;">
        Your leave has been marked as unpaid leave.
    </p>
@endif
