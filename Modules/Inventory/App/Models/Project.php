<?php

namespace Modules\Inventory\App\Models;

use Modules\Inventory\App\Models\ProductItem;
use Modules\Shared\App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'inventory_projects';

    protected $fillable = [
        'name',
        'status',
        'priority',
        'budget',
        'start_date',
        'end_date',
        'created_by',
        'is_deleted',
        'refurbish',
        'comment',
        'completion_date',
        'work_flow',
    ];


    public function projectProducts()
    {
        return $this->hasMany(ProjectProduct::class);
    }

    public function ProductItem()
    {
        return $this->hasMany(ProductItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // app/Models/Project.php

    public function projectItems()
    {
        return $this->hasMany(ProjectItem::class, 'project_id');
    }



    public function getIsLateAttribute()
    {
        if ($this->status == 'completed' && $this->end_date && $this->completion_date) {
            $planned   = Carbon::parse($this->end_date)->startOfDay();
            $completed = Carbon::parse($this->completion_date)->startOfDay();

            if ($completed->gt($planned)) {
                return [
                    'late'       => true,
                    'delay_days' => $planned->diffInDays($completed),
                ];
            }
        }

        return false;
    }

    public function getIsNearDeadlineAttribute()
    {
        if ($this->status != 'completed' && $this->end_date) {

            $today = Carbon::today();
            $end = Carbon::parse($this->end_date);
            $warningDate = $end->copy()->subDays(3);

            // ✅ Near deadline OR Late
            if ($today->gte($warningDate)) {
                return true;
            }
        }

        return false;
    }

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }
}
