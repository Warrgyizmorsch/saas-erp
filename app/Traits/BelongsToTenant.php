<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        // Automatically scope all select/read queries by tenant_id when in a tenant context
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenant('id')) {
                $builder->where($builder->getQuery()->from . '.tenant_id', tenant('id'));
            }
        });

        // Automatically set the tenant_id on model creation when in a tenant context
        static::creating(function (Model $model) {
            if (tenant('id') && !$model->tenant_id) {
                $model->tenant_id = tenant('id');
            }
        });
    }
}
