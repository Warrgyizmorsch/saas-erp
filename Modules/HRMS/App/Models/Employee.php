<?php

namespace Modules\HRMS\App\Models;

use Modules\Shared\App\Models\User;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Employee extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'employee_code',
        'email',
        'mobile_number',
        'role',
        'department',
        'designation',
        'date_of_joining',
        'date_of_birth',
        'gender',
        'password',
        'aadhaar_number',
        'pan_number',
        'address',
        'time_in',
        'time_out',
        'leave',
        'photo',
        'pf',
        'pf_number',
        'esi',
        'esi_number',
        'insurance',
        'insurance_provider',
        'insurance_policy_number',
        'bank_name',
        'account_number',
        'ifsc_code',
        'basic_salary',
        'hra',
        'conveyance_allowance',
        'medical_allowance',
        'other_allowance',
        'working_mode',
    ];

    public function leaveAllotments()
    {
        return $this->hasMany(LeaveAllotment::class);
    }

    public function tasks()
    {
        return $this->hasMany(DailyTask::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function user()
    {
        if (isset($this->attributes['user_id']) && !empty($this->attributes['user_id'])) {
            return $this->belongsTo(\Modules\Shared\App\Models\User::class, 'user_id');
        }
        return $this->belongsTo(\Modules\Shared\App\Models\User::class, 'email', 'email');
    }
}
