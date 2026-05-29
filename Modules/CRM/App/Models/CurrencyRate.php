<?php   

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $table = 'currency_rates';

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'fetched_at'
    ];

    public $timestamps = true;
}
