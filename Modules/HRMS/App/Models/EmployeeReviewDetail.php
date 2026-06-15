<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class EmployeeReviewDetail extends Model
{
    use BelongsToTenant;
    protected $fillable=[
        'review_id',
        'criteria_name',
        'criteria_point',
        'self_review',
        'author_review',
        'admin_review'
    ];
}
