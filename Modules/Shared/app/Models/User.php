<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'country_code',
        'contact_no',
        'image',
        'is_deleted',
        'city'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // User belongs to a Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // User has many custom permissions
    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function leads()
    {
        return $this->hasMany(\Modules\CRM\App\Models\Leads::class, 'lead_owner', 'id');
    }

    public function workLogs()
    {
        return $this->hasMany(UserWorkLog::class);
    }
}
