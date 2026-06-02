<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class GrnRow extends Model
{
    use BelongsToTenant;

    use HasFactory;
   protected $table = 'grn_items';

    protected $fillable = [
        'grn_id',          // ✅ REQUIRED
        'inventory_id',
        'received_qty',
        'accepted_qty',
        'rejected_qty',
        'remarks',         // agar column hai
        'placement'
    ];

     public function grn()
    {
        return $this->belongsTo(Grn::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
