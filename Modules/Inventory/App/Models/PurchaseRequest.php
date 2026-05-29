<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $table = 'purchase_requests';

    protected $fillable = [
        'pr_no',
        'request_date',
        'requested_by',
        'department_id',
        'priority',
        'status',
        'remarks',
        'total_qty',
        'approved_by',
        'approved_at',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }
    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }
}
