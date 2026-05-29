<?php

namespace Modules\Inventory\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAvailableController extends Controller
{
    public function availableStock($inventoryId)
    {
        $in = DB::table('stock_transactions')
            ->where('inventory_id', $inventoryId)
            ->where('txn_type', 'In')
            ->where('ref_type', '!=', 'Finish')
            ->sum('quantity');

        // OUT (Machining exclude)
        $out = DB::table('stock_transactions')
            ->where('inventory_id', $inventoryId)
            ->where('txn_type', 'Out')
            ->where('ref_type', '!=', 'Machining')
            ->sum('quantity');

        $finish = DB::table('stock_transactions')->where('inventory_id', $inventoryId)->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');

         $mc =DB::table('stock_transactions')->where('inventory_id', $inventoryId)->where('txn_type','Out')->where('ref_type','Machining')->sum('quantity');
         

         $finalMc =      $mc -$finish  ; 
        $finalFnsh =   $finish -  $out;
        $semifinish = $in - $out - $finalMc - $finalFnsh;




        return response()->json([
            'available_stock' => max(0, $semifinish)
        ]);
    }
}
