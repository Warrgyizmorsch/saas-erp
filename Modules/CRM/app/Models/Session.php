<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';

    // This table doesn’t have created_at/updated_at timestamps
    public $timestamps = false;

    // Fillable columns (adjust as per your schema)
    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];
}
