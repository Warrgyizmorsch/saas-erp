<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class LeadSource extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = ['source_name', 'description', 'is_active'];
}
