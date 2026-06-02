<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Category extends Model
{
    use BelongsToTenant;

    protected $table = 'categories';
    protected $fillable = [
        'category_name',
        'is_active'
    ];
}
