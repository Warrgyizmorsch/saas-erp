<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = ['user_id', 'route_id', 'menu_id', 'tenant_id', 'is_allowed'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant_fallback', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $tenantId = tenant('id');
            if ($tenantId) {
                $builder->where(function ($query) use ($tenantId) {
                    $query->where('user_permissions.tenant_id', $tenantId)
                          ->orWhere(function ($sub) use ($tenantId) {
                              $sub->whereNull('user_permissions.tenant_id')
                                  ->whereNotExists(function ($existsQuery) use ($tenantId) {
                                      $existsQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                                          ->from('user_permissions as up_sub')
                                          ->whereRaw('up_sub.user_id = user_permissions.user_id')
                                          ->where('up_sub.tenant_id', $tenantId)
                                          ->where(function ($q) {
                                              $q->where(function ($q1) {
                                                  $q1->whereNotNull('user_permissions.menu_id')
                                                     ->whereRaw('up_sub.menu_id = user_permissions.menu_id');
                                              })
                                              ->orWhere(function ($q2) {
                                                  $q2->whereNull('user_permissions.menu_id')
                                                     ->whereNull('up_sub.menu_id')
                                                     ->whereRaw('up_sub.route_id = user_permissions.route_id');
                                              });
                                          });
                                  });
                          });
                });
            }
        });
    }

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
