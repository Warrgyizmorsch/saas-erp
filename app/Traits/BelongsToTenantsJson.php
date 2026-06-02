<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenantsJson
{
    public static function bootBelongsToTenantsJson()
    {
        // Automatically scope all select/read queries by tenant_id (JSON column) when in a tenant context
        static::addGlobalScope('tenant_json', function (Builder $builder) {
            if (tenant('id')) {
                $builder->where(function (Builder $query) {
                    $query->whereNull('tenant_id')
                          ->orWhereJsonContains('tenant_id', tenant('id'));
                });
            }
        });

        // Automatically set the tenant_id on model creation when in a tenant context
        static::creating(function (Model $model) {
            if (tenant('id') && empty($model->tenant_id)) {
                $model->tenant_id = [tenant('id')];
            }
        });
    }
}
