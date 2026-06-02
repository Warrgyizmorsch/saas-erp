<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{

    protected $fillable = ['user_id', 'route_id', 'menu_id'];

    // UserPermission belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // UserPermission belongs to Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    // UserPermission belongs to Route
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
