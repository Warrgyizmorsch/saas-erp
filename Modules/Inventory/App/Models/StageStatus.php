<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class StageStatus extends Model
{
    use BelongsToTenant;

    protected $table = 'stage_status';

    protected $fillable = [
        'name',
        'type',
        'order_no'
    ];
}
