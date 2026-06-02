<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class WarrCity extends Model
{
    use BelongsToTenant;

    protected $table = 'warr_cities';

    protected $fillable = ['country_id', 'name', 'is_active'];

    public function country()
    {
        return $this->belongsTo(WarrCountry::class, 'country_id');
    }
}
