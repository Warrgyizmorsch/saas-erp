<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\RequestSlip;
use Modules\Inventory\App\Models\RequestSlipHistory;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Notifications\RequestSlipStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $users    = User::orderBy('name')->get();
            $projects = Project::orderBy('name')->get();

            $query = RequestSlip::with([
                'creator',
                'rows.inventory',
                'histories.user',
                'project',
            ]);

            /**
             * ROLE-WISE VISIBILITY
             * --------------------------------------
             * 1,2,5  → Pending/Hold wali sari dikhegi (status filter default se handle hoga)
             * 7      → khud ki nahi dikhegi
             * 3      → sirf apni + pending_hod/rejected_hod nahi
             * Others → sirf apni
             */
            if (in_array($user->role_id, [1, 2])) {

                // No created_by restriction (status filter default Pending/Hold will apply later)

            } elseif ($user->role_id == 6) {

                $query->where('created_by', '!=', $user->id)
                    ->where('store_rs', '1')
                    ->where(function ($q) use ($user) {
                        $q->where('status', 'Pending')
                            ->orWhere(function ($qq) use ($user) {
                                $qq->where('status', 'Hold')
                                    ->where('hold_by', $user->id);
                            });
                    });
            } elseif ($user->role_id == 5) {

                $query->where('store_rs', '1')
                    ->where(function ($q) use ($user) {
                        $q->where('status', 'Approved HOD')
                            ->orWhere(function ($qq) use ($user) {
                                $qq->where('status', 'Hold')
                                    ->where('hold_by', $user->id);
                            });
                    });
            } else {

                $query->where('created_by', $user->id)
                    ->where('store_rs', '1')
                    ->where(function ($q) use ($user) {
                        $q->whereIn('status', ['Pending', 'Approved HOD'])
                            ->orWhere(function ($qq) use ($user) {
                                $qq->where('status', 'Hold')
                                    ->where('hold_by', $user->id);
                            });
                    });
            }


            /**
             * FILTERS
             */

            // Status Filter (only if user selects)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // RS Code Filter
            if ($request->filled('rs_code')) {
                $cleanCode = preg_replace('/\D/', '', $request->rs_code);
                if (!empty($cleanCode)) {
                    $query->where('rs_id', $cleanCode);
                }
            }

            // User Filter
            if ($request->filled('user')) {
                $query->where('created_by', $request->user);
            }

            // Project Filter
            if ($request->filled('project')) {
                $query->where('project_id', $request->project);
            }

            // Results
            $requestSlips = $query
                ->orderBy('created_on', 'desc')
                ->paginate(10)
                ->withQueryString();

            // Keep Filter Box Open
            $isFilterActive =
                $request->filled('status') ||
                $request->filled('rs_code') ||
                $request->filled('user') ||
                $request->filled('project');

            return view('inventory::approval.requisition', compact(
                'requestSlips',
                'users',
                'projects',
                'isFilterActive'
            ));
        } catch (\Throwable $e) {

            \Log::error('Requisition Approval Index Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Unable to load requisition approvals. Please try again.');
        }
    }




    public function show($id)
    {
        $rs = RequestSlip::with([
            'creator',
            'project',
            'items.inventory.productItems.product',
        ])->findOrFail($id);

        return view('inventory::approval.show', compact('rs'));
    }

    public function approve(Request $request, $id)
    {


        $rs   = RequestSlip::findOrFail($id);


        $user = Auth::user();

        if ($user->role_id != 4 && $user->role_id != 1) {
            return back()->with('error', 'You are not authorized to perform this action.');
        }

        if (!in_array($rs->status, ['Pending', 'rejected'])) {
            return back()->with('error', 'Request Slip is not in an approvable state.');
        }

        $rs->update([
            'status' => 'Approved',
            'approve_comment'  => $request->approval_note,
        ]);

        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => Auth::id(),
            'action'          => 'approved',
            'status'          => 'Approved',
            'remarks'         => $request->approval_note,
        ]);


        return redirect()
            ->route('requisition.index')
            ->with('success', 'Request Slip approved and sent to Store Department.');
    }

    public function reject(Request $request, $id)
    {


        $rs   = RequestSlip::findOrFail($id);


        $user = Auth::user();

        if ($user->role_id != 4 && $user->role_id != 1) {
            return back()->with('error', 'You are not authorized to perform this action.');
        }

        if (!in_array($rs->status, ['Pending', 'rejected'])) {
            return back()->with('error', 'Request Slip is not in an approvable state.');
        }

        $rs->update([
            'status' => 'Rejected',
            'rejected_reason'  => $request->reject_note,
        ]);
        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => Auth::id(),
            'action'          => 'rejected',
            'status'          => 'Rejected',
            'remarks'         => $request->reject_note,
        ]);


        return redirect()
            ->route('requisition.index')
            ->with('success', 'Request Slip approved and sent to Store Department.');
    }



    public function edit($id)
    {
        $data = DB::select("
    SELECT 
        rs.requisition_slip_no,
        r_rows.order_qty,
        inv.name AS inventory_item_name

    FROM requisition_slips rs
    JOIN requisition_slip_rows r_rows 
        ON r_rows.id = rs.rs_id
    JOIN inventories inv
        ON inv.id = r_rows.item_id
    WHERE rs.id = ?
", [$id]);


        return view('inventory::approval.requisition', compact(
            'data',
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $rs   = RequestSlip::findOrFail($id);
            // $user = Auth::user();

            // if ($user->role_id != 4 && $user->role_id != 1) {
            //     return back()->with('error', 'You are not authorized to perform this action.');
            // }

            // $request->validate([
            //     'status'        => 'required|in:Approved,Rejected,Hold',
            //     'approval_note' => 'nullable|string|max:1000',
            // ]);

            $allowedStates = ['Pending', 'rejected', 'Rejected', 'Hold', 'Approved HOD'];

            if (!in_array($rs->status, $allowedStates, true)) {
                return back()->with('error', 'Request Slip is not in an updatable state.');
            }

            $newStatus = $request->status;
            $note      = $request->remarks;
            $rs->hold_by   = $newStatus === 'Hold' ? Auth::id() : null;

            // ✅ update main table (same style as old)
            $updateData = [
                'status' => $newStatus,
            ];


            $updateData['approve_comment'] = $note;

            $rs->update($updateData);

            // ✅ history (same structure as old)
            RequestSlipHistory::create([
                'request_slip_id' => $rs->id,
                'action_by'       => Auth::id(),
                'action'          => strtolower($newStatus),  // approved / rejected
                'status'          => $newStatus,
                'remarks'         => $note,
                
            ]);

            $creator = User::find($rs->created_by);

         
            if ($creator) {
                Notification::create([
                    'notify_id' => $creator->id,
                    "data" => [
                        'module'            => 'request_slip',
                        'request_slip_id'   => $rs->id,
                        'request_slip_no'   => $rs->requisition_slip_no,

                        'status'            => $newStatus,
                        'remarks'           => $note,
                        'approved_by_id'    => auth()->id(),
                        'auth'  => auth()->user()->name ?? 'System',
                        'status_color' => match ($newStatus) {
                            'Hold' => 'text-danger',
                            'Rejected' => 'text-danger',
                            'Approved' => 'text-success',
                            'Pending' => 'text-warning',
                            default => 'text-dark',
                        },

                        'message' => "Your Request Slip {$rs->requisition_slip_no} has been .",
                    ],
                ]);
            }

            DB::commit();

            //  same redirect as old approve()
            return redirect()
                ->back()
                ->with('success', "Request Slip {$newStatus} successfully.");
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Admin Approvals
    public function admin(Request $request)
    {
        try {
            $users    = User::orderBy('name')->get();
            $projects = Project::orderBy('name')->get();

            $query = RequestSlip::with([
                'creator',
                'rows.inventory',
                'histories.user',
                'project',
            ]);

            // ✅ Sirf exited data
            $query->where('is_exited', 1);

            // ✅ ROLE-WISE VISIBILITY हटाया (sabko sab exited dikhega)

            // FILTERS (optional)
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('rs_code')) {
                $cleanCode = preg_replace('/\D/', '', $request->rs_code);
                if (!empty($cleanCode)) {
                    $query->where('rs_id', $cleanCode);
                }
            }

            if ($request->filled('user')) {
                $query->where('created_by', $request->user);
            }

            if ($request->filled('project')) {
                $query->where('project_id', $request->project);
            }

            // ✅ Pending ऊपर, बाकी नीचे (then latest first)
            $requestSlips = $query
                ->orderByRaw("CASE WHEN LOWER(status) = 'pending' THEN 0 ELSE 1 END")
                ->orderBy('created_on', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('inventory::approval.admin', compact('requestSlips', 'users', 'projects'));
        } catch (\Throwable $e) {
            \Log::error('Requisition Approval Index Failed', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Unable to load data.');
        }
    }
}
