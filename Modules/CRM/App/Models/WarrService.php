<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class WarrService extends Model
{
    use BelongsToTenant;

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
