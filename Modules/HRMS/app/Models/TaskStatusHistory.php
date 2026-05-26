<?php

namespace Modules\HRMS\App\Models;

use Modules\Shared\App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'old_status',
        'new_status',
        'comment',
        'updated_by',
    ];

    public function task()
    {
        return $this->belongsTo(DailyTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
