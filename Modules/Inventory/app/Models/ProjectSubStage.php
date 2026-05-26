<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSubStage extends Model
{
    protected $table = 'project_sub_stages';

    protected $fillable = [
        'project_id',
        'project_main_stage_id',
        'sub_stage_id',
        'status_id',
        'created_by',
    ];

    public function mainStage()
    {
        return $this->belongsTo(ProjectMainStage::class, 'project_main_stage_id');
    }

    public function subStage()
    {
        return $this->belongsTo(Stage::class, 'sub_stage_id');
    }

    public function status()
{
    return $this->belongsTo(StageStatus::class, 'status_id');
}

}
