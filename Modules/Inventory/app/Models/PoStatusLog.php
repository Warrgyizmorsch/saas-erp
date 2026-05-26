<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoStatusLog extends Model
{
    use HasFactory;

    protected $table = 'po_status_logs';

    protected $fillable = [
        'purchase_order_id',
        'status',
        'changed_by',
        'remarks',
    ];

    public $timestamps = false;

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // RELATIONSHIPS

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
