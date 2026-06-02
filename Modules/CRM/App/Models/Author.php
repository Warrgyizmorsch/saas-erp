<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Author extends Model
{
    use BelongsToTenant;

      protected $table = 'author';

    protected $fillable = [
        'name',
        'photo',
        'description',
    ];
}
