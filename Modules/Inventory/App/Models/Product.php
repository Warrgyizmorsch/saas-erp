<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'is_deleted',
        'estimation_budget',
        'estimation_duration',
    ];

    public function productItems()
    {
        return $this->hasMany(ProductItem::class);
    }

    public function projectProducts()
    {
        return $this->hasMany(ProjectProduct::class);
    }
}

