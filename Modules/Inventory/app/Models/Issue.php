<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class Issue extends Model
{
    use HasFactory;



    protected $table = 'issue_slips';
     public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'project_id',
        'requisition_slip_id',
        'requisition_date',
        'transaction_date',
        'department_id',
        'issue_slip_no',
        'employee_department',
        'total_issue_qty',
        'total_pending_qty',
        'total_req_qty',
        'comment',
        'created_by',
        'edited_by',
        'flag',
        'status'
    ];

    // If you want relationship (optional)
    public function requisitionSlip()
    {
        return $this->belongsTo(RequestSlip::class, 'requisition_slip_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'employee_department');
    }
    public function rows()
{
    return $this->hasMany(IssueSlipRow::class, 'issue_slip_id');
}

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
