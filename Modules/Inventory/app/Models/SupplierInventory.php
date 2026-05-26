<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInventory extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'inventory_id', 'quantity'];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
