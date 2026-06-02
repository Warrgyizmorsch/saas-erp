<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Category extends Model
{
    use BelongsToTenant;

    protected $table = 'inventory_categories';

     protected $fillable = [
        'name', 'is_delete'
    ];
}
