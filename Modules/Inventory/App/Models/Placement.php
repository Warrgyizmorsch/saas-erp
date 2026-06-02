<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Placement extends Model
{
    use BelongsToTenant;

    use HasFactory;

      protected $fillable = [
        'name',
    ];

        public $timestamps = true;

}
