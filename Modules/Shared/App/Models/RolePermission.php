<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = ['role_id', 'route_id', 'menu_id', 'tenant_id', 'is_allowed'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant_fallback', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $tenantId = tenant('id');
            if ($tenantId) {
                $builder->where(function ($query) use ($tenantId) {
                    $query->where('role_permissions.tenant_id', $tenantId)
                          ->orWhere(function ($sub) use ($tenantId) {
                              $sub->whereNull('role_permissions.tenant_id')
                                  ->whereNotExists(function ($existsQuery) use ($tenantId) {
                                      $existsQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                                          ->from('role_permissions as rp_sub')
                                          ->whereRaw('rp_sub.role_id = role_permissions.role_id')
                                          ->where('rp_sub.tenant_id', $tenantId)
                                          ->where(function ($q) {
                                              $q->where(function ($q1) {
                                                  $q1->whereNotNull('role_permissions.menu_id')
                                                     ->whereRaw('rp_sub.menu_id = role_permissions.menu_id');
                                              })
                                              ->orWhere(function ($q2) {
                                                  $q2->whereNull('role_permissions.menu_id')
                                                     ->whereNull('rp_sub.menu_id')
                                                     ->whereRaw('rp_sub.route_id = role_permissions.route_id');
                                              });
                                          });
                                  });
                          });
                });
            }
        });
    }

    // RolePermission belongs to Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // RolePermission belongs to Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    // RolePermission belongs to Route
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
