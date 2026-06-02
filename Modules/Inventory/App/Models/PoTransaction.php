<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class PoTransaction extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'po_transactions';


    // Agar timestamps (created_at, updated_at) nahi hain
 public $timestamps = false;
 
    protected $fillable = [
        'po_id',
        'pay_amount',
    ];
}
