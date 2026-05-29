<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_task_id',
        'work_description',
        'reference_name',
        'time_taken',
        'photo',
    ];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }
}
