<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'name',
        'route_name',
        'method',
        'menu_id',
        'is_deleted'
    ];

    // Route has many RolePermissions
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }

    // Route has many UserPermissions
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    // Route may belong to a Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
