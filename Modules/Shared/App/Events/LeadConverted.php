<?php

namespace Modules\Shared\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\App\Models\Leads;

class LeadConverted
{
    use Dispatchable, SerializesModels;

    public $lead;
    public $customerData;

    /**
     * Create a new event instance.
     */
    public function __construct(Leads $lead, array $customerData = [])
    {
        $this->lead = $lead;
        $this->customerData = $customerData;
    }
}
