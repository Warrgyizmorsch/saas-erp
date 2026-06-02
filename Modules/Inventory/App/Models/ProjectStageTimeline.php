<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class ProjectStageTimeline extends Model
{
    use BelongsToTenant;

    use HasFactory;
    
    protected $table = 'project_stage_timelines';

    protected $fillable = [
        'project_id',
        'stage_id',
        'start_date',
        'end_date',
    ];
}
