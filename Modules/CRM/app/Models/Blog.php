<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $table = 'blogs';
    
    public function author()
    {
        return $this->belongsTo(\App\Models\Author::class, 'author_image', 'id');
    }
}
