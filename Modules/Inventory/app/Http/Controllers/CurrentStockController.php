<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Exports\CurrentStockExport;
use Modules\Inventory\App\Models\Category;
use Modules\Inventory\App\Models\Grn;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\JobCard;
use Modules\Inventory\App\Models\StockTransaction;
use Modules\Inventory\App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CurrentStockController extends Controller
{

    // Controller: CurrentStockController.php (index method)


    // public function index(Request $request)
    // {
    //     // =========================
    //     // Master dropdowns
    //     // =========================
    //     $categories = Category::all();
    //     $units = Unit::all();

    //     $names = Inventory::select('name')
    //         ->whereNotNull('name')
    //         ->groupBy('name')
    //         ->orderBy('name')
    //         ->pluck('name');

    //     // =========================
    //     // Inventory Filters
    //     // =========================
    //     $query = Inventory::with('category');

    //     if ($request->filled('name')) {
    //         // dropdown exact value or type search — keeping LIKE for flexibility
    //         $query->where('id',  $request->name);
    //     }

    //     if ($request->filled('category_id')) {
    //         $query->where('category_id', $request->category_id);
    //     }

    //     if ($request->filled('classification')) {
    //         $query->where('classification', $request->classification);
    //     }

    //     // =========================
    //     // Paginate Inventories
    //     // =========================
    //     $currentStock = $query->orderBy('id', 'desc')
    //         ->paginate(20)
    //         ->withQueryString();

    //          $inventoryName = null;


    //         if ($request->name) {
    //             $inventory = Inventory::find($request->name);
    //             $inventoryName = $inventory?->name;
    //         }


    //     $inventoryIds = $currentStock->pluck('id')->values();

    //     // =========================
    //     // Transactions (FILTER BY txn_date BETWEEN from/to)
    //     // =========================
    //     $txnQuery = StockTransaction::select('inventory_id', 'txn_type', 'ref_type', 'quantity', 'txn_date')
    //         ->whereIn('inventory_id', $inventoryIds);


    //     $transactions = $txnQuery
    //         ->get()
    //         ->groupBy('inventory_id');

    //     // =========================
    //     // Compute stocks per item (based on filtered transactions)
    //     // =========================
    //     foreach ($currentStock as $item) {
    //         $rows = $transactions->get($item->id, collect());

    //         $in     = $rows->where('txn_type', 'In')->where('ref_type', '!=', 'Finish')->sum('quantity');
    //         $out    = $rows->where('txn_type', 'Out')->where('ref_type', '!=', 'Machining')->sum('quantity');
    //         $finish = $rows->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');
    //         $mc     = $rows->where('txn_type', 'Out')->where('ref_type', 'Machining')->sum('quantity');

    //         $cls = strtoupper(trim((string)($item->classification ?? '')));

    //         // Treat blank/null/FINISH same
    //         if ($cls === 'FINISH' || $cls === '' || $cls === 'NULL') {
    //             $item->machining_stock    = 0;
    //             $item->semi_finish_stock  = 0;
    //             $item->finish_stock       = $in - $out;
    //             $item->total              = $in - $out;
    //         } elseif ($cls === 'SEMI_FINISH') {
    //             $finalMc    = $mc - $finish;
    //             $finalFnsh  = $finish - $out;
    //             $semifinish = $in - $out - $finalMc - $finalFnsh;

    //             $item->machining_stock    = $finalMc;
    //             $item->finish_stock       = $finalFnsh;
    //             $item->semi_finish_stock  = $semifinish;
    //             $item->total              = $in - $out;
    //         } else {
    //             // default case
    //             $item->machining_stock    = 0;
    //             $item->semi_finish_stock  = 0;
    //             $item->finish_stock       = $in - $finish;
    //             $item->total              = $in - $out;
    //         }
    //     }

    //     return view('inventory::current-stock.current', compact('currentStock', 'categories', 'units', 'names','inventoryName'));
    // }

    public function index(Request $request)
    {
        // =========================
        // Master dropdowns
        // =========================
        $categories = Category::all();

        $units = Unit::all();

        $names = Inventory::select('name')
            ->whereNotNull('name')
            ->groupBy('name')
            ->orderBy('name')
            ->pluck('name');

        // =========================
        // Inventory Filters
        // =========================
        $query = Inventory::with('category');

        if ($request->filled('name')) {

            $query->where('id', $request->name);
        }

        if ($request->filled('category_id')) {

            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('classification')) {

            $query->where('classification', $request->classification);
        }

        // =========================
        // Selected inventory text
        // =========================
        $inventoryName = null;

        if ($request->name) {

            $inventory = Inventory::find($request->name);

            $inventoryName = $inventory?->name;
        }

        // =========================
        // GET ALL FILTERED DATA
        // =========================
        $inventories = $query
            ->orderBy('id', 'desc')
            ->get();

        $inventoryIds = $inventories->pluck('id')->values();

        // =========================
        // Transactions
        // =========================
        $transactions = StockTransaction::select(
            'inventory_id',
            'txn_type',
            'ref_type',
            'quantity',
            'txn_date'
        )
            ->whereIn('inventory_id', $inventoryIds)
            ->get()
            ->groupBy('inventory_id');

        // =========================
        // Compute stock
        // =========================
        foreach ($inventories as $item) {

            $rows = $transactions->get($item->id, collect());

            $in = $rows->where('txn_type', 'In')
                ->where('ref_type', '!=', 'Finish')
                ->sum('quantity');

            $out = $rows->where('txn_type', 'Out')
                ->where('ref_type', '!=', 'Machining')
                ->sum('quantity');

            $finish = $rows->where('txn_type', 'In')
                ->where('ref_type', 'Finish')
                ->sum('quantity');

            $mc = $rows->where('txn_type', 'Out')
                ->where('ref_type', 'Machining')
                ->sum('quantity');

            $cls = strtoupper(trim((string)($item->classification ?? '')));

            // =========================
            // FINISH
            // =========================
            if ($cls === 'FINISH' || $cls === '' || $cls === 'NULL') {

                $item->machining_stock = 0;

                $item->semi_finish_stock = 0;

                $item->finish_stock = $in - $out;

                $item->total = $in - $out;
            }

            // =========================
            // SEMI FINISH
            // =========================
            elseif ($cls === 'SEMI_FINISH') {

                $finalMc = $mc - $finish;

                $finalFnsh = $finish - $out;

                $semifinish = $in - $out - $finalMc - $finalFnsh;

                $item->machining_stock = $finalMc;

                $item->finish_stock = $finalFnsh;

                $item->semi_finish_stock = $semifinish;

                $item->total = $in - $out;
            }

            // =========================
            // DEFAULT
            // =========================
            else {

                $item->machining_stock = 0;

                $item->semi_finish_stock = 0;

                $item->finish_stock = $in - $finish;

                $item->total = $in - $out;
            }
        }

        // =========================
        // SORTING
        // =========================
        $sort = $request->get('sort', 'desc');

        if ($sort == 'asc') {

            $inventories = $inventories->sortBy('total');
        } else {

            // default DESC
            $inventories = $inventories->sortByDesc('total');
        }

        // =========================
        // MANUAL PAGINATION
        // =========================
        $page = request()->get('page', 1);

        $perPage = 20;

        $currentStock = new \Illuminate\Pagination\LengthAwarePaginator(
            $inventories->forPage($page, $perPage)->values(),
            $inventories->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        // =========================
        // RETURN VIEW
        // =========================
        return view('inventory::current-stock.current',
            compact(
                'currentStock',
                'categories',
                'units',
                'names',
                'inventoryName'
            )
        );
    }

    public function currentExport(Request $request)
    {
        return Excel::download(
            new CurrentStockExport($request),
            'current_stock.xlsx'
        );
    }

    public function stockLedger(Request $request)
    {
        $query = StockTransaction::with('inventory');

        // Inventory Filter
        if ($request->inventory_id) {
            $query->where('inventory_id', $request->inventory_id);
        }

        // Transaction Type Filter
        if ($request->transaction_type) {
            $query->where('txn_type', $request->transaction_type);
        }

        // From Date
        if ($request->from_date) {
            $query->whereDate('txn_date', '>=', $request->from_date);
        }

        // To Date
        if ($request->to_date) {
            $query->whereDate('txn_date', '<=', $request->to_date);
        }

        $inventoryName = '';

        if ($request->inventory_id) {

            $inventory = Inventory::find($request->inventory_id);

            $inventoryName = $inventory?->name;
        }

        $transactions = $query
            ->orderBy('inventory_id')
            ->orderBy('txn_date', 'desc')
            ->paginate(50);

        $totalIn = $transactions
            ->where('txn_type', 'In')
            ->whereNotIn('ref_type',['Finish','Machining'])
            ->sum('quantity');

        $totalOut = $transactions
            ->where('txn_type', 'Out')
             ->whereNotIn('ref_type',['Finish','Machining'])
            ->sum('quantity');

        $availableStock = $totalIn - $totalOut;

        return view('inventory::current-stock.stock-transaction-Ledger',
            compact('transactions', 'inventoryName','totalIn','totalOut','availableStock')
        );
    }

    public function redirectToSource($id)
{
    $txn = StockTransaction::findOrFail($id);

    if ($txn->ref_type == 'GRN') {

        $grn = Grn::where('grn_number', $txn->ref_no)->first();

        if ($grn) {
            return redirect()->route('grn.show', $grn->id);
        }

    }elseif($txn->ref_type == 'Issue Slip' || $txn->ref_type == 'Machining' || $txn->ref_type == 'Finish'){

        $job = JobCard::where('job_card_no', $txn->ref_no)->first();

        if ($job) {
            return redirect()->route('job_card.show', $job->id);
        }

    }

     return redirect()->back()->with('error', 'Record not found');
}
}
