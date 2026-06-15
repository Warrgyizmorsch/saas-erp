<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToTenant;

class JobRequirement extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'role_id',
        'priority',
        'date',
        'candidate_type',
        'minimum_experience',
        'skills'
    ];

    protected $casts = [
        'skills' => 'array',
    ];
}
