<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'status',
        'department',
        'description',
        'technology',
        'type',
        'manage',
        'leaders',
        'members',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'leaders' => 'array',
        'members' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function tasks()
    {
        return $this->hasMany(DailyTask::class);
    }

    public function getProgressAttribute()
    {
        // If explicitly set to completed status, it's 100%
        if (in_array(strtolower($this->status), ['completed', 'finished'])) {
            return 100;
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $now = now();

        if ($startDate) {
            if ($now < $startDate) {
                return 0;
            }

            if ($endDate) {
                $totalDuration = $startDate->diffInSeconds($endDate);
                if ($totalDuration <= 0) return 100; // Edge case
                
                $elapsed = $startDate->diffInSeconds($now);
                $progress = ($elapsed / $totalDuration) * 100;
                return min(100, round($progress));
            } else {
                $daysPassed = $startDate->diffInDays($now);
                return min(100, round($daysPassed));
            }
        }

        return 0;
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'Pending' => 'bg-soft-pending text-pending',
            'In Process' => 'bg-soft-process text-process',
            'Completed' => 'bg-soft-completed text-completed',
            'On Hold' => 'bg-soft-hold text-hold',
            'Review' => 'bg-soft-review text-review',
            'Rework' => 'bg-soft-rework text-rework',
            default => 'bg-soft-secondary text-secondary',
        };
    }
}

