<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LeaveAllotment extends Model
{
    use BelongsToTenant;

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
