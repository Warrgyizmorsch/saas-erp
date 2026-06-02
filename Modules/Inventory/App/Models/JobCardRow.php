<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class JobCardRow extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'job_card_rows';
     public $timestamps = false;

    protected $fillable = [
        'job_card_id',
        'supplier_id',
        'item_id',
        'issue_slip_row_id',
        'qty',
        'item_pending_qty',
        'status',
        'description',
        'received_qty',
    ];

    // Relation: Job Card
    public function jobCard()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }

    // Relation: Inventory / Item
    public function item()
    {
        return $this->belongsTo(Inventory::class, 'item_id');
    }

    public function issueSlipRow()
        {
            return $this->belongsTo(IssueSlipRow::class, 'issue_slip_row_id');
        }
}
