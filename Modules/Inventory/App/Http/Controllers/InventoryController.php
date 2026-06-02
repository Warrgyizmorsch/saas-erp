<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Exports\RequiredVsAvailableExport;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\StockTransaction;
use Modules\Inventory\App\Models\SupplierInventory;
use Modules\Inventory\App\Models\Unit;
use Modules\Inventory\App\Models\Category;
use Modules\Inventory\App\Models\Placement;
use Modules\Inventory\App\Models\Supplier;
use Modules\Shared\App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    // 1️⃣ List inventory
    public function index(Request $request)
    {
        $query = Inventory::with('unit', 'category')->where('is_deleted', 0)->orderBy('id', 'desc');

        // Filter by Inventory Name
        if ($request->filled('name')) {
            $query->where('id',  $request->name);
        }

        if ($request->filled('classification')) {
            $query->where('classification', $request->classification);
        }

        // Filter by Unit Name (related table)
        if ($request->filled('Category')) {
            $query->where('category_id', $request->Category);
        }
        if ($request->filled('placement')) {
            $query->where('placement', $request->placement);
        }


        // Paginated results
        $items = $query->paginate(10)->withQueryString();

        $inventoryName = null;


        if ($request->name) {
            $inventory = Inventory::find($request->name);
            $inventoryName = $inventory?->name;
        }


        // Units list for dropdown
        $units = Unit::where('is_deleted', 0)->get();
        $categories = Category::all();
        $placements = Placement::select('id', 'name')->get();

        return view('inventory::inventory.index', compact('items', 'categories', 'units', 'placements', 'inventoryName'));
    }



    // 2️⃣ Show form to add new item
    public function create()
    {
        $units = Unit::where('is_deleted', 0)->get();
        $categories = Category::where('is_delete', 0)->whereNull('deleted_at')->get();
        return view('inventory::inventory.create', compact('units', 'categories'));
    }

    // 3️⃣ Store new item
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'model' => 'required|string',
    //         'opening_quantity' => 'required|numeric',
    //         'unit_id' => 'required|exists:units,id'
    //     ]);

    //     Inventory::create([
    //         'name' => $request->name,
    //         'model' => $request->model,
    //         'opening_quantity' => $request->opening_quantity,
    //         'unit_id' => $request->unit_id,
    //         'is_deleted' => 0
    //     ]);

    //     return redirect()->route('inventory.index')
    //         ->with('success', 'Item added successfully!');
    // }

    public function store(Request $request)
    {


        $request->validate(
            [
                'name' => [
                    'required',
                    Rule::unique('inventories')
                        ->where(function ($query) use ($request) {
                            return $query->where('model', $request->model);
                        }),
                ],
                'model' => 'required|string',
                'min_quantity' => 'required|numeric|min:1',
                'category_id' => 'required|exists:inventory_categories,id',
                'classification' => 'required',
                'placement' => 'required',
                'unit' => 'required',
                'height' => 'nullable|integer',
                'width' => 'nullable|integer',
                'thikness' => 'nullable|integer',
                'outer_diameter' => 'nullable',
                'inner_diameter' => 'nullable',
                'composition' => 'nullable',
                'grade' => 'nullable|string',
            ],
            [
                'classification.required' => "please select classification",
                'unit.required' => 'please select unit',
            ]
        );


        // $unit = Unit::findOrFail($request->unit_id);
        Inventory::create([
            'name' => $request->name,
            'model' => $request->model,
            'min_quantity' => $request->min_quantity,
            'category_id' => $request->category_id,
            'classification' => $request->classification,
            'placement' => $request->placement,
            'unit' => $request->unit,
            'grade' => $request->grade,
            'height' => $request->height,
            'width' => $request->width,
            'length' => $request->length ?? 0,
            'thikness' => $request->thikness,
            'composition' => $request->composition,
            'outer_diameter' => $request->outer_diameter,
            'inner_diameter' => $request->inner_diameter,
            'is_deleted' => 0,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory added successfully!');
    }

    // 4️⃣ Soft delete item
    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        $item->is_deleted = 1;
        $item->save();

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }

    // 5️⃣ Edit item
    public function edit($id)
    {
        $item = Inventory::findOrFail($id);

        $items = Inventory::with('category')
            ->where('is_deleted', 0)
            ->paginate(100);

        $units = Unit::where('is_deleted', 0)->get();
        $categories = Category::all();
        $placements = Placement::select('id', 'name')->get();


        return view('inventory::inventory.index', compact('item', 'items', 'units', 'categories', 'placements'));
    }



    // 6️⃣ Update item
    //     public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'model' => 'required|string',
    //         'min_quantity' => 'required|numeric',
    //         'unit_id' => 'required|exists:units,id'
    //     ]);

    //     $item = Inventory::findOrFail($id);

    //     $item->update([
    //         'name' => $request->name,
    //         'model' => $request->model,
    //         'min_quantity' => $request->min_quantity,
    //         'unit_id' => $request->unit_id
    //     ]);

    //     return redirect()->route('inventory.index')
    //         ->with('success', 'Item updated successfully!');
    // }
    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'name' => [
                    'required',
                    Rule::unique('inventories')
                        ->where(function ($query) use ($request) {
                            return $query->where('model', $request->model);
                        })
                        ->ignore($id),
                ],
                'model' => 'required|string',
                'min_quantity' => 'required|numeric|min:1',
                'category_id' => 'required|exists:inventory_categories,id',
                'classification' => 'required',
                'placement' => 'required',
                'unit' => 'required',
                'height' => 'nullable|integer',
                'width' => 'nullable|integer',
                'thikness' => 'nullable|integer',
                'outer_diameter' => 'nullable',
                'inner_diameter' => 'nullable',
                'composition' => 'nullable',
                'grade' => 'nullable|string',
            ],
            [
                'classification.required' => "please select classification",
                'unit.required' => 'please select unit',
            ]
        );


        $item = Inventory::findOrFail($id);

        $item->update([
            'name' => $request->name,
            'model' => $request->model,
            'min_quantity' => $request->min_quantity,
            'category_id' => $request->category_id,
            'classification' => $request->classification,
            'placement' => $request->placement,
            'unit' => $request->unit,
            'grade' => $request->grade,
            'height' => $request->height,
            'width' => $request->width,
            'length' => $request->length ?? 0,
            'thikness' => $request->thikness,
            'composition' => $request->composition,
            'outer_diameter' => $request->outer_diameter,
            'inner_diameter' => $request->inner_diameter,
            'is_deleted' => 0,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', 'Inventory updated successfully!');
    }




    // 7️⃣ Toggle inventory status (recover or deactivate)
    public function toggle($id)
    {
        $item = Inventory::findOrFail($id);

        // Toggle is_deleted: if 0 => 1 (soft delete), if 1 => 0 (recover)
        $item->is_deleted = $item->is_deleted == 0 ? 1 : 0;
        $item->save();

        $message = $item->is_deleted == 0 ? 'Item recovered successfully!' : 'Item deleted successfully!';
        return redirect()->back()->with('success', $message);
    }

    public function addInventory()
    {
        $suppliers = User::where('is_delete', 0)
            ->whereHas('role', function ($q) {
                $q->where('authority_level', 60);
            })
            ->get();
        $items = Inventory::with('unit')->where('is_deleted', 0)->get(); // fetch all items, regardless of is_deleted

        $supplierInventory = SupplierInventory::with('supplier', 'inventory')
            ->latest()
            ->get();

        return view('inventory::inventory.add', compact('suppliers', 'items', 'supplierInventory'));
    }



    public function openingStockForm(Request $request)
    {
        $query = Inventory::with('stockTransactions')
            ->where('is_deleted', 0);

        $inventories =  Inventory::pluck('name');
        $suppliers = Supplier::pluck('supplier_name', 'id');
        $placements = Placement::pluck('name', 'id');

        $categories = Category::select('id', 'name')->get();

        // Apply name filter if provided
        if ($request->filled('name')) {
            $query->where('id',  $request->name);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $items = $query->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $inventoryName = null;


        if ($request->name) {
            $inventory = Inventory::find($request->name);
            $inventoryName = $inventory?->name;
        }



        // Add available_stock
        $items->transform(function ($item) {
            $item->available_stock = $item->opening_stock_available;
            return $item;
        });

        $selectedSupplierNames = [];

        if ($items->count()) {
            foreach ($items as $item) {
                if (request('supplier_id') && isset(request('supplier_id')[$item->id])) {
                    $supplier = Supplier::find(request('supplier_id')[$item->id]);
                    $selectedSupplierNames[$item->id] = $supplier?->supplier_name;
                }
            }
        }

        return view('inventory::inventory.opening_stock', compact('items', 'inventories', 'suppliers', 'categories', 'placements', 'inventoryName','selectedSupplierNames'));
    }





    public function storeOpeningStock(Request $request)
    {
        $request->validate([
            'opening_stock'   => 'required|array',
            'opening_stock.*' => 'nullable',
        ]);


        DB::beginTransaction();


        try {
            foreach ($request->opening_stock as $inventoryId => $inputStock) {


                // 1. Skip if empty
                if ($inputStock === null || $inputStock === '') {
                    continue;
                }

                // 2. Format input value
                $cleanStock = str_replace([',', ' '], '', $inputStock);
                $newStock = round((float) $cleanStock, 2);

                // 3. 🔥 AGAR VALUE 0 HAI TOH ENTRY NA KAREIN
                if ($newStock <= 0) {
                    continue;
                }

                $inventory = Inventory::find($inventoryId);
                if (!$inventory) {
                    continue;
                }

                $supplierId = $request->supplier_id[$inventoryId] ?? null;
                $placement = $request->placement[$inventoryId] ?? null;

                // Purani value sirf comparison ke liye fetch kar rahe hain
                $oldStock = round((float) ($inventory->opening_stock ?? 0), 2);

                // 4. Update Inventory Table (Actual stock update)
                $inventory->update([
                    'placement' => $placement,
                    'opening_stock' => $newStock,
                ]);

                // 5. 🔥 DIRECT ENTRY: Jo value aayi hai wahi quantity mein jayegi
                StockTransaction::create([
                    'inventory_id' => $inventory->id,
                    'supplier_id'  => $supplierId,
                    'txn_date'     => now()->toDateString(),
                    'txn_type'     => 'In',
                    'quantity'     => $newStock, // <--- Yahan difference nahi, direct value hai
                    'ref_type'     => 'OPENING_STOCK_ADJUST',
                    'remarks'      => "Direct opening stock entry: {$newStock}",
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Stock updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Opening Stock Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    // public function requiredVsAvailable()
    // {
    //     $projects = Project::with([
    //         'projectProducts.product.productItems', // product BOM items
    //         'projectItems'                          // ✅ direct project items (project_items table)
    //     ])
    //         ->whereNotIn('status', ['completed', 'hold'])
    //         ->get();

    //     $requirements = [];


    //     foreach ($projects as $project) {

    //         // ✅ 1) Requirements from PRODUCTS (projectProducts -> productItems)
    //         foreach ($project->projectProducts as $projectProduct) {

    //             $productQty = (int) ($projectProduct->quantity ?? 0);
    //             if ($productQty <= 0) continue;

    //             foreach (($projectProduct->product->productItems ?? []) as $item) {

    //                 $inventoryId = (int) $item->inventory_id;
    //                 if (!$inventoryId) continue;

    //                 $requiredQty = $productQty * (int) ($item->quantity ?? 0);

    //                 if (!isset($requirements[$inventoryId])) {
    //                     $requirements[$inventoryId] = 0;
    //                 }

    //                 $requirements[$inventoryId] += $requiredQty;
    //             }
    //         }

    //         // ✅ 2) Requirements from PROJECT ITEMS (direct project_items)
    //         foreach (($project->projectItems ?? []) as $pi) {

    //             $inventoryId = (int) $pi->inventory_id;
    //             if (!$inventoryId) continue;

    //             $requiredQty = (int) ($pi->quantity ?? 0);

    //             if (!isset($requirements[$inventoryId])) {
    //                 $requirements[$inventoryId] = 0;
    //             }

    //             $requirements[$inventoryId] += $requiredQty; // ✅ add into same requirement
    //         }
    //     }

    //     // Inventory table se available stock lao
    //     $inventories = Inventory::whereIn('id', array_keys($requirements))->get();

    //     $data = [];

    //     $inventoryIds = $inventories->pluck('id');

    //     $transactions = StockTransaction::select('inventory_id', 'txn_type', 'ref_type', 'quantity')->whereIn('inventory_id', $inventoryIds)
    //         ->get()
    //         ->groupBy('inventory_id');


    //     foreach ($inventories as $inventory) {

    //         $rows = $transactions->get($inventory->id, collect());


    //         $in = $rows->where('txn_type', 'In')->where('ref_type', '!=', 'Finish')->sum('quantity');
    //         $out = $rows->where('txn_type', 'Out')->where('ref_type', '!=', 'Machining')->sum('quantity');


    //         $finish = $rows->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');


    //         $mc = $rows->where('txn_type', 'Out')->where('ref_type', 'Machining')->sum('quantity');

    //         $classification = $inventory->classification;

    //         if ($classification === 'FINISH' || $classification === "" || $classification === "null") {
    //             $finalFnsh = $in - $out;
    //             $finalMc = 0;
    //             $semifinish = 0;
    //         } else {
    //             $finalMc =      $mc - $finish;
    //             $finalFnsh =   $finish -  $out;
    //             $semifinish = $in - $out - $finalMc - $finalFnsh;
    //         }

    //         $total = $in - $out;


    //         $required   = (int) ($requirements[$inventory->id] ?? 0);
    //         $difference = $total - $required;

    //         $data[] = [
    //             'inventory_name' => $inventory->name,
    //             'required'       => $required,
    //             'available'      => $total,
    //             'machine_available'  => $semifinish,
    //             'finish'         => $finalFnsh,
    //             'machining'      => $finalMc,
    //             'difference'     => $difference,
    //             'short'          => $difference >= 0, // true means stock ok
    //         ];
    //     }

    //     $data = collect($data)
    //         ->sortBy('short') // shortage first
    //         ->values()
    //         ->all();


    //     return view('inventory::inventory.required_vs_available', compact('data'));
    // }

public function requiredVsAvailable(Request $request)
{
    $projects = Project::with([
        'projectProducts.product.productItems',
        'projectItems'
    ])
        ->where('status', 'in_progress')
        ->get();

    $runningProjectIds = $projects->pluck('id')->filter()->values();
    $runningProjectIdSet = $runningProjectIds->flip();
    $requirements = [];

    foreach ($projects as $project) {
        foreach ($project->projectProducts as $projectProduct) {
            $productQty = (int) ($projectProduct->quantity ?? 0);
            if ($productQty <= 0) continue;

            foreach (($projectProduct->product->productItems ?? []) as $item) {
                $inventoryId = (int) $item->inventory_id;
                if (!$inventoryId) continue;

                $requirements[$inventoryId] = ($requirements[$inventoryId] ?? 0)
                    + ($productQty * (int) ($item->quantity ?? 0));
            }
        }

        foreach (($project->projectItems ?? []) as $pi) {
            $inventoryId = (int) $pi->inventory_id;
            if (!$inventoryId) continue;

            $requirements[$inventoryId] = ($requirements[$inventoryId] ?? 0)
                + (int) ($pi->quantity ?? 0);
        }
    }

    $data = [];

    if (!empty($requirements)) {
        $query = Inventory::whereIn('id', array_keys($requirements));

        if ($request->filled('name')) {
            $query->where('id', $request->name);
        }

        $inventories = $query->get();
        $inventoryIds = $inventories->pluck('id')->values();

        $allowedMachineIdSet = StockTransaction::whereIn('project_id', $runningProjectIds)
            ->whereNotNull('machine_id')
            ->pluck('machine_id')
            ->unique()
            ->values()
            ->flip();

        $transactions = StockTransaction::select(
            'inventory_id',
            'txn_type',
            'ref_type',
            'quantity',
            'project_id',
            'machine_id'
        )
            ->whereIn('inventory_id', $inventoryIds)
            ->get()
            ->groupBy('inventory_id');

        foreach ($inventories as $inventory) {
            $rows = $transactions->get($inventory->id, collect());

            $in = $rows->where('txn_type', 'In')->where('ref_type', '!=', 'Finish')->sum('quantity');
            $out = $rows->where('txn_type', 'Out')->where('ref_type', '!=', 'Machining')->sum('quantity');
            $finish = $rows->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');
            $mc = $rows->where('txn_type', 'Out')->where('ref_type', 'Machining')->sum('quantity');

            $consumption = $rows
                ->where('txn_type', 'Out')
                ->filter(function ($r) use ($runningProjectIdSet, $allowedMachineIdSet) {
                    $hasRunningProject = !empty($r->project_id) && $runningProjectIdSet->has($r->project_id);
                    $hasAllowedMachine = !empty($r->machine_id) && $allowedMachineIdSet->has($r->machine_id);

                    return $hasRunningProject || $hasAllowedMachine;
                })
                ->sum('quantity');

            $classification = $inventory->classification;

            if ($classification === 'FINISH' || $classification === "" || $classification === "null") {
                $finalFnsh = $in - $out;
                $finalMc = 0;
                $semifinish = 0;
            } else {
                $finalMc = $mc - $finish;
                $finalFnsh = $finish - $out;
                $semifinish = $in - $out - $finalMc - $finalFnsh;
            }

            $total = $in - $out;
            $required = (int) ($requirements[$inventory->id] ?? 0) - (float) $consumption;
            $diff = $required - $total;

            $data[] = [
                'inventory_name' => $inventory->name,
                'inventory_id' => $inventory->id,
                'inventory_model' => $inventory->model,
                'classification' => $inventory->classification,
                'required' => $required,
                'available' => $total,
                'machine_available' => $semifinish,
                'finish' => $finalFnsh,
                'machining' => $finalMc,
                'consumption' => (float) $consumption,
                'short_qty' => max($diff, 0),
                'extra_qty' => max(-$diff, 0),
                'short_extra' => $diff,
                'short' => $diff <= 0,
            ];
        }
    }

    $sortBy = $request->input('sort_by', 'short_extra');
    $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
    $allowedSorts = ['required', 'available', 'short_extra'];

    if (!in_array($sortBy, $allowedSorts, true)) {
        $sortBy = 'short_extra';
    }

    $data = collect($data);
    $data = $sortDirection === 'asc'
        ? $data->sortBy($sortBy)
        : $data->sortByDesc($sortBy);

    $data = $data->values()->all();

    $totals = [
        'required' => collect($data)->sum('required'),
        'available' => collect($data)->sum('available'),
        'short_qty' => collect($data)->sum('short_qty'),
        'extra_qty' => collect($data)->sum('extra_qty'),
    ];

    $inventoryName = null;
    if ($request->filled('name')) {
        $inventory = Inventory::find($request->name);
        if ($inventory) {
            $inventoryName = trim($inventory->name . ' ' . $inventory->model);
        }
    }

    return view('inventory::inventory.required_vs_available', compact('data', 'inventoryName', 'totals', 'sortBy', 'sortDirection'));
}

    public function search(Request $request)
{
    $search = $request->q;

    $inventories = Inventory::query()
        // Deleted items hide karne ke liye (agar aapke table me is_deleted column hai)
        ->where('is_deleted', 0) 
        ->when($search, function ($q) use ($search) {
            // Group the OR condition properly
            $q->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('model', 'like', "%$search%");
            });
        })
        ->limit(100)
        ->get();

    return response()->json([
        'results' => $inventories->map(function ($item) {
            return [
                'id' => $item->id,
                // Display me aapne already name aur model rakha hai, jo bilkul sahi hai
                'text' => $item->name . ' ' . $item->model,
            ];
        })
    ]);
}

public function exportRequiredVsAvailable()
{
    return Excel::download(
        new RequiredVsAvailableExport(),
        'required_vs_available.xlsx'
    );
}
}
