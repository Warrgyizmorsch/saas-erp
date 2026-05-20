<?php

namespace Modules\CRM\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        return $this->hasMany(Leads::class, 'lead_owner', 'id');
    }

    public function workLogs()
{
    return $this->hasMany(UserWorkLog::class);
}
}
