<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\RequestSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseRequestCreated extends Notification
{
    use Queueable;

    protected $purchaseRequest;

    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->purchaseRequest = $purchaseRequest;
    }

    /**
     * Kaunse channel use karne hain
     */
    public function via($notifiable)
    {
        // Abhi sirf database; baad me mail/SMS bhi add kar sakte ho
        return ['database'];
    }

    /**
     * Database table ke "data" column me kya store hoga
     */
    public function toDatabase($notifiable)
    {
        return [
            'module'       => 'request_slip',
            'purchase_Request_id'        => $this->purchaseRequest->id,
            'purchase_request_number'      => $this->purchaseRequest->formatted_pr_no ?? $this->purchaseRequest->pr_no,
            'auth'   => $this->purchaseRequest->creator->name ?? 'Unknown',
            'message'      => "New Purchase Request Slip generated: ",
            'url'          => route('purchase_request.approval-view'),

        ];
    }
}
