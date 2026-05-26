<?php

namespace Modules\Inventory\App\Notifications;

use Modules\Inventory\App\Models\RequestSlip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequestSlipCreated extends Notification
{
    use Queueable;

    protected $requestSlip;

    public function __construct(RequestSlip $requestSlip)
    {
        $this->requestSlip = $requestSlip;
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
            'rs_id'        => $this->requestSlip->id,
            'rs_code'      => $this->requestSlip->formatted_rs_id ?? $this->requestSlip->rs_id,
            'rs_name'      => $this->requestSlip->name,
            'auth'   => $this->requestSlip->creator->name ?? 'Unknown',
            'project_name' => $this->requestSlip->project->name ?? 'Manual / Other',
            'message'      => "New Request Slip generated: ",
            'url'          => route('requisition.index'),
        ];
    }
}
