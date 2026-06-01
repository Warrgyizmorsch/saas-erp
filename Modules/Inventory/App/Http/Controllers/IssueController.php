<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Department;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\Issue;
use Modules\Inventory\App\Models\IssueSlipRow;
use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\ProductItem;
use Modules\Inventory\App\Models\RequestSlip;
use Modules\Inventory\App\Models\RequestSlipHistory;
use Modules\Inventory\App\Models\RequestSlipItem;
use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\RequisitionSlipRow;
use Modules\Inventory\App\Models\StockTransaction;
use Modules\Inventory\App\Models\Supplier;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Notifications\RequestSlipCreated;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Log;


class IssueController extends Controller
{
    /**
     * ROLE IDs:
     * 1 = Admin
     * 3 = Supervisor
     * 4 = HOD
     * 5 = Store Department
     */

    public function index(Request $request)
    {
        $user = Auth::user();

        // Dropdown Data
        $users    = User::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        // Base Query (NO role restriction)
        $query = RequestSlip::with([
            'creator',
            'rows.inventory',
            'histories.user',
            'project',
            'issue', // safe to keep
        ]);

        /**
         * FILTERS
         */
        if ($request->filled('status')) {
            $query->whereHas('issue', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
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

        /**
         * ✅ Hide RS whose Issue status = Issued
         */
        $query->whereDoesntHave('issue', function ($q) {
            $q->where('status', 'Issued');
        });

        /**
         * ✅ Only Approved RS
         */
        $requestSlips = $query
            ->whereIn('status', ['Approved','Approved HOD'])
            ->orderBy('created_on', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Keep Filter Box Open
        $isFilterActive =
            $request->filled('status') ||
            $request->filled('rs_code') ||
            $request->filled('user') ||
            $request->filled('project');

        return view('inventory::issue.index', compact(
            'requestSlips',
            'users',
            'projects',
            'isFilterActive'
        ));
    }



    public function create(Request $request)
    {  
        $suppliers = Supplier::all();
        $request->validate(['req_id' => 'required|exists:requisition_slips,id']);

        $reqId = $request->req_id;
        $today = now()->format('Y-m-d');

        // ✅ CHECK: Agar is Requisition ka Issue pehle se bana hai, toh EDIT par bhej do
        $existingIssue = Issue::where('requisition_slip_id', $reqId)->first();
        if ($existingIssue) {
            return redirect()->route('issue.edit', $existingIssue->id);
        }

        // Agar nahi bana, toh puraana logic chalne dein (Create Mode)
        $requisition = RequestSlip::with(['rows.inventory', 'project'])->findOrFail($reqId);

        foreach ($requisition->rows as $row) {
            $item = $row->inventory;
            if (!$item) continue;

            $rows = StockTransaction::where('inventory_id', $item->id)->get();

            $in     = $rows->where('txn_type', 'In')->where('ref_type', '!=', 'Finish')->sum('quantity');
            $out    = $rows->where('txn_type', 'Out')->where('ref_type', '!=', 'Machining')->sum('quantity');
            $finish = $rows->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');
            $mc     = $rows->where('txn_type', 'Out')->where('ref_type', 'Machining')->sum('quantity');

            $cls = strtoupper(trim((string)($item->classification ?? '')));

            if ($cls === 'SEMI_FINISH') {
                $finalMc    = $mc - $finish;
                $finalFnsh  = $finish - $out;
                $semifinish = $in - $out - $finalMc - $finalFnsh;

                $row->sf_stock = max(0, $semifinish);
                $row->mc_stock = max(0, $finalMc);
                $row->fn_stock = max(0, $finalFnsh);
                $row->total_stock = $in - $out;
            } else {
                $row->sf_stock = 0;
                $row->mc_stock = 0;
                $row->fn_stock = $in - $out;
                $row->total_stock = $in - $out;
            }
        }

        $lastIssue = Issue::latest('id')->first();
        $nextId = $lastIssue ? $lastIssue->id + 1 : 1;
        $issueSlipNo = 'IS-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        return view('inventory::issue.create', compact('issueSlipNo', 'requisition', 'today', 'suppliers'));
    }


    public function edit($id)
    {
        $suppliers = Supplier::all();
        $issue = Issue::with(['rows.inventory', 'rows.supplier' ,'requisitionSlip.rows'])->findOrFail($id);
        $requisition = $issue->requisitionSlip;

        $machiningTotals = $issue->rows
            ->where('status', 'partial_machining')
            ->groupBy('requisition_slip_row_id')
            ->map(function ($rows) {
                return $rows->sum(function ($r) {
                    return (float)($r->pending_qty ?? 0);
                });
            });

        $issue->rows = $issue->rows
            ->sortBy('requisition_slip_row_id')
            ->values();

        foreach ($issue->rows as $row) {

            $row->machining_total = (float)($machiningTotals[$row->requisition_slip_row_id] ?? 0);

            $item = $row->inventory;
            if (!$item) continue;

            $txnRows = StockTransaction::where('inventory_id', $item->id)->get();

            // Stock Buckets Calculation
            $in     = $txnRows->where('txn_type', 'In')->where('ref_type', '!=', 'Finish')->sum('quantity');
            $out    = $txnRows->where('txn_type', 'Out')->where('ref_type', '!=', 'Machining')->sum('quantity');
            $finish = $txnRows->where('txn_type', 'In')->where('ref_type', 'Finish')->sum('quantity');
            $mc     = $txnRows->where('txn_type', 'Out')->where('ref_type', 'Machining')->sum('quantity');

            // Logic for Semi-Finish vs Finish
            if (strtoupper($item->classification) === 'SEMI_FINISH') {
                $row->sf_stock = max(0, $in - $out - ($mc - $finish) - ($finish - $out));
                $row->mc_stock = max(0, $mc - $finish);
                $row->fn_stock = max(0, $finish - $out);
            } else {
                $row->fn_stock = $in - $out;
                $row->sf_stock = 0;
                $row->mc_stock = 0;
            }


            $pendingQty = (float)($row->pending_qty ?? 0);
            $totalRequested = (float)($row->quantity ?? 0);
            $alreadyMachining = (float)($row->machining_total ?? 0);

            $remainingRequest = max($totalRequested - $alreadyMachining, 0);
            $availableSF = max($row->sf_stock - $alreadyMachining, 0);

            $row->max_machining = min($remainingRequest, $availableSF);

        }

        
        return view('inventory::issue.edit', compact('issue', 'requisition', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items.*.status'       => 'required',

        ], [
            'items.*.status.required' => 'Status is required',
        ]);


        // Use Database Transaction for safety
        return \DB::transaction(function () use ($request) {

            $items = $request->input('items', []);
            $totalIssue = 0;

            foreach ($items as $item) {
                if (($item['status'] ?? '') === 'full') {
                    $totalIssue += (float) ($item['issue_qty'] ?? 0);
                }
            }

            $rs = RequestSlip::findOrFail($request->requisition_id);

            $issue = new Issue();
            $issue->issue_slip_no      = $request->issue_slip_no;
            $issue->project_id         = $request->project_id;
            $issue->requisition_slip_id = $request->requisition_id;
            $issue->employee_id        = $rs->employee_id;
            $issue->transaction_date   = $request->issue_date;
            $issue->department_id      = auth()->user()->department_id;
            $issue->created_on         = now();
            $issue->created_by         = auth()->user()->id;
            $issue->total_issue_qty    = $totalIssue;
            $issue->total_req_qty      = (float) ($rs->total_qty ?? 0);
            $issue->total_pending_qty  = max(0, (float) ($rs->total_qty ?? 0) - $totalIssue);
            if ((float) ($rs->total_qty ?? 0) == $totalIssue) {
                $issue->status = 'Issued';
            } else {
                $issue->status = 'Partially Issued';
            }

            $issue->save();

            foreach ($items as $item) {
                $rsRow = RequisitionSlipRow::findOrFail($item['row_id']);
                $rsRow->status = $item['status'];

                // FIX: Use '==' for comparison, not '='
                if ($item['status'] == 'full') {
                    $rsRow->issued_qty = $rsRow->issued_qty + $item['issue_qty'];
                } elseif ($item['status'] == 'partial_out_of_stock') { // Fixed: added '=='
                    $rsRow->order_qty =  $rsRow->order_qty + $item['issue_qty'];
                } elseif ($item['status'] == 'partial_machining') { // Ab ye chalega
                    $rsRow->pending_qty = $rsRow->pending_qty + $item['issue_qty'];
                }
                $rsRow->save();

                $issueRow = new IssueSlipRow();
                $issueRow->issue_slip_id            = $issue->id;
                $issueRow->supplier_id              = $item['supplier_id'];
                $issueRow->requisition_slip_row_id  = $item['row_id'];
                $issueRow->item_id                  = $item['inventory_id'];
                $issueRow->quantity                 = $rsRow->quantity;
                $issueRow->machine_id               = $rsRow->machine_id;
                $issueRow->status                   = $item['status'];


                // FIX: Check status correctly for IssueSlipRow too
                if ($item['status'] == 'full') {
                    $issueRow->issue_qty = $item['issue_qty'];
                } elseif ($item['status'] == 'partial_out_of_stock') {
                    $issueRow->order_qty = $item['issue_qty'];
                } elseif ($item['status'] == 'partial_machining') {
                    $issueRow->pending_qty = $item['issue_qty'];
                }
                $issueRow->save();

                if ($item['status'] == 'full') {
                    $txn = new StockTransaction();
                    $txn->project_id      = $request->project_id;
                    $txn->machine_id      = $rsRow->machine_id;
                    $txn->inventory_id    = $item['inventory_id'];
                    $txn->quantity        = $item['issue_qty'];
                    $txn->txn_type        = 'Out';
                    $txn->ref_type        = 'Issue';
                    $txn->issue_by        = auth()->user()->id;
                    $txn->requision_id    = $rs->id;
                    $txn->issued_to       = $rs->employee_id;
                    $txn->issue_slip_id   = $issue->id;
                    $txn->supplier_id     = $item['supplier_id'];
                    $txn->txn_date        = now();
                    $txn->save();
                }
            }

            return redirect()->route('issue.view-list')->with('success', 'Issue created successfully!');
        });
    }



    public function update(Request $request, $issueId)
    {
        return \DB::transaction(function () use ($request, $issueId) {

            $items = $request->input('items', []);

            $rs    = RequestSlip::findOrFail($request->requisition_id);
            $issue = Issue::findOrFail($issueId);

            // ===============================
            // HEADER TOTAL (only FULL)
            // ===============================
            $newFullIssueQty = 0;
            foreach ($items as $item) {
                if (($item['status'] ?? '') === 'full') {
                    $newFullIssueQty += (float)($item['issue_qty'] ?? 0);
                }
            }

            $issue->total_issue_qty   = (float)$issue->total_issue_qty + $newFullIssueQty;
            $issue->total_pending_qty = max(0, (float)$rs->total_qty - $issue->total_issue_qty);
            $issue->status            = ($issue->total_issue_qty >= (float)$rs->total_qty)
                ? 'Issued'
                : 'Partially Issued';
            $issue->save();

            // ===============================
            // LOOP ITEMS
            // ===============================
            foreach ($items as $index => $item) {

                $issueQty   = (float)($item['issue_qty'] ?? 0);
                $status     = $item['status'] ?? '';
                $is_clone       = $item['is_clone'] ?? '';
                $supplierId = $item['supplier_id'] ?? null;

                if ($issueQty <= 0 || $status === '') {
                    continue;
                }


                $rsRow = RequisitionSlipRow::findOrFail($item['rs_row_id']);

                $oosRows = IssueSlipRow::where('issue_slip_id', $issue->id)
                    ->where('requisition_slip_row_id', $rsRow->id)
                    ->where('status', 'partial_out_of_stock')
                    ->first();

                    $hasOOS = collect($items)->contains(function ($i) use ($rsRow) {
                    return ($i['rs_row_id'] == $rsRow->id)
                        && (($i['status'] ?? '') === 'partial_out_of_stock');
                });

                // =================================================
                // 2️⃣ PARTIAL OUT OF STOCK → OVERWRITE SAME ROW
                // =================================================
                if ($status === 'partial_out_of_stock') {
                    $oosRow = IssueSlipRow::where('issue_slip_id', $issue->id)
                        ->where('requisition_slip_row_id', $rsRow->id)
                        ->where('status', 'partial_out_of_stock')
                        ->first();

                    if ($oosRow) {

                        $oosRow->order_qty   = $issueQty;     // overwrite
                        $oosRow->supplier_id = $supplierId;
                        $oosRow->save();
                    }

                    // RS update
                    $rsRow->order_qty = $issueQty;
                    $rsRow->status    = 'partial_out_of_stock';
                    $rsRow->save();

                    continue;
                }

                // =================================================
                // 3️⃣ PARTIAL MACHINING → CREATE NEW ROW
                // =================================================
                if ($status === 'partial_machining') {
                    if ($oosRows && ($oosRows->order_qty - $issueQty == 0) && !$hasOOS ) {

                        $oosRows->order_qty   = 0;
                        $oosRows->pending_qty = $issueQty;
                        $oosRows->status      = 'partial_machining';
                        $oosRows->supplier_id = $supplierId;
                        $oosRows->save();

                        $rsRow->order_qty   = 0;
                        $rsRow->pending_qty += $issueQty;
                        $rsRow->status      = 'partial_machining';
                        $rsRow->save();
                    }
                    // 👉 CASE-2: SPLIT (new row)
                    else {

                        $newRow = new IssueSlipRow();
                        $newRow->issue_slip_id           = $issue->id;
                        $newRow->requisition_slip_row_id = $rsRow->id;
                        $newRow->item_id                 = $item['inventory_id'];
                        $newRow->machine_id              = $rsRow->machine_id;
                        $newRow->supplier_id             = $supplierId;

                        $newRow->quantity      = (float)$rsRow->quantity;
                        $newRow->pending_qty   = $issueQty;
                        $newRow->issue_qty     = 0;
                        $newRow->order_qty     = 0;
                        $newRow->status        = 'partial_machining';
                        $newRow->save();

                          if(!$hasOOS){
                           $oosRows->order_qty -= $issueQty;
                            $oosRows->save();
                        }

                        // RS update
                        $rsRow->pending_qty = (float)$rsRow->pending_qty + $issueQty;
                        $rsRow->status      = 'partial_machining';
                        $rsRow->save();

                        continue;
                    }
                }

                // =================================================
                // 4️⃣ FULL → NORMAL EXISTING UPDATE
                // =================================================
                if ($status === 'full') {
                    $issueRowId = $item['issue_row_id'] ?? $item['clone_of_issue_row_id'];

                    $issueRow = IssueSlipRow::findOrFail($issueRowId);
                    if ($issueRow && ( $issueRow->issue_qty  +  $issueQty == $issueRow->quantity ) ) {

                        $issueRow->issue_qty = $issueQty;
                        $issueRow->order_qty = 0;
                        $issueRow->status    = 'full';
                        $issueRow->supplier_id = $supplierId;
                         
                        $issueRow->save();
                    } else {

                        // ➕ NEW FULL ROW
                        $newRow = new IssueSlipRow();
                        $newRow->issue_slip_id           = $issue->id;
                        $newRow->requisition_slip_row_id = $rsRow->id;
                        $newRow->item_id                 = $item['inventory_id'];
                        $newRow->machine_id              = $rsRow->machine_id;
                        $newRow->supplier_id             = $supplierId;

                        $newRow->quantity  = $rsRow->quantity;
                        $newRow->issue_qty = $issueQty;
                        $newRow->status    = 'full';
                        $newRow->save();

                        // ➖ Reduce order_qty in SAME OOS row
                        if(!$hasOOS){
                           $issueRow->order_qty -= $issueQty;
                            $issueRow->save();
                        }
                       
                    }
                    // RS update
                    $rsRow->issued_qty = (float)$rsRow->issued_qty + $issueQty;
                    if (!$hasOOS) {
                        $rsRow->order_qty  = (float)$rsRow->order_qty - $issueQty;
                    }
                    $rsRow->status     = ($rsRow->issued_qty >= $rsRow->quantity)
                        ? 'full'
                        : 'partial_issue';
                    $rsRow->save();

                    // STOCK TXN
                    $txn = new StockTransaction();
                    $txn->project_id    = $request->project_id;
                    $txn->machine_id    = $rsRow->machine_id;
                    $txn->inventory_id  = $item['inventory_id'];
                    $txn->quantity      = $issueQty;
                    $txn->txn_type      = 'Out';
                    $txn->ref_type      = 'Issue';
                    $txn->issue_by      = auth()->id();
                    $txn->requision_id  = $rs->id;
                    $txn->issued_to     = $rs->employee_id;
                    $txn->issue_slip_id = $issue->id;
                    $txn->supplier_id   = $supplierId;
                    $txn->txn_date      = now();
                    $txn->save();
                }
            }



            return redirect()
                ->route('issue.index')
                ->with('success', 'Issue updated successfully!');
        });
    }





    /**
     * Helper function for Stock Transaction to keep code clean
     */
    private function createStockTxn($requisition, $issue, $item, $qty)
    {
        $reqRow = $requisition->rows->where('id', $item['row_id'])->first();
        StockTransaction::create([
            'project_id'    => $requisition->project_id,
            'inventory_id'  => $item['inventory_id'],
            'machine_id'    => $reqRow ? $reqRow->machine_id : null,
            'txn_date'      => now(),
            'txn_type'      => 'Out',
            'quantity'      => $qty,
            'ref_type'      => 'Issue Slip',
            'ref_no'        => $issue->id,
            'issued_to'     => $requisition->employee_id,
            'issue_by'      => Auth::id(),
            'requision_id'  => $requisition->id,
            'issue_slip_id' => $issue->id,
        ]);
    }





    public function show($id)
    {
        // Attempt to fetch Issue using requisition_slip_id
        $issue = Issue::with([
            'rows.inventory',
            'rows.machine',
            'requisitionSlip.creator',
            'project'
        ])
            ->where('requisition_slip_id', $id)
            ->first();


        // If Issue not found, fetch Requisition Slip instead
        if (is_null($issue)) {

            $requisition = RequestSlip::with([
                'rows.inventory',
                'rows.machine',
                'creator',
                'project'
            ])
                ->find($id);

            // If neither Issue nor Requisition exists
            if (is_null($requisition)) {
                return redirect()
                    ->route('issue.index')
                    ->with('error', 'Record not found.');
            }

            // Create a fallback Issue instance to prevent view errors
            $issue = new Issue([
                'id'               => null,
                'issue_slip_no'    => 'N/A (Pending Issue)',
                'transaction_date' => now(),
                'status'           => 'pending',
            ]);

            // Manually assign relationships
            $issue->setRelation('project', $requisition->project);
            $issue->setRelation('requisitionSlip', $requisition);
            $issue->setRelation('rows', $requisition->rows);
        }

        return view('inventory::issue.show-create-issue', compact('issue'));
    }


    // IssueController.php

    public function viewList(Request $request)
    {

        $request->validate(
            [
                'from_date' => 'nullable|date|',
                'to_date'   => 'nullable|date|after_or_equal:from_date',
            ],
            [
                'from_date.before_or_equal' => 'From date must be earlier than To date',
                'to_date.after_or_equal'    => 'To date must be after From date',
            ]
        );

        $query = Issue::with([
            'requisitionSlip.project',
            'requisitionSlip.rows',
        ]);

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('transaction_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // 🔹 Filter by Issue Slip No
        if ($request->filled('issue_no')) {
            $query->where('issue_slip_no', 'like', '%' . $request->issue_no . '%');
        }

        // 🔹 Filter by Project
        if ($request->filled('project_id')) {
            $query->whereHas('requisitionSlip.project', function ($q) use ($request) {
                $q->where('id', $request->project_id);
            });
        }

        // 🔹 Filter by Requisition No
        if ($request->filled('req_no')) {
            $query->whereHas('requisitionSlip', function ($q) use ($request) {
                $q->where('requisition_slip_no', 'like', '%' . $request->req_no . '%');
            });
        }

        // 🔹 Filter by Status (based on computed status)
        if ($request->filled('status')) {
            $query->whereRaw('TRIM(status) = ?', [$request->status]);
        }

        $issueSlipData = $query->latest('id')->paginate(10)->withQueryString();

        $projects = Project::orderBy('name')->get();

        return view('inventory::issue.viewList', compact('issueSlipData', 'projects'));
    }
}
