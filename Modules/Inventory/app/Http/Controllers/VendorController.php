<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::select('id', 'name', 'mobile_no', 'email', 'address', 'city')
            ->orderBy('name');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('mobile_no')) {
            $query->where('mobile_no', 'like', '%' . $request->mobile_no . '%');
        }

        $vendors = $query->paginate(10);
        return view('inventory::vendor.index', compact('vendors'));
    }


    public function create()
    {
        
        return view('inventory::vendor.create');
    }

    public function store(Request $request)
    {
       $request->validate(
    [
        'name'      => 'required|string',
        'email'     => 'required|email',
        'mobile_no' => 'required|digits_between:10,15',
        'city'      => 'required|string',
        'address'   => 'nullable|string',
    ],
    [
        'name.required' => 'Vendor name is required.',

        'email.required' => 'Email is required.',

        'mobile_no.required'       => 'Mobile number is required.',
        'mobile_no.digits_between' => 'Mobile number must be between 10 and 15 digits.',

        'city.required' => 'City is required.',

    ]
);

        Vendor::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'city' => $request->city,
            'address' => $request->address
        ]);

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor created successfully');
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('inventory::vendor.create', compact('vendor'));
    }

    public function update(Request $request, $id)
    {

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'city' => $request->city,
            'address' => $request->address
        ]);

        return redirect()->route('vendor.index')
            ->with('success', 'Vendor updated successfully');
    }
    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);

        $vendor->delete();

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor deleted successfully');
    }
}
