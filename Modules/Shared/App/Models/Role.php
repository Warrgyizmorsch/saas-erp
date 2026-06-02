<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenantsJson;

class Role extends Model
{
    use BelongsToTenantsJson;

    protected $fillable = ['name', 'is_deleted', 'guard_name', 'authority_level', 'tenant_id'];

    protected $casts = [
        'tenant_id' => 'array',
    ];

    // Role has many Users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Role has many RolePermissions
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    // Dynamic slug accessor
    public function getSlugAttribute()
    {
        return \Illuminate\Support\Str::slug($this->name, '_');
    }

    public function canManageRole($targetRole): bool
    {
        return canManageRole($this, $targetRole);
    }
}
