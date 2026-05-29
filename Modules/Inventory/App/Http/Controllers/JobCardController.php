<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\Issue;
use Modules\Inventory\App\Models\IssueSlipRow;
use Modules\Inventory\App\Models\JobCard;
use Modules\Inventory\App\Models\JobCardRow;
use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\RequisitionSlipRow;
use Modules\Inventory\App\Models\StockTransaction;
use Modules\Inventory\App\Models\Supplier;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Models\Vendor;
use Modules\Inventory\App\Notifications\JobCardCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobCardController extends Controller
{

    public function add(Request $request)
    {
        $query = IssueSlipRow::with(['inventory', 'jobcartrow'])
            ->select(
                'issue_slip_rows.*',
                DB::raw('
                (pending_qty - IFNULL(
                    (SELECT SUM(qty)
                     FROM job_card_rows
                     WHERE job_card_rows.issue_slip_row_id = issue_slip_rows.id
                      AND  job_card_rows.item_pending_qty= 1    
                    ), 0)
                ) as calculated_pending_qty
            ')
            )
            ->where('status', 'partial_machining')
            ->where('pr_machining_status', 0)
            ->having('calculated_pending_qty', '>', 0)
            ->orderBy('id', 'desc');

        // 🔍 Filter by Item Name
        if ($request->filled('name')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }


        $Request_items = $query->paginate(20)->withQueryString();

        return view('inventory::job_card.add', compact('Request_items'));
    }


    // JobCardController.php  (create method - FULL)
    public function create(Request $request)
    {
        $suppliers = Supplier::all();
        $emp_id = null;

        // detect issue mode
        $fromIssue = $request->filled('req_id') || $request->filled('req_ids');

        if ($fromIssue) {

            // emp_id (optional)
            if ($request->filled('req_id')) {
                $oneRow = IssueSlipRow::find($request->req_id);
                if ($oneRow) {
                    $issue  = Issue::find($oneRow->issue_slip_id);
                    $emp_id = $issue->employee_id ?? null;
                }
            }

            // ids
            $reqIds = [];
            if ($request->filled('req_ids')) {
                $reqIds = array_values(array_filter(array_map('trim', explode(',', $request->req_ids))));
            } elseif ($request->filled('req_id')) {
                $reqIds = [(int) $request->req_id];
            }

            $Request_items = IssueSlipRow::with('inventory')
                ->select('issue_slip_rows.*')
                ->selectRaw('
                (quantity - IFNULL(
                    (SELECT SUM(qty)
                     FROM job_card_rows
                     WHERE job_card_rows.issue_slip_row_id = issue_slip_rows.id
                    ), 0
                )) as remaining_qty
            ')
                ->whereIn('id', $reqIds)
                ->where('status', 'partial_machining')
                ->get();

            // attach available_stock (optional for display, not used for max in issue mode)
            $Request_items->map(function ($row) {
                $inventoryId = $row->item_id;

                $in = DB::table('stock_transactions')
                    ->where('inventory_id', $inventoryId)
                    ->where('txn_type', 'In')
                    ->where(function ($q) {
                        $q->whereNull('ref_type')
                            ->orWhere('ref_type', '!=', 'Finish');
                    })
                    ->sum('quantity');

                $out = DB::table('stock_transactions')
                    ->where('inventory_id', $inventoryId)
                    ->where('txn_type', 'Out')
                    ->where(function ($q) {
                        $q->whereNull('ref_type')
                            ->orWhere('ref_type', '!=', 'Issue Slip');
                    })
                    ->sum('quantity');

                $row->available_stock = max(0, $in - $out);
                return $row;
            });
        } else {
            $Request_items = collect();
        }

        $inventories = Inventory::where('classification', 'SEMI_FINISH')
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        $vendor = Vendor::all();
        $users  = User::select('id', 'name')->get();

        $today    = now()->format('Y-m-d');
        $nextPrNo = 'JC-' . (100 + JobCard::count());

        return view('inventory::job_card.create', compact(
            'Request_items',
            'inventories',
            'today',
            'nextPrNo',
            'vendor',
            'users',
            'emp_id',
            'suppliers',
            'fromIssue'
        ));
    }


    public function store(Request $request)
    {
        // ✅ Base validation
        $request->validate([
            'transaction_date' => 'required|date',
            'job_card_no'      => 'required|string',
            'priority'         => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'completion_date'  => 'required|date|after_or_equal:transaction_date',

            'items'                    => 'required|array|min:1',
            'items.*.item_id'          => 'required|exists:inventories,id',
            'items.*.request_qty'      => 'required|numeric|min:1',
            'items.*.supplier'      => 'required',
            'items.*.description'      => 'nullable|string|max:255',
        ], [
            'priority.required' => 'Please select priority',
            'vendor.required'   => 'Please select vendor',
            'completion_date.after_or_equal' => 'Completion date must be equal to or after transaction date',
            'items.required'    => 'Please add at least one item',
            'items.*.request_qty.min' => 'Please add at least one item',
            'items.*.item_id.required' => 'Please select an item',
            'items.*.item_id.exists'   => 'Selected item is invalid',
            'items.*.supplier.required' => 'please select supplier',
        ]);

        // ✅ Job type validation
        if ($request->job_type === 'in_house') {
            $request->validate([
                'employee' => 'required|exists:users,id',
            ], [
                'employee.required' => 'Please select employee',
            ]);
        }

        if ($request->job_type === 'out_source') {
            $request->validate([
                'vendor' => 'required|exists:vendors,id',
            ], [
                'vendor.required' => 'Please select vendor',
            ]);
        }

        DB::beginTransaction();

        try {

            // ✅ Create Job Card
            $jobcard = JobCard::create([
                'transaction_date' => $request->transaction_date,
                'job_card_no'      => $request->job_card_no,
                'priority'         => $request->priority,
                'total_qty'        => $request->total_qty,
                'vendor_id'        => $request->job_type === 'out_source' ? $request->vendor : null,
                'employee_id'      => $request->job_type === 'in_house' ? $request->employee : null,
                'pending_qty'      => $request->total_qty,
                'completion_date'  => $request->completion_date,
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                // reset per item
                $issueData = null;

                // ✅ Create Job Card Row
                $jobRow = JobCardRow::create([
                    'job_card_id'        => $jobcard->id,
                    'item_id'            => $item['item_id'],
                    'issue_slip_row_id'  => $item['row_id'] ?? null,
                    'qty'                => $item['request_qty'],
                    'supplier_id'           => $item['supplier'],
                    'item_pending_qty'   => $item['request_qty'],
                    'description'        => $item['description'] ?? null,
                ]);

                /**
                 * ✅ If linked to Issue Slip Row:
                 * 1) append JSON in description
                 * 2) decrease pending_qty
                 * 3) if pending_qty becomes 0 => pr_machining_status = 1
                 */
                if (!empty($item['row_id'])) {

                    //  Lock row so concurrent job cards don't break qty
                    $issueData = IssueSlipRow::with('issueSlip')
                        ->select('id', 'quantity', 'pending_qty', 'machine_id', 'issue_slip_id', 'description', 'pr_machining_status')
                        ->where('id', $item['row_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($issueData) {

                        // -------- ✅ APPEND IN DESCRIPTION (NO OVERWRITE) --------
                        $existingDesc = (string) ($issueData->description ?? '');
                        $descArray = [];

                        $decoded = json_decode($existingDesc, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $descArray = $decoded;
                        } else {
                            // preserve old text
                            if (trim($existingDesc) !== '') {
                                $descArray[] = [
                                    'legacy_text' => $existingDesc,
                                    'saved_at'    => now()->toDateTimeString(),
                                ];
                            }
                        }

                        //  append new job card info
                        $descArray[] = [
                            'job_card_id'     => $jobcard->id,
                            'job_card_no'     => $jobcard->job_card_no,
                            'job_type'        => strtoupper($request->job_type), // IN_HOUSE / OUT_SOURCE
                            'employee_id'     => $request->job_type === 'in_house' ? $jobcard->employee_id : null,
                            'vendor_id'       => $request->job_type === 'out_source' ? $jobcard->vendor_id : null,
                            'completion_date' => $jobcard->completion_date,
                            'qty'             => (float) $item['request_qty'],
                            'created_at'      => now()->toDateTimeString(),
                        ];

                        // -------- ✅ PENDING QTY UPDATE (your requirement) --------
                        // If you want exactly "-1" per jobcard creation (not by qty), use: $decreaseBy = 1;
                        // If you want decrease by requested qty, use: $decreaseBy = (float)$item['request_qty'];
                        $decreaseBy = (float) $item['request_qty']; // ✅ recommended (qty wise)

                        $currentPending = (float) ($issueData->pending_qty ?? 0);
                        $newPending     = max($currentPending - $decreaseBy, 0);

                        IssueSlipRow::where('id', $issueData->id)->update([
                            'description'         => json_encode($descArray, JSON_UNESCAPED_UNICODE),
                            'pr_machining_status' => ($newPending <= 0) ? 1 : 0,
                        ]);
                    }
                }

                // ✅ Stock Transaction
                StockTransaction::create([
                    'inventory_id' => $item['item_id'],
                    'txn_date'     => now(),
                    'txn_type'     => 'Out',
                    'quantity'     => $item['request_qty'],
                    'ref_type'     => 'Machining',
                    'ref_no'       => $request->job_card_no,
                    'remarks'      => 'job card',
                    'machine_id'   => $issueData?->machine_id ?? null,
                    'project_id'   => $issueData?->issueSlip?->project_id ?? null,
                ]);
            }


            $currentUser = Auth::user();

            $authorityId = $currentUser->authority_id;


            if ($authorityId) {
                Notification::create([
                    'role_id' => $authorityId,
                    "data" => [
                        'module'      => 'job_card',
                        'job_card_id' => $jobcard->id,
                        'job_card_no' => $jobcard->job_card_no,
                        'message'     => 'New Job Card Created',
                        'created_by'  => auth()->id(),
                        'auth'        => auth()->user()->name ?? 'System',
                    ],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('job_card.view')
                ->with('success', 'job card created successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Unable to create Job Card. Please check required fields.');
        }
    }






    public function view(Request $request)
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

        $query = JobCard::with('vendor', 'employee', 'rows.item', 'rows.issueSlipRow.issueSlip')->where('created_by', auth()->id())->orderBy('id', 'desc');

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('transaction_date', $request->from_date);
        }

        //  Only To Date
        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('transaction_date', $request->to_date);
        }

        //  From + To Date
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('transaction_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // Filter: Job Card No
        if ($request->filled('job_card_no')) {
            $query->where('job_card_no', 'like', '%' . $request->job_card_no . '%');
        }

        // Filter: Priority
        if ($request->filled('priority')) {
            $query->where('priority', 'like', '%' . $request->priority . '%');
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $vendors = Vendor::orderBy('name')->get();


        $jobCard = $query->paginate(20)->withQueryString();

        return view('inventory::job_card.view', compact('jobCard', 'vendors'));
    }

    public function edit($id)
    {
        $jobCard = JobCard::with([
            'rows.item'
        ])->findOrFail($id);

        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();

        $vendors = vendor::all();

        $users = User::select('id', 'name')->get();


        // dd( $vendor);

        return view('inventory::job_card.edit', compact('jobCard', 'vendors', 'inventories', 'users'));
    }

    // new ansh 
    public function update($id)
    {

        $jobCard = JobCard::with('rows')->findOrFail($id);
        $jobCard->status = 'COMPLETED';
        $jobCard->completed_at = now();
        $jobCard->save();

        foreach ($jobCard->rows as $row) {
            $row->item_pending_qty = 0;
            $row->received_qty = 1;

            // $row->employee_id = auth()->user()->id;
            $row->save();

            $issueRow = null;
            if (!empty($row->issue_slip_row_id)) {
                $issueRow = IssueSlipRow::with('issueSlip')->find($row->issue_slip_row_id);

                if ($issueRow) {
                    $issueRow->issue_qty  = $issueRow->issue_qty  + 1;
                    $issueRow->pending_qty = $issueRow->pending_qty - 1;

                    if ((float) $issueRow->pending_qty == 0) {
                        $issueRow->pr_machining_status = 1;
                        $issueRow->status = 'full';
                    }
                    $issueRow->save();
                    $supplierId = $issueRow->supplier_id ?? null;
                    $machineId  = $issueRow->machine_id ?? null;
                    $projectId  = $issueRow->issueSlip->project_id ?? null;



                    // =====================================
                    // 2) ISSUE SLIP MASTER (TOTALS) UPDATE
                    //    total_issue_qty +1, total_pending_qty -1
                    // =====================================
                    if ($issueRow->issue_slip_id) {
                        $issueSlip = Issue::find($issueRow->issue_slip_id);
                        if ($issueSlip) {
                            $issueSlip->total_issue_qty   = $issueSlip->total_issue_qty + 1;
                            $issueSlip->total_pending_qty = $issueSlip->total_pending_qty - 1;

                            if ((float) $issueSlip->total_pending_qty == 0) {
                                $issueSlip->status = 'Issued';
                            }

                            $issueSlip->save();
                        }
                    }

                    // ===========================================
                    // 3) REQUISITION SLIP ROW UPDATE (FROM ISSUE ROW)
                    //    requisition_slip_row_id -> pending_qty -1, issued_qty +1
                    // ===========================================
                    if (!empty($issueRow->requisition_slip_row_id)) {
                        $reqRow = RequisitionSlipRow::find($issueRow->requisition_slip_row_id);

                        if ($reqRow) {
                            $reqRow->issued_qty  = ($reqRow->issued_qty ?? 0) + 1;
                            $reqRow->pending_qty = ($reqRow->pending_qty ?? 0) - 1;

                            if ((float) $reqRow->pending_qty <= 0) {
                                $reqRow->pending_qty = 0;
                            }

                            $reqRow->save();
                        }
                    }
                }
            }

            StockTransaction::create([
                'inventory_id' => $row->item_id,
                'txn_date'     => now(),
                'txn_type'     => 'In',
                'quantity'     => 1,
                'ref_type'     => 'Finish',
                'ref_no'       => $jobCard->job_card_no,
                'remarks'      => 'job card',
                'supplier_id'  => $issueRow->supplier_id ?? $row->supplier_id,
                'vendor_id'    => $jobCard->vendor_id,
                'machine_id'   => $issueRow->machine_id ?? null,
                'project_id'   => $issueRow->issueSlip->project_id ?? null
            ]);
            if ($issueRow) {
                StockTransaction::create([
                    'inventory_id' => $row->item_id,
                    'txn_date'     => now(),
                    'txn_type'     => 'Out',
                    'quantity'     => 1,
                    'ref_type'     => 'Issue Slip',
                    'ref_no'       => $jobCard->job_card_no,
                    'remarks'      => 'job card',
                    'supplier_id'  => $supplierId,
                    'vendor_id'    => $jobCard->vendor_id,
                    'machine_id'   => $machineId,
                    'project_id'   => $projectId,
                ]);
            }
        }
        return redirect()
            ->route('job_card.view')
            ->with('success', 'Job Card marked as Completed successfully');
    }

    // old Manish function
    // public function update(Request $request, $id)
    // {
    //     dd($id);

    //     $request->validate([
    //         'transaction_date' => 'required|date',
    //         'completion_date'  => 'required|date|after_or_equal:transaction_date',

    //         'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
    //         'items' => 'required|array|min:1',
    //         'items.*.item_id' => 'required|exists:inventories,id',
    //         'items.*.request_qty' => 'required|numeric|min:1',
    //         'items.*.item_status' => 'required|in:MACHINING,COMPLETED',
    //     ], [
    //         'vendor_id.required'  => 'Please select vendor',
    //         'completion_date.after_or_equal' =>
    //         'Completion date must be equal to or after transaction date',
    //         'items.*.request_qty.min' => 'Please add at least one item',
    //         'items.*.request_qty.required' => 'Request quantity is required.',
    //         'items.*.item_id.required' => 'Please select an item',
    //         'items.*.item_id.exists'   => 'Selected item is invalid',

    //     ]);

    //     DB::beginTransaction();

    //     try {

    //         // 🔹 Update Job Card (MASTER)
    //         $jobCard = JobCard::findOrFail($id);

    //         $oldReceived = (float) $jobCard->total_received_qty;
    //         $newReceived = (float) $request->total_received_qty;

    //         $finalReceived = (float) $oldReceived + $newReceived;

    //         $totalQty        = (float) $request->total_qty;
    //         $pendingQty      = $totalQty - $finalReceived;

    //         $jobStatus =  ($pendingQty <= 0) ? 'COMPLETED' : 'PENDING';

    //         $completedAt = ($jobStatus === 'COMPLETED') ? now() : null;

    //         $jobCard->update([
    //             'transaction_date' => $request->transaction_date,
    //             'priority'         => $request->priority,
    //             'status'           => $jobStatus,
    //             'vendor_id'        => $request->vendor_id ?? null,
    //             'employee_id'      =>$request->employee ?? null,
    //             'total_qty'        => $request->total_qty,
    //             'total_received_qty' => $finalReceived,
    //             'pending_qty'      => $pendingQty,
    //             'completion_date' => $request->completion_date,
    //             'completed_at'        => $completedAt,
    //         ]);

    //         // dd($jobCard);

    //         $existingRowIds = [];

    //         // 🔹 Update / Insert Job Card Rows
    //         if ($request->has('items')) {
    //             foreach ($request->items as $item) {
    //                 $qty         = (float) $item['request_qty'];
    //                 $newReceived = (float) ($item['received_qty'] ?? 0);
    //                 if (!empty($item['row_id'])) {
    //                     $row = JobCardRow::find($item['row_id']);
    //                     if ($row) {

    //                         $oldReceived   = (float) $row->received_qty;
    //                         $finalReceived = $oldReceived + $newReceived;

    //                         if ($finalReceived > $qty) {
    //                             $finalReceived = $qty;
    //                         }

    //                         $itemPendingQty = $qty - $finalReceived;

    //                         $row->update([
    //                             'item_id'         => $item['item_id'],
    //                             'qty'             => $item['request_qty'],
    //                             'item_pending_qty' => $itemPendingQty,
    //                             'received_qty'     => $finalReceived,
    //                             'status'          => $item['item_status'],
    //                             'description'     => $item['description'] ?? null,
    //                         ]);

    //                         $existingRowIds[] = $row->id;
    //                     }
    //                 }
    //                 // INSERT
    //                 else {
    //                     $itemPendingQty = $qty - $newReceived;
    //                     $newRow = JobCardRow::create([
    //                         'job_card_id'     => $jobCard->id,
    //                         'item_id'         => $item['item_id'],
    //                         'qty'             => $item['request_qty'],
    //                         'received_qty'     => $item['received_qty'],
    //                         'item_pending_qty' => $itemPendingQty,
    //                         'status'          => $item['item_status'],
    //                         'description'     => $item['description'] ?? null,
    //                     ]);

    //                     $existingRowIds[] = $newRow->id;
    //                 }
    //             }
    //         }

    //         // 🔹 Delete removed rows
    //         JobCardRow::where('job_card_id', $jobCard->id)
    //             ->whereNotIn('id', $existingRowIds)
    //             ->delete();


    //         foreach ($request->items as $item) {
    //             $jc = JobCardRow::with('jobCard')
    //                 ->find($item['row_id']);
    //             $issueData = IssueSlipRow::with('issueSlip')
    //                 ->select('machine_id', 'issue_slip_id')
    //                 ->find($jc->issue_slip_row_id);


    //             StockTransaction::create([
    //                 'inventory_id' => $item['item_id'],
    //                 'txn_date'     => now(),
    //                 'txn_type'     => 'in',
    //                 'quantity'     => $item['received_qty'],
    //                 'ref_type'     => 'Finish',
    //                 'ref_no'       => $request->job_card_no,
    //                 'remarks'      => 'job card',
    //                 'machine_id' => $issueData?->machine_id ?? null,
    //                 'project_id' => $issueData?->issueSlip->project_id ?? null,
    //             ]);
    //         }


    //         DB::commit();

    //         return redirect()
    //             ->route('job_card.view')
    //             ->with('success', 'Job Card updated successfully');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->withErrors($e->getMessage());
    //     }
    // }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $jobCard = JobCard::findOrFail($id);

            JobCardRow::where('job_card_id', $id)->delete();

            $jobCard->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Purchase Request deleted permanently');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {

        $jobCard = JobCard::with('rows.item', 'vendor', 'employee')->findorFail($id);


        return view('inventory::job_card.show', compact('jobCard'));
    }
}
