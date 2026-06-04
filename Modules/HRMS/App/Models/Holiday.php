<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Holiday extends Model
{
    use BelongsToTenant;

    protected $fillable = ['title', 'date'];
}
