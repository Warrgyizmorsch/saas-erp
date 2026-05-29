<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\App\Models\WarrLead;

class WarrLeadController extends Controller
{
    public function index(Request $request)
    {
        // Total count (without filters)
        $totalLeadsCount = WarrLead::count();

        // Dropdown options
        $sources = WarrLead::query()->whereNotNull('source')->where('source', '!=', '')
            ->distinct()->orderBy('source')->pluck('source');

        $page_url = WarrLead::query()->whereNotNull('page_url')->where('page_url', '!=', '')
            ->distinct()->orderBy('page_url')->pluck('page_url');

        $statuses = WarrLead::query()->whereNotNull('status')->where('status', '!=', '')
            ->distinct()->orderBy('status')->pluck('status');

        // Build filtered query
        $query = WarrLead::query()->latest();

        // Search: name/email/mobile/company
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Date range (created_at)
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pagination + keep query string
        $leads = $query->paginate(10)->withQueryString();

        // Filtered count (after filters)
        $filteredLeadCount = $leads->total();

        return view('crm::crm.warr-leads.index', compact(
            'leads',
            'sources',
            'page_url',
            'statuses',
            'totalLeadsCount',
            'filteredLeadCount'
        ));
    }

    public function update(Request $request, WarrLead $lead)
    {
        $request->validate([
            'status' => 'sometimes|nullable|in:new,hold,executed,dead',
            'comment' => 'sometimes|nullable|string|max:2000',
        ]);

        if ($request->has('status')) {
            $lead->status = $request->status;
        }
        if ($request->has('comment')) {
            $lead->comment = $request->comment;
        }

        $lead->save();

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead,
        ]);
    }
}


