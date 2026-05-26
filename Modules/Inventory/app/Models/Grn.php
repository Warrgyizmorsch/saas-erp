<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    use HasFactory;
    protected $table = 'grns';
      protected $fillable = [
        'grn_number',
        'purchase_order_id',
        'grn_date',
        'invoice_no',
        'remarks',
        'created_by',
    ];

      public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // ✅ THIS IS MISSING (IMPORTANT)
    public function items()
    {
        return $this->hasMany(GrnRow::class, 'grn_id');
    }
}
