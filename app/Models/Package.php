<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'code',
        'max_users',
        'price',
        'description',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
        'max_users' => 'integer',
        'price' => 'decimal:2',
    ];
}
