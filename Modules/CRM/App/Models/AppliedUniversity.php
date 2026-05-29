<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\App\Models\User;

class AppliedUniversity extends Model
{
    use HasFactory;

    protected $table = 'applied_universities';

    protected $fillable = [
        'user_id',

        'course_id',
        'course_title',
        'course_level',
        'duration',
        'location',

        'tuition_fees',
        'application_fees',

        'university_id',
        'university_name',
        'currency_symbol',
        'currency_code',

        'status',
    ];

    protected $casts = [
        'tuition_fees' => 'float',
        'application_fees' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
