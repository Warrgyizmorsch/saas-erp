<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseRquestApproved extends Notification
{
    use Queueable;

    protected PurchaseRequest $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
       
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    { 
        return [
            'module'            => 'request_slip',
            'purchaseRequest_slip_id'   => $this->purchaseRequest->id,
            'purchaseRequest_slip_no'   => $this->purchaseRequest->pr_no,

            'status'            => $this->purchaseRequest->status,
            'approved_by_id'    => auth()->id(),
            'auth'  => auth()->user()->name ?? 'System',
             'status_color' => match ($this->purchaseRequest->status) {
            'HOLD' => 'text-danger',
            'REJECTED' => 'text-danger',
            'APPROVED'=> 'text-success',
            'SUBMITTED'=>'text-info',
            'DRAFT'=>'text-warning',
            default => 'text-dark',
             },

            'message' => "Your Purchase Request Slip {$this->purchaseRequest->pr_no} has been .",

        ];
    }
}
