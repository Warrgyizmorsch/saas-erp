<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
      protected $table = 'author';

    protected $fillable = [
        'name',
        'photo',
        'description',
    ];
}
