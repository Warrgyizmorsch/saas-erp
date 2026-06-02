<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class RequisitionSlipRowPiece extends Model
{
    use BelongsToTenant;

    use HasFactory;
     protected $table = 'requisition_slip_row_pieces';
    public $timestamps = false;

     protected $fillable = [
    'requisition_slip_row_id',
    'item_id',
    'issued_height',
    'issued_width',
    'consumed_height',
    'consumed_width',
    'issued_qty',
    'consumed_qty',
    'shape',
    'is_completed',
     ];

     public function inventory()
     {
         return $this->belongsTo(Inventory::class, 'item_id', 'id');
     
         }
}
