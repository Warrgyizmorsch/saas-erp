<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\Grn;
use Modules\Inventory\App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GrnCreated extends Notification
{
    use Queueable;

    protected $grn;
    protected PurchaseOrder $po;

    public function __construct(Grn $grn , PurchaseOrder $po)
    {
        $this->grn = $grn;
         $this->po  = $po;
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
            'module'       => 'grn',
            'grn_id'        => $this->grn->id,
            'grn_number'      => $this->grn->formatted_grn_number ?? $this->grn->grn_number,
              'po_id'      => $this->po->id,
            'po_number'  => $this->po->formatted_po_number ?? $this->po->po_number,
            'status'  => $this->po->status,
            'auth'   => auth()->user()->name ?? 'Unknown' ,
             'status_color' => match ($this->po->status) {
            'Completed' => 'text-info',
            'Rejected' => ' text-danger',
            'Partially Received' => 'text-warning',
            default => 'text-dark',
        },
            'message'      => "GRN created for {$this->po->po_number}.",
        ];
    }
}
