<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\BelongsToTenant;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role_id',
        'country_code',
        'contact_no',
        'image',
        'is_deleted',
        'city',
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
        return $this->belongsTo(\Modules\Shared\App\Models\Role::class);
    }

    // User has many custom permissions
    public function permissions()
    {
        return $this->hasMany(\Modules\Shared\App\Models\UserPermission::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(\Modules\Shared\App\Models\UserPermission::class, 'user_id');
    }

    public function loginHistories()
    {
        return $this->hasMany(\Modules\Shared\App\Models\LoginHistory::class);
    }

    public function leads()
    {
        return $this->hasMany(\Modules\CRM\App\Models\Leads::class, 'lead_owner', 'id');
    }

    public function workLogs()
    {
        return $this->hasMany(\Modules\Shared\App\Models\UserWorkLog::class);
    }

    // HRMS Employee relationship
    public function employee()
    {
        if (\Schema::hasColumn('users', 'employee_id')) {
            return $this->belongsTo(\Modules\HRMS\App\Models\Employee::class, 'employee_id');
        }
        return $this->belongsTo(\Modules\HRMS\App\Models\Employee::class, 'email', 'email');
    }

    // Fallback accessor for employee_id
    public function getEmployeeIdAttribute()
    {
        if (isset($this->attributes['employee_id']) && !empty($this->attributes['employee_id'])) {
            return $this->attributes['employee_id'];
        }
        try {
            if (class_exists(\Modules\HRMS\App\Models\Employee::class)) {
                $employee = \Modules\HRMS\App\Models\Employee::where('email', $this->email)->first();
                return $employee ? $employee->id : null;
            }
        } catch (\Exception $e) {
            // ignore
        }
        return null;
    }

    // Accessor for hrm_role slug
    public function getHrmRoleAttribute()
    {
        if (isset($this->attributes['role']) && !empty($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        if ($this->role_id == 1) {
            return 'super_admin';
        }

        $roleRelation = $this->role()->first();
        if ($roleRelation) {
            return str_replace(' ', '_', strtolower($roleRelation->name));
        }

        return 'employee';
    }
}
