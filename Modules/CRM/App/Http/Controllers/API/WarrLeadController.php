<?php

namespace Modules\CRM\App\Http\Controllers\API;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\WarrLead;

class WarrLeadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'mobile_no' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',

            // ✅ new fields (rules must be strings, NOT values)
            'company_size' => 'nullable|string|max:255',
            'service_categories' => 'nullable|string',
            'comment' => 'nullable|string',
            'page_url' => 'nullable|string|max:2048',

            'message' => 'nullable|string',
        ]);

        $source = 'Website';

        if (!empty($validated['page_url']) && str_contains($validated['page_url'], '/lp')) {
            $source = 'Landing page';
        }

        $lead = WarrLead::create(array_merge(
            $validated,
            [
                'source' => $source,
                'status' => 'new'
            ]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Lead submitted successfully',
            'data' => $lead
        ], 201);
    }
}


