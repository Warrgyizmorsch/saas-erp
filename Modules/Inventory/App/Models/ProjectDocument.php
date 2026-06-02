<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

use App\Traits\BelongsToTenant;
class ProjectDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'project_documents';

    protected $fillable = [
        'project_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
