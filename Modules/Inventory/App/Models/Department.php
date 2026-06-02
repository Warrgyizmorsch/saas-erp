<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Department extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'inventory_departments';

    protected $fillable = [
        'department_name',
        'status',
    ];
}
