<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

use App\Traits\BelongsToTenant;
class LeadAssignHistory extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'lead_assign_histories';

    protected $fillable = [
        'lead_id',
        'lead_owner_id',
        'assigned_date',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

   
    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'lead_owner_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
