<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class SubjectPage extends Model
{
    use BelongsToTenant;

    protected $table = 'subject_pages';

    protected $fillable = [
        'title',
        'content',
        'images',
        'slug',
        'status',
        'meta_title',
        'meta_description',
        'schema',
        'faq',
    ];

    protected $casts = [
        'faq' => 'array',
        'schema' => 'array',
    ];
}

