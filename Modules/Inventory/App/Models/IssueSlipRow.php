<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class IssueSlipRow extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'issue_slip_rows';
    public $timestamps = false;


    protected $fillable = [
        'issue_slip_id',
        'requisition_slip_row_id',
        'item_id',
        'quantity',
        'description',
        'status',
        'machine_id',
        'order_qty',
        'issue_qty',
        'pending_qty',
        'supplier_id',
    ];

    // 🔗 Relation: row belongs to issue slip
    public function issueSlip()
    {
        return $this->belongsTo(Issue::class, 'issue_slip_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'item_id');
    }

    public function machine()
    {
        return $this->belongsTo(Product::class, 'machine_id');
    }
    public function jobcartrow()
    {
        return $this->hasMany(JobCardRow::class, 'issue_slip_row_id');
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
}
