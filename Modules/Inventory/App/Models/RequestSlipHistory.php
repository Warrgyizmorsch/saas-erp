<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class RequestSlipHistory extends Model
{
    use HasFactory;

    protected $table = 'request_slip_histories';

    protected $fillable = [
        'request_slip_id',
        'action_by',
        'action',
        'status',
        'remarks',
        'hold_by'
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function requestSlip()
    {
        return $this->belongsTo(RequestSlip::class, 'request_slip_id', 'id');
    }
}
