<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Exports\ProductSampleExport;
use DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\ProductItem;
use Modules\Inventory\App\Models\Inventory;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Inventory\App\Imports\ProductImport;


class ProductController extends Controller
{
    // Same page: Add + List
    public function index(Request $request)
    {
        $query = Product::with(['productItems.inventory'])->where('is_deleted',0)->orderBy('id', 'desc');


        // Filter: Product Name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Filter: Items (inventory name)
        if ($request->filled('Estimation_Budget')) {
            $query->where('estimation_budget', $request->Estimation_Budget);
        }

        $products = $query->latest()->paginate(10);

        // For form dropdown
        $product  = null;
        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();
        $allproducts = Product::all();
        return view('inventory::product.index', compact('products', 'product', 'inventories', 'allproducts'));
    }


    // Store new product + its items
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'inventory_id.*' => 'required|exists:inventories,id',
            'estimation_duration' => 'required|numeric|min:0',

        ], [
            'name.required' => 'Machine name is required.',
            'name.unique'   => 'This product name already exists.',
            'inventory_id.*.required' => 'Item selection is required.',
            'estimation_budget.required' => 'Estimation budget must be 0 or greater.',
            'estimation_duration.required' => 'Estimation duration is required.',
        ]);


        if ($validator->fails()) {
            return redirect()
                ->route('product.index')
                ->withErrors($validator)
                ->withInput()
                ->with('show_add_form', true);
        }

        // Product create
        $product = Product::create([
            'name'                => $request->name,
            'is_deleted'          => 0,
            'estimation_duration' => $request->estimation_duration,
        ]);

        // Items (BOM) save karein
        if ($request->has('inventory_id')) {
            foreach ($request->inventory_id as $index => $invId) {
                if ($invId) {
                    ProductItem::create([
                        'product_id'   => $product->id,
                        'inventory_id' => $invId,
                        'quantity'     => $request->quantity[$index] ?? 0,
                        'is_deleted'   => 0,
                    ]);
                }
            }
        }

        return back()->with('success', 'Product added successfully');
    }


    // Edit (same page, form me data load)
    public function edit(Request $request, $id)
    {
        $query    = Product::with(['productItems.inventory'])->orderBy('id', 'desc');
        $product     = Product::with('productItems')->findOrFail($id);
        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Filter: Items (inventory name)
        if ($request->filled('Estimation_Budget')) {
            $query->where('estimation_budget', $request->Estimation_Budget);
        }

        $products = $query->latest()->paginate(10);

        $allproducts = Product::all();

        return view('inventory::product.index', compact('products', 'product', 'inventories', 'allproducts'));
    }

    public function view($id)
    {
        $product = Product::with(['productItems.inventory'])->findOrFail($id);

        return view('inventory::product.view', compact('product'));
    }

    public function pdf($id)
    {

        $product = Product::with(['productItems.inventory.unit'])->findOrFail($id);
        return view('inventory::product.pdf', compact('product'));

        // $product = Product::with(['productItems.inventory'])->findOrFail($id);
        // $product = Product::with(['productItems.inventory.unit'])->findOrFail($id);

        // return Pdf::loadView('product.pdf', compact('product'))
        //     ->setPaper('a4')
        //     ->download('product' . $product->id . '.pdf');
    }
    // Update product + its items
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'inventory_id.*' => 'required|exists:inventories,id',
            'estimation_duration' => 'required|numeric|min:0',

        ], [
            'name.required' => 'Machine name is required.',
            'inventory_id.*.required' => 'Item selection is required.',
            'estimation_budget.required' => 'Estimation budget must be 0 or greater.',
            'estimation_duration.required' => 'Estimation duration is required.',
        ]);

        // Update product fields
        $product->name                = $request->name;
        $product->is_deleted          = ($request->status === 'deleted') ? 1 : 0;
        $product->estimation_duration = $request->estimation_duration;
        $product->save();

        // Purane items delete karke fir add
        ProductItem::where('product_id', $product->id)->delete();

        if ($request->has('inventory_id')) {
            foreach ($request->inventory_id as $index => $invId) {
                if ($invId) {
                    ProductItem::create([
                        'product_id'   => $product->id,
                        'inventory_id' => $invId,
                        'quantity'     => $request->quantity[$index] ?? 0,
                        'is_deleted'   => 0,
                    ]);
                }
            }
        }

        return redirect()->route('product.index')->with('success', 'Product updated successfully');
    }


    // Mark as deleted (flag)
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->is_deleted = 1;
        $product->save();

        return back()->with('success', 'Product deleted successfully');
    }

    // Restore
    public function restore($id)
    {
        $product = Product::findOrFail($id);
        $product->is_deleted = 0;
        $product->save();

        return back()->with('success', 'Product restored successfully');
    }

    public function duplicate($id)
    {
        $product = Product::with(['productItems'])->findOrFail($id);

        return DB::transaction(function () use ($product) {

            // ✅ Unique name: "Name - COPY", "Name - COPY 2"...
            $baseName = trim((string)$product->name);
            $copyName = $baseName . ' - COPY';

            if (Product::where('name', $copyName)->exists()) {
                $n = 2;
                while (Product::where('name', $baseName . " - COPY {$n}")->exists()) {
                    $n++;
                }
                $copyName = $baseName . " - COPY {$n}";
            }

            // ✅ Create new Product
            $newProduct = Product::create([
                'name'                => $copyName,
                'is_deleted'          => 0,
                'estimation_duration' => $product->estimation_duration ?? 0,
            ]);

            // ✅ Copy BOM items (IMPORTANT: productItems)
            if ($product->productItems && $product->productItems->count() > 0) {
                foreach ($product->productItems as $it) {
                    ProductItem::create([
                        'product_id'   => $newProduct->id,
                        'inventory_id' => $it->inventory_id,
                        'quantity'     => $it->quantity ?? 0,
                        'is_deleted'   => 0,
                    ]);
                }
            }

            return redirect()->back()->with('success', "Product duplicated successfully: {$newProduct->name}");
        });
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        try {
            Excel::import(new ProductImport, $request->file('file'));

            return back()->with('success', 'Excel Imported Successfully');

        } catch (\Exception $e) {
            return back()->with('failed', $e->getMessage());
        }
    }

    public function downloadSample()
{
    return Excel::download(new ProductSampleExport, 'product_sample.xlsx');
}
}
