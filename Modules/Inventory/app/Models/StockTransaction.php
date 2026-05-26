<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    // Table name
    protected $table = 'stock_transactions';

    // If no created_at / updated_at columns
    public $timestamps = false;

    // Columns allowed for mass assignment
    protected $fillable = [
        'inventory_id',
        'txn_date',
        'txn_type',
        'quantity',
        'ref_type',
        'ref_no',
        'issued_to',
        'remarks',
        'project_id',
        'machine_id',
        'issue_by',
        'issue_slip_id',
        'requision_id',
        'supplier_id',
        'placement'
    ];

    // Relationship with Inventory table
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
