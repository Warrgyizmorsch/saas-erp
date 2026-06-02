<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class PurchaseOrderItem extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'purchase_order_items';
    public $timestamps = false;

    protected $fillable = [
        'purchase_order_id',
        'inventory_id',
        'pr_item_id',
        'ordered_qty',
        'received_qty',
        'unit_price',
        'tax_percent',
        'line_total',
        'tax_type',
        'tax_amount',
        'taxable_total',
        'discount',
        'discount_amount',
        'item_not',
        'hsn',
    ];


    public function prItem()
    {
        return $this->belongsTo(
            PurchaseRequestItem::class,
            'pr_item_id'
        );
    }
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
