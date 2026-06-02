<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class Blog extends Model
{
    use BelongsToTenant;

    use HasFactory;
    protected $table = 'blogs';
    
    public function author()
    {
        return $this->belongsTo(\App\Models\Author::class, 'author_image', 'id');
    }
}
