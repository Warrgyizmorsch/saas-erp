<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class ProjectItem extends Model
{
    use BelongsToTenant;

    use HasFactory;
    protected $table = 'project_item';
    protected $fillable = [
        'project_id',
        'inventory_id',
        'quantity',
        'length',
    ];

    public function inventory()
{
    return $this->belongsTo(Inventory::class, 'inventory_id');
}
}
