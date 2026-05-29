<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $table = 'vendors';

    protected $fillable = [
        'name',
        'mobile_no',
        'email',
        'address',
        'city'
    ];

     public function jobCards()
    {
        return $this->hasMany(JobCard::class, 'vendor_id', 'id');
    }

}
