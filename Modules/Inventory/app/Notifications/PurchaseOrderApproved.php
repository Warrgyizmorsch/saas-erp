<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\PurchaseOrder;
use Modules\Inventory\App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseOrderApproved extends Notification
{
    use Queueable;

    protected PurchaseOrder $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
       
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'module'            => 'purchase_order',
            'purchaseOrder_slip_id'   => $this->purchaseOrder->id,
            'purchaseOrder_slip_no'   => $this->purchaseOrder->po_number,

            'status'            => $this->purchaseOrder->status,
            'created_by'    =>          auth()->id(),
            'auth'  => auth()->user()->name ?? 'System',
             'status_color' => match ($this->purchaseOrder->status) {
            'Completed' => 'text-info',
            'Cancelled' => ' text-danger',
            'Partially Received' => 'text-warning',
            'Approved'=> 'text-success',
            'Submitted'=>'text-info',
            'Draft'=>'text-warning',
            default => 'text-dark',
             },

            'message' => "Your Purchase Request Slip {$this->purchaseOrder->po_number} has been .",

        ];
    }
}
