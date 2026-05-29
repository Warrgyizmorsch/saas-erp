<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionSlipRow extends Model
{
    use HasFactory;

    protected $table = 'requisition_slip_rows';
    public $timestamps = false;

    protected $fillable = [
        'requisition_slip_id',
        'project_id',
        'machine_id',
        'item_id',
        'unit_id',
        'quantity',
        'order_qty',
        'pending_qty',
        'order_pending_qty',
        'description',
        'status',
        'exited_qty',
        'issued_qty',
        'consumed_qty',
        'issued_height',
        'issued_width',
        'consumed_height',
        'consumed_width',
        'is_completed',
        'machinig_qty'
    ];


    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'item_id');
    }

    public function machine()
    {
        return $this->belongsTo(Product::class, 'machine_id');
    }

    public function pieces()
    {
        return $this->hasMany(RequisitionSlipRowPiece::class,);
    }
}
