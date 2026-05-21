<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;
    protected $table = 'login_histories';
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'updated_at', 'logout_at',];

    // Cast logout_at to Carbon
    protected $casts = [
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
