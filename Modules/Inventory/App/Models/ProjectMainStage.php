<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class ProjectMainStage extends Model
{
    use BelongsToTenant;

    protected $table = 'project_main_stage';

    protected $fillable = [
        'project_id',
        'main_stage_id',
        'status_id',
        'created_by',
    ];

    public function subs()
    {
        return $this->hasMany(ProjectSubStage::class, 'project_main_stage_id')->orderBy('id', 'desc');
    }

    public function status(){
        return $this->belongsTo(StageStatus::class, 'status_id');
    }
    public function mainStage(){
        return $this->belongsTo(Stage::class, 'main_stage_id');
    }

}
