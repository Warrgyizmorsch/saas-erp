<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Designation extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = ['name', 'short_name', 'is_active'];
}
