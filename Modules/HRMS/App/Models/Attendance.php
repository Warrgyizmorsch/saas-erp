<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Attendance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'total_hours',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in' => 'datetime:H:i:s',
        'check_out' => 'datetime:H:i:s',
        'total_hours' => 'float',
    ];

    // Optional: default status
    protected $attributes = [
        'status' => 'present',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
