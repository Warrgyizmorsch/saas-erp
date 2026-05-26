<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveAllotment extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'leave_count',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
