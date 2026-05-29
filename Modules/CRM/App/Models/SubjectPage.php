<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectPage extends Model
{
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

