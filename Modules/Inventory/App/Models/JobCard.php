<?php

namespace Modules\Inventory\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCard extends Model
{
    use HasFactory;

    protected $table = 'job_cards';
    public $timestamps = false;

    protected $fillable = [
        'transaction_date',
        'job_card_no',
        'priority',
        'status',
        'vendor_id',
        'employee_id',
        'total_qty',
        'total_received_qty',
        'pending_qty',
        'completion_date',
        'completed_at',
        'created_by',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function rows()
    {
        return $this->hasMany(JobCardRow::class, 'job_card_id', 'id');
    }

    public function getIsLateAttribute()
{
    if ($this->status == 'COMPLETED' && $this->completed_at && $this->completion_date) {
         $planned   = Carbon::parse($this->completion_date)->startOfDay();
        $completed = Carbon::parse($this->completed_at)->startOfDay();

        if ($completed->gt($planned)) {
            return [
                'late'       => true,
                'delay_days' => $planned->diffInDays($completed),
            ];
        }
    }

    return false;
}

}
