<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

use App\Traits\BelongsToTenant;
class RequestSlip extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'requisition_slips';
    public $timestamps = false;

    protected $fillable = [
        'requisition_slip_no',
        'rs_id',
        'store_rs',
        'employee_id',
        'department_id',
        'project_id',
        'transaction_date',
        'lot_no',
        'batch_no',
        'purpose',
        'comment',
        'status',
        'created_by',
        'total_qty',
        'edited_by',
        'edited_on',
        'admin_id',
        'admin_action_date',
        'admin_action_remark',
        'po_flag',
        'issue_completed',
        'approve_comment',
        'rejected_reason',
        'is_exited',
        'hold_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = self::orderBy('rs_id', 'desc')->first();
            $model->rs_id = $last ? ($last->rs_id + 1) : 1;
        });

        static::deleting(function ($slip) {
            $slip->rows()->delete();
            $slip->histories()->delete();
        });
    }

    public function creator()
{
    return $this->belongsTo(User::class, 'created_by', 'id');
}
    public function getFormattedRsIdAttribute()
    {
        return str_pad($this->rs_id, 4, '0', STR_PAD_LEFT);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function rows()
    {
        return $this->hasMany(RequisitionSlipRow::class, 'requisition_slip_id');
    }

    public function histories()
    {
        // FK column: request_slip_id (in request_slip_histories table)
        return $this->hasMany(RequestSlipHistory::class, 'request_slip_id', 'id');
    }

    public function issue()
    {
        return $this->hasOne(Issue::class, 'requisition_slip_id', 'id');
    }

    public function latestIssue()
{
    return $this->hasOne(Issue::class, 'requisition_slip_id')->latestOfMany();
}

}
