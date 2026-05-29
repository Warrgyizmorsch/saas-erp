<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'units';  // optional, Laravel default me ye same hota hai
    protected $fillable = ['name', 'is_deleted'];
    public $timestamps = true;   // created_at & updated_at
}
