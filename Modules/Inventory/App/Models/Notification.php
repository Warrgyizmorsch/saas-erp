<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Notification extends Model
{
    use BelongsToTenant;

    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'notify_id',
        'role_id',
        'read_at',
        'data',
        'is_delete',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
