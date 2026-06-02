<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

use App\Traits\BelongsToTenant;
class PurchaseOrder extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'purchase_orders';
    public $timestamps = false;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'expected_delivery',
        'subtotal',
        'subtotal_discount_amount',
        'tax_amount',
        'total_amount',
        'advance_amount',
        'balance_amount',
        'status',
        'delivery_status',
        'firm',
        'final_discount',
        'remarks',
        'terms_and_conditions',
        'total_qty',
        'created_by',
        'approved_by',
        'completed_at',
        'freight_charges',
        'loading_cutting_charges',
        'remaining_amount',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function firmData(){
        return $this->belongsTo(Firm::class, 'firm');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function paymentRecord()
    {
        return $this->hasMany(PoTransaction::class, 'po_id');
    }

    public function getIsLateAttribute()
    {
        if (
            $this->status === 'Completed' && // exact match
            $this->completed_at &&
            $this->expected_delivery
        ) {
            $planned   = Carbon::parse($this->expected_delivery)->startOfDay();
        $completed = Carbon::parse($this->completed_at)->startOfDay();

        // Check if completed date is after planned date
        if ($completed->gt($planned)) {
            return [
                'late'       => true,
                'delay_days' => $planned->diffInDays($completed), // number of delayed days
            ];
        }
        }

        return false;
    }

    public function grns()
{
    return $this->hasMany(Grn::class,'purchase_order_id');
}
}
