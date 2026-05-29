<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class LeadHistory extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'action', 'changes'];

    protected $casts = [
        'changes' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}
