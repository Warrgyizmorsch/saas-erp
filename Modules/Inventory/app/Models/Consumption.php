<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class Consumption extends Model
{
    protected $table = 'consumptions';

    protected $fillable = [
        'consumption_no',
        'request_slips_id',
        'transaction_date',
        'created_by',
        'rs_row_id',
        'inventory_id',
        'machine_id',
        'unit',
        'quantity',
        'height',
        'width',
        'project_id',
        'remark',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'created_by'); // ya user_id (jo column ho)
}
public function inventory()
{
    return $this->belongsTo(Inventory::class, 'inventory_id');
}

public function requestslipItem(){
    return  $this->belongsTo(RequisitionSlipRow::class, 'rs_row_id');
}

public function requestslip(){
    return $this->belongsTo(RequestSlip::class,'request_slips_id');
}

// Consumption.php


}
