<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\LeadSource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index()
    {
        $sources = LeadSource::orderBy('id', 'desc')->paginate(10);
        return view('crm.lead_sources.index', compact('sources'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        LeadSource::create([
            'source_name' => $request->source_name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('lead_sources.index')->with('success', 'Lead Source added successfully.');
    }

    public function update(Request $request, LeadSource $source)
    {
        $request->validate([
            'source_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        $source->update([
            'source_name' => $request->source_name,
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('lead_sources.index')->with('success', 'Lead Source updated successfully.');
    }

    // public function destroy(LeadSource $source)
    // {
    //     $source->delete();
    //     return back()->with('success', 'Lead Source deleted!');
    // }

    public function toggle(LeadSource $source)
    {
        $source->update(['is_active' => !$source->is_active]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $source->is_active,
            ]);
        }

        return back()->with('success', 'Lead Source status updated!');
    }
}


