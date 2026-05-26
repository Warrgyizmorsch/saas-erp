<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Supplier;
use Modules\Inventory\App\Models\Category;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with('category');

        // Supplier Name filter
        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->supplier_name . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', 'like', '%' . $request->mobile . '%');
        }

        // Supplier Code filter
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', 'like', '%' . $request->supplier_code . '%');
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $suppliers = $query->paginate(10)->withQueryString();

          $selectedSupplierName = null;


        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $selectedSupplierName = $supplier?->supplier_name;
        }

        $categories = Category::all();

        

        return view('inventory::suppliers.index', compact('suppliers', 'categories','selectedSupplierName'));
    }


    public function create()
    {
        $today = now()->format('Y-m-d');
        $NextEvalution = now()->addYear()->format('Y-m-d');
        $last = Supplier::max('supplier_code');
        $next = $last ? $last + 1 : 100;

        $supplierCode = 'SUP-' . $next;
        $categories = Category::all();

        // ✅ edit ke liye $item null rahega
        $item = null;

        return view('inventory::suppliers.create', compact('today', 'supplierCode', 'NextEvalution', 'categories', 'item'));
    }

    public function store(Request $request)
    {
        $supplierCode = $request->supplier_code;

        $numericCode = (int) str_replace('SUP-', '', $supplierCode);

        $validated = $request->validate([
            'category'          => 'required|exists:categories,id',
            'supplier_name'     => 'required|string|max:255',
            'supplier_code'     => 'required|string|max:100|unique:suppliers,supplier_code',
            'email'             => 'nullable|email|max:255',
            'state'             => 'nullable|string|max:50',
            'city'              => 'nullable|string|max:100',
            'mobile'            => 'nullable|string|max:15',
            'gstin'             => 'nullable|string|max:20',
            'pan'               => 'nullable|string|max:20',
            'supplier_address'  => 'nullable|string',
            'bank_name'         => 'nullable|string|max:255',
            'account_number'    => 'nullable|string|max:50',
            'ifsc'              => 'nullable|string|max:20',
        ]);

        Supplier::create([
            'category'          => $validated['category'],
            'supplier_name'     => $validated['supplier_name'],
            'supplier_code'     => $numericCode,
            'email'             => $validated['email'],
            'state'             => $validated['state'],
            'city'              => $validated['city'],
            'mobile'            => $validated['mobile'],
            'gstin'             => $validated['gstin'],
            'pan'               => $validated['pan'],
            'supplier_address'  => $validated['supplier_address'],
            'bank_name'         => $validated['bank_name'],
            'account_number'    => $validated['account_number'],
            'ifsc'              => $validated['ifsc'],
            'supplier_type'     => 'New',
        ]);

        return redirect()->back()->with('success', 'Supplier created successfully!');
    }


    // public function show($id)
    // {
    //     $supplier = Supplier::findOrFail($id);
    //     return view('inventory::suppliers.show', compact('supplier'));
    // }

    public function edit($id)
    {
        $today = now()->format('Y-m-d');
        $NextEvalution = now()->addYear()->format('Y-m-d');

        $categories = Category::all();

        // ✅ edit wala record
        $item = Supplier::findOrFail($id);

        // ✅ supplier_code auto-generate edit me nahi karna
        $supplierCode = $item->supplier_code;

        return view('inventory::suppliers.create', compact('today', 'supplierCode', 'NextEvalution', 'categories', 'item'));
    }

    public function update(Request $request, $id)
    {
        $supplierCode = $request->supplier_code;

        $numericCode = (int) str_replace('SUP-', '', $supplierCode);

        $item = Supplier::findOrFail($id);


        $validated = $request->validate([
            'category'          => 'required|exists:categories,id',
            'supplier_name'     => 'required|string|max:255',
            'supplier_code'     => 'required|string|max:100|unique:suppliers,supplier_code,' . $item->id,
            'email'             => 'nullable|email|max:255',
            'state'             => 'nullable|string|max:50',
            'city'              => 'nullable|string|max:100',
            'mobile'            => 'nullable|string|max:15',
            'gstin'             => 'nullable|string|max:20',
            'pan'               => 'nullable|string|max:20',
            'supplier_address'  => 'nullable|string',
            'bank_name'         => 'nullable|string|max:255',
            'account_number'    => 'nullable|string|max:50',
            'ifsc'              => 'nullable|string|max:20',
        ]);


        $item->update([
            'category'          => $validated['category'],
            'supplier_name'     => $validated['supplier_name'],
            'supplier_code'     => $numericCode,
            'email'             => $validated['email'],
            'state'             => $validated['state'],
            'city'              => $validated['city'],
            'mobile'            => $validated['mobile'],
            'gstin'             => $validated['gstin'],
            'pan'               => $validated['pan'],
            'supplier_address'  => $validated['supplier_address'],
            'bank_name'         => $validated['bank_name'],
            'account_number'    => $validated['account_number'],
            'ifsc'              => $validated['ifsc'],
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully!');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted!');
    }

    public function search(Request $request)
    {
        $search = $request->q;

        $suppliers = Supplier::query()
            ->when($search, function ($q) use ($search) {
                $q->where('supplier_name', 'like', "%$search%");
            })
            ->limit(100)
            ->get();

        return response()->json([
            'results' => $suppliers->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->supplier_name
                ];
            })
        ]);
    }

    public function searchCode(Request $request)
    {
        $search = $request->q;

        $suppliers = Supplier::query()
            ->when($search, function ($q) use ($search) {
                $q->where('supplier_code', 'like', "%$search%");
            })
            ->limit(100)
            ->get();

        return response()->json([
            'results' => $suppliers->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->supplier_code
                ];
            })
        ]);
    }
}
