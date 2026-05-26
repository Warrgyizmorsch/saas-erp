<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\RequestSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestSlipStatusUpdated extends Notification
{
    use Queueable;

    protected RequestSlip $requestSlip;
    protected string $status;
    protected ?string $remarks;

    public function __construct(RequestSlip $requestSlip, string $status, ?string $remarks = null)
    {
        $this->requestSlip = $requestSlip;
        $this->status      = $status;
        $this->remarks     = $remarks;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'module'            => 'request_slip',
            'request_slip_id'   => $this->requestSlip->id,
            'request_slip_no'   => $this->requestSlip->requisition_slip_no,

            'status'            => $this->status,
            'remarks'           => $this->remarks,

            'approved_by_id'    => auth()->id(),
            'auth'  => auth()->user()->name ?? 'System',
             'status_color' => match ($this->status) {
            'Hold' => 'text-danger',
            'Rejected' => 'text-danger',
            'Approved'=> 'text-success',
            'Pending'=>'text-warning',
            default => 'text-dark',
             },

            'message' => "Your Request Slip {$this->requestSlip->requisition_slip_no} has been .",

        ];
    }
}
