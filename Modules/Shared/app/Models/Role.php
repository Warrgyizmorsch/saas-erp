<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'is_deleted', 'guard_name'];

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
}
