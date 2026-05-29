<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['name', 'min_quantity', 'model','length' ,'unit','unit_id', 'grade','classification','placement', 'height', 'width', 'thikness', 'category_id', 'is_deleted','outer_diameter','inner_diameter','composition'];

    public $timestamps = true; // Laravel automatically manages created_at & updated_at


    // Relation with Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function productItems()
    {
        return $this->hasMany(\App\Models\ProductItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'inventory_id');
    }


    public function stockIn()
    {
        return $this->hasMany(StockTransaction::class, 'inventory_id')
            ->where('txn_type', 'In');
    }

    // total OUT
    public function stockOut()
    {
        return $this->hasMany(StockTransaction::class, 'inventory_id')
            ->where('txn_type', 'Out');
    }

    // 🔹 Available Stock (Dynamic)
    public function getAvailableStockAttribute()
    {
        $in = $this->stockTransactions()
            ->where('txn_type', 'In')
            ->sum('quantity');

        $out = $this->stockTransactions()
            ->where('txn_type', 'Out')
            ->sum('quantity');

        return $in - $out;
    }

     public function getMachineAvailableStockAttribute()
    {
        $in = $this->stockTransactions()
            ->where('txn_type', 'In')
            ->where('ref_type', 'Machining')
            ->sum('quantity');

        $out = $this->stockTransactions()
            ->where('txn_type', 'Out')
            ->where('ref_type', 'Machining')
            ->sum('quantity');

        return $out - $in;
    }


    public function requestSlipItems()
    {
        return $this->hasMany(RequisitionSlipRow::class, 'inventory_id');
    }
}
