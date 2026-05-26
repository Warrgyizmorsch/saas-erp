<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firm extends Model
{
    use HasFactory;
    protected $table = 'firms';
      protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
        'website',
        'gst_no',
        'pan',
        'logo',
        
    ];
}
