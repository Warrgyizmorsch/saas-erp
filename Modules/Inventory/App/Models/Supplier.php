<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\App\Models\Category;

use App\Traits\BelongsToTenant;
class Supplier extends Model
{
    use BelongsToTenant;

    use HasFactory;
    protected $fillable = [
        'category',
        'registration_date',
        'supplier_name',
        'supplier_code',
        'contact_person',
        'email',
        'state',
        'city',
        'mobile',
        'gst_registered',
        'gstin',
        'pan',
        'supplier_address',
        'bank_name',
        'branch_address',
        'ifsc',
        'account_number',
  
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category', 'id');
    }

    public function getSupplierCodeAttribute($value)
    {
        return 'SUP-' . $value;
    }
}
