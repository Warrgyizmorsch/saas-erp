<?php

namespace Modules\Shared\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HRMS\App\Models\Employee;

class EmployeeCreated
{
    use Dispatchable, SerializesModels;

    public $employee;

    /**
     * Create a new event instance.
     */
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }
}
