<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrCountry extends Model
{
    protected $table = 'warr_countries';

    protected $fillable = ['name', 'code', 'is_active'];

    public function cities()
    {
        return $this->hasMany(WarrCity::class, 'country_id');
    }
}
