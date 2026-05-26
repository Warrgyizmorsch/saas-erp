<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Inventory;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Models\SupplierInventory;
use Illuminate\Http\Request;

class SupplierInventoryController extends Controller
{
    
    /**
     * 3️⃣ Store Supplier Inventory & Update Stock
     */
    public function store(Request $request)
    {
        $request->validate([
            'sup_id'      => 'required|exists:users,id',
            'item_id'     => 'required|array',
            'item_qty'    => 'required|array',
            'item_id.*'   => 'required|exists:inventories,id',
            'item_qty.*'  => 'required|numeric|min:1',
        ]);

        foreach ($request->item_id as $index => $inventoryId) {

            $qty = $request->item_qty[$index];

            // 1️⃣ Insert record in supplier_inventories table
            SupplierInventory::create([
                'supplier_id'  => $request->sup_id,
                'inventory_id' => $inventoryId,
                'quantity'     => $qty,
            ]);

            // 2️⃣ Update quantity in inventories table
            $inv = Inventory::find($inventoryId);
            $inv->quantity += $qty;
            $inv->save();
        }

        return redirect()
            ->route('inventory.add')
            ->with('success', 'Inventory stock added successfully!');
    }
}
