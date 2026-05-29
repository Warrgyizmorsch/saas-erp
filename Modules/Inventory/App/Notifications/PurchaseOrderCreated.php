<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\PurchaseOrder;
use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\RequestSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PurchaseOrderCreated extends Notification
{
    use Queueable;

    protected $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
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
            'module'       => 'purchase_order',
            'purchase_Request_id'        => $this->purchaseOrder->id,
            'purchase_request_number'      => $this->purchaseOrder->formatted_po_number ?? $this->purchaseOrder->po_number,
            'auth'   => $this->purchaseOrder->creator->name ?? 'Unknown',
            'message'      => "New Purchase Order generated: ",
            'url'          => route('purchase-order.approval'),

        ];
    }
}
