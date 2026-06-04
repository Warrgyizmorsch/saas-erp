<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class TaskFollowUp extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'daily_task_id',
        'project_id',
        'work_description',
        'reference_name',
        'time_taken',
        'photo',
    ];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
