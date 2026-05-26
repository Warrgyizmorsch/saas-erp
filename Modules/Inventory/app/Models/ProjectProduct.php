<?php


namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProduct extends Model
{
    use HasFactory;

    protected $table = 'project_products';

    protected $fillable = [
        'project_id',
        'product_id',
        'quantity',
        'status',
        'is_deleted',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
