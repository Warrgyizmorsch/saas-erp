<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class JobVacancy extends Model
{
    use BelongsToTenant;

    protected $table = 'job_applications';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'department_id',
        'designation',
        'qualification',
        'experience',
        'interview_date',
        'interview_time',
        'interviewer_id',
        'interview_details',
        'status',
        'resume',
        'remarks'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(Employee::class,'interviewer_id');
    }
}
