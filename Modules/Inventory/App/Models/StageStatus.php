<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class StageStatus extends Model
{
    protected $table = 'stage_status';

    protected $fillable = [
        'name',
        'type',
        'order_no'
    ];
}
