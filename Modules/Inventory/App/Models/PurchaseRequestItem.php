<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class PurchaseRequestItem extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'purchase_request_items';

        public $timestamps = false;

    protected $fillable = [
        'purchase_request_id',
        'issue_slip_row_id',
        'item_id',
        'requested_qty',
        'approved_qty',
        'ordered_qty',
        'status',
        'required_date',
        'uom',
        'description',
    ];

    public function inventory()
{
    return $this->belongsTo(Inventory::class, 'item_id');
}

    
}
