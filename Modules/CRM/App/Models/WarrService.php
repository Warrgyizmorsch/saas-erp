<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrService extends Model
{
    protected $table = 'warr_services';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    public function pages()
    {
        return $this->hasMany(WarrServicePage::class, 'service_id');
    }
}
