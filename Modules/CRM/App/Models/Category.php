<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'category_name',
        'is_active'
    ];
}
