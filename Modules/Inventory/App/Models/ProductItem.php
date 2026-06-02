<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
class ProductItem extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'product_items';

    protected $fillable = [
        'product_id',
        'inventory_id',
        'quantity',
        'is_deleted',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function project()
{
    return $this->belongsTo(Project::class);
}
}
