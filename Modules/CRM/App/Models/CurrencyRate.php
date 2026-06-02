<?php   

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class CurrencyRate extends Model
{
    use BelongsToTenant;

    protected $table = 'currency_rates';

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'fetched_at'
    ];

    public $timestamps = true;
}
