<?php
namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RsRequestSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slip_number',
        'status',
        'description',
    ];

    // Relationship with User (user_id column)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsTo(product::class);
    }

    public function items()
{
    return $this->hasMany(RequestSlipItem::class);
}
}
