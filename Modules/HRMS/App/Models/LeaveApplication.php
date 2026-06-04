<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LeaveApplication extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'leave_category',
        'start_date',
        'end_date',
        'reason',
        'message',
        'status',
        'total_days',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
