<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Stage extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'stages';

    protected $fillable = [
        'name',
        'parent_id',
        'order_no',
        'present',
        'section',
    ];

   

    public function parent()
    {
        return $this->belongsTo(Stage::class, 'parent_id');
    }

   

    public function children()
    {
        return $this->hasMany(Stage::class, 'parent_id')
            ->orderBy('order_no');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(Stage::class, 'parent_id')
            ->with('childrenRecursive')
            ->orderBy('order_no');
    }


    public function scopeMainStages($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubStages($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
