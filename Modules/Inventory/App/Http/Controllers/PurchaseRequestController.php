<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\IssueSlipRow;
use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\PurchaseRequestItem;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Notifications\PurchaseRequestCreated;
use Modules\Inventory\App\Notifications\PurchaseRquestApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function add(Request $request)
    {
        $query = IssueSlipRow::with('inventory')
            ->leftJoin('purchase_request_items as pri', function ($join) {
                $join->on('pri.issue_slip_row_id', '=', 'issue_slip_rows.id');
            })
            ->select(
                'issue_slip_rows.id as issue_slip_row_id',
                'issue_slip_rows.item_id',
                'issue_slip_rows.id',
                DB::raw(
                    '(issue_slip_rows.order_qty - COALESCE(SUM(pri.requested_qty), 0)) as order_qty'
                )
            )
            ->where('issue_slip_rows.status', 'partial_out_of_stock')
            ->where('issue_slip_rows.pr_status', 0)
            ->groupBy(
                'issue_slip_rows.id',
                'issue_slip_rows.item_id',
                'issue_slip_rows.order_qty'
            )
            ->havingRaw('(issue_slip_rows.order_qty - COALESCE(SUM(pri.requested_qty), 0)) > 0')
            ->orderBy('issue_slip_rows.id', 'DESC');

        if ($request->filled('name')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        $Request_items = $query->get();


        return view('inventory::purchase_request.add', compact('Request_items'));
    }

    public function create(Request $request)
    {
        if ($request->has('req_ids')) {
            $reqIds = explode(',', $request->req_ids);
            $Request_items = IssueSlipRow::with('inventory')
                ->leftJoin('purchase_request_items as pri', function ($join) {
                    $join->on('pri.issue_slip_row_id', '=', 'issue_slip_rows.id');
                })
                ->select(
                    'issue_slip_rows.id as issue_slip_row_id',
                    'issue_slip_rows.item_id',
                    'issue_slip_rows.id',
                    DB::raw(
                        '(issue_slip_rows.order_qty - COALESCE(SUM(pri.requested_qty), 0)) as order_qty'
                    )
                )
                ->whereIn('issue_slip_rows.id', $reqIds)
                ->where('issue_slip_rows.status', 'partial_out_of_stock')
                ->where('issue_slip_rows.pr_status', 0)
                ->groupBy(
                    'issue_slip_rows.id',
                    'issue_slip_rows.item_id',
                    'issue_slip_rows.order_qty'
                )
                ->get();
        } elseif ($request->has('req_id')) {
            $reqId = [$request->req_id];
            $Request_items = IssueSlipRow::with('inventory')
                ->leftJoin('purchase_request_items as pri', function ($join) {
                    $join->on('pri.issue_slip_row_id', '=', 'issue_slip_rows.id');
                })
                ->select(
                    'issue_slip_rows.id as issue_slip_row_id',
                    'issue_slip_rows.item_id',
                    'issue_slip_rows.id',
                    DB::raw(
                        '(issue_slip_rows.order_qty - COALESCE(SUM(pri.requested_qty), 0)) as order_qty'
                    )
                )
                ->where('issue_slip_rows.id', $reqId)
                ->where('issue_slip_rows.status', 'partial_out_of_stock')
                ->where('issue_slip_rows.pr_status', 0)
                ->groupBy(
                    'issue_slip_rows.id',
                    'issue_slip_rows.item_id',
                    'issue_slip_rows.order_qty'
                )
                ->get();
        } else {
            $Request_items = collect();
        }
        $selectedItems = [];

        if ($request->filled('selected_items')) {

            $selectedItems = json_decode(
                $request->selected_items,
                true
            );

            foreach ($selectedItems as $key => $item) {

        $inventory = Inventory::find($item['item_id']);

        $selectedItems[$key]['item_name'] = $inventory?->name ?? '';

        $selectedItems[$key]['inventory_model'] = $inventory?->model ?? '';
    }
        }

        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();

        $today = now()->format('Y-m-d');

        $nextPrNo = 'PR-' . (100 + PurchaseRequest::count());

        $oldItems = old('items');

        if ($oldItems && is_array($oldItems)) {
            foreach ($oldItems as $key => $item) {
                if (!empty($item['item_id'])) {
                    $inventory = Inventory::find($item['item_id']);
                    $oldItems[$key]['item_name'] = $inventory?->name . ' ' . $inventory?->model;
                }
            }
        }


        return view('inventory::purchase_request.create', compact('Request_items', 'selectedItems', 'inventories', 'today', 'nextPrNo', 'oldItems'));
    }

    /**
     * Store purchase request
     */
    public function store(Request $request)
    {

        $request->validate([
            'request_date' => 'required|date',
            'purchase_request_no' => 'required',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'status' => 'required|in:DRAFT,SUBMITTED',
            'comment' => 'nullable|string|max:500',

            'items' => 'required|array|min:1',

            'items.*.item_id' => 'required|exists:inventories,id',
            'items.*.request_qty' => 'required|numeric|min:1',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.required_date' => 'nullable|date|after_or_equal:request_date',
        ], [
            'priority.required' => 'Please select priority',
            'items.required' => 'At least one item is required.',
            'items.*.item_id.required' => 'Inventory item is required.',
            'items.*.request_qty.min' => 'Request quantity must be greater than 0.',
            'items.*.required_date.after_or_equal' => 'Required date must be on or after request date.',
        ]);

        DB::beginTransaction();

        try {

            // 🔹 Save Parent Purchase Request
            $purchaseRequest = PurchaseRequest::create([
                'request_date' => $request->request_date,
                'pr_no' => $request->purchase_request_no,
                'priority' => $request->priority,
                'status' => $request->status,
                'remarks' => $request->comment,
                'total_qty' => $request->total_qty,
                'requested_by' => Auth::user()->id,
                'department_id' => Auth::user()->department_id,
            ]);
            $itemIds = [];

            // 🔹 Save Child Items (Multiple Rows)
            foreach ($request->items as $item) {
                $inventory = Inventory::find($item['item_id']);

                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'issue_slip_row_id'   => $item['issue_slip_row_id'] ?? null,
                    'item_id'             => $item['item_id'],
                    'requested_qty'       => $item['request_qty'],
                    'uom'                 => $inventory?->unit,
                    'description'         => $item['description'] ?? null,
                    'required_date'       => $item['required_date'] ?? null,
                ]);

                $itemIds[] = $item['item_id'];
            }

            if (!empty($item['row_id'])) {

                if ($item['order_qty'] <= $item['request_qty']) {

                    IssueSlipRow::whereIn('item_id', $itemIds)
                        ->where('pr_status', 0)
                        ->where('status', 'partial_out_of_stock')
                        ->update([
                            'pr_status' => 1
                        ]);
                }
            }

            $currentUser = Auth::user();

            $authorityId = $currentUser->authority_id;

            if ($authorityId) {
                Notification::create([
                    'role_id' => $authorityId,
                    "data" => [
                        'module' => 'request_slip',
                        'purchase_Request_id' => $purchaseRequest->id,
                        'purchase_request_number' => $purchaseRequest->pr_no,
                        'auth' => $purchaseRequest->creator->name ?? 'Unknown',
                        'message' => "New Purchase Request Slip generated: ",
                        'url' => route('purchase_request.approval-view'),
                    ],
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchase_request.list-view')
                ->with('success', 'Purchase Request created successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $items = PurchaseRequestItem::with('inventory')
            ->where('purchase_request_id', $id)
            ->get();

        // dd($items);

        $inventories = Inventory::where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        $oldItems = old('items');

        if ($oldItems && is_array($oldItems)) {
            foreach ($oldItems as $key => $item) {
                if (!empty($item['item_id'])) {
                    $inventory = Inventory::find($item['item_id']);
                    $oldItems[$key]['item_name'] = $inventory?->name . ' ' . $inventory?->model;
                }
            }
        }


        return view('inventory::purchase_request.list-edit', compact(
            'purchaseRequest',
            'inventories',
            'items',
            'oldItems',
        ));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'request_date' => 'required|date',
            'purchase_request_no' => 'required',
            'priority' => 'required|in:LOW,NORMAL,HIGH,URGENT',
            'status' => 'required|in:DRAFT,SUBMITTED',
            'comment' => 'nullable|string|max:500',

            'items' => 'required|array|min:1',

            'items.*.item_id' => 'required|exists:inventories,id',
            'items.*.request_qty' => 'required|numeric|min:1',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.required_date' => 'nullable|date|after_or_equal:request_date',
        ], [
            'priority.required' => 'Please select priority',
            'items.required' => 'At least one item is required.',
            'items.*.item_id.required' => 'Inventory item is required.',
            'items.*.request_qty.min' => 'Request quantity must be greater than 0.',
            'items.*.request_qty.required' => 'Request quantity is required.',
            'items.*.required_date.after_or_equal' => 'Required date must be on or after request date.',
        ]);


        DB::beginTransaction();

        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            // 🔹 Update parent PR
            $purchaseRequest->update([
                'request_date' => $request->request_date,
                'priority'     => $request->priority,
                'status'       => $request->status,
                'remarks'      => $request->comment,
                'total_qty'    => $request->total_qty,
            ]);

            $existingItemIds = [];

            foreach ($request->items as $item) {

                // 🔹 OLD ITEM → UPDATE
                if (!empty($item['row_id'])) {

                    $prItem = PurchaseRequestItem::find($item['row_id']);

                    if ($prItem) {
                        $prItem->update([
                            'requested_qty' => $item['request_qty'],
                            'description'   => $item['description'],
                            'required_date' => $item['required_date'],
                        ]);

                        $existingItemIds[] = $prItem->id;
                    }
                }
                // 🔹 NEW ITEM → INSERT
                else {

                    $inventory = Inventory::find($item['item_id']);

                    $newItem = PurchaseRequestItem::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'item_id'             => $item['item_id'],
                        'requested_qty'       => $item['request_qty'],
                        'uom'                 => $inventory?->unit,
                        'description'         => $item['description'],
                        'required_date'       => $item['required_date'],
                    ]);

                    $existingItemIds[] = $newItem->id;
                }
                $itemIds[] = $item['item_id'];

                if ($request->status === 'SUBMITTED' && !empty($item['issue_slip_row_id'])) {

                    $issueSlipRowId = $item['issue_slip_row_id'];

                    // 1️⃣ Issue slip ki original order qty
                    $issueSlipRow = IssueSlipRow::find($issueSlipRowId);

                    if ($issueSlipRow) {

                        // 2️⃣ Ab tak kitni PR ban chuki hai (excluding current PR item if edit)
                        $alreadyRequestedQty = PurchaseRequestItem::where('issue_slip_row_id', $issueSlipRowId)
                            ->where('purchase_request_id', '!=', $purchaseRequest->id)
                            ->sum('requested_qty');

                        // 3️⃣ Is update request ki qty
                        $currentRequestQty = $item['request_qty'];

                        // 4️⃣ Total PR qty
                        $totalRequestedQty = $alreadyRequestedQty + $currentRequestQty;

                        // 5️⃣ Compare & update pr_status
                        if ($totalRequestedQty >= $issueSlipRow->order_qty) {
                            $issueSlipRow->update([
                                'pr_status' => 1
                            ]);
                        }
                    }
                }
            }


            // 🔹 Optional: deleted rows remove
            PurchaseRequestItem::where('purchase_request_id', $id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();



            DB::commit();

            return redirect()
                ->route('purchase_request.list-view')
                ->with('success', 'Purchase Request updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }




    public function view()
    {
        $purchaseRequests = PurchaseRequest::with('creator')->whereIn('status', ['SUBMITTED', 'HOLD'])->orderBy('id', 'desc')->get();
        $user = Auth::user();
        $users    = User::orderBy('name')->get();
        return view('inventory::purchase_request.approval-view', compact('purchaseRequests', 'users'));
    }


    public function listView(Request $request)
    {
        $query = PurchaseRequest::with('creator')->orderBy('id', 'desc');

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('request_date', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('request_date', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('request_date', [
                $request->from_date,
                $request->to_date
            ]);
        }
        if ($request->filled('pr_no')) {
            $query->where('pr_no', 'like', '%' . $request->pr_no . '%');
        }
        if ($request->filled('priority')) {
            $query->where('priority', 'like', '%' . $request->priority . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $purchaseRequests = $query->paginate(10)->withQueryString();

        $user = Auth::user();
        $users    = User::orderBy('name')->get();


        return view('inventory::purchase_request.list-view', compact('purchaseRequests', 'users'));
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);
            $purchaseRequest->update([
                'status'       => $request->status,
                'approved_by'  => Auth::user()->id,
                'approved_at'  => now(),
            ]);


            $creator = User::find($purchaseRequest->requested_by);

            if ($creator) {
                Notification::create([
                    'notify_id' => $creator->id,
                    "data" => [
                        'module'            => 'request_slip',
                        'purchaseRequest_slip_id'   => $purchaseRequest->id,
                        'purchaseRequest_slip_no'   => $purchaseRequest->pr_no,

                        'status'            =>  $request->status,
                        'approved_by_id'    => auth()->id(),
                        'auth'  => auth()->user()->name ?? 'System',
                        'status_color' => match ($request->status) {
                            'HOLD' => 'text-danger',
                            'REJECTED' => 'text-danger',
                            'APPROVED' => 'text-success',
                            'SUBMITTED' => 'text-info',
                            'DRAFT' => 'text-warning',
                            default => 'text-dark',
                        },

                        'message' => "Your Purchase Request Slip {$purchaseRequest->pr_no} has been .",
                    ],
                ]);
            }

            DB::commit();


            return redirect()
                ->back()
                ->with('success', 'Purchase Request status updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $pr = PurchaseRequest::with('creator')->findorFail($id);
        // dd($pr);
        $items = PurchaseRequestItem::with('inventory')->where('purchase_request_id', $id)->get();


        return view('inventory::purchase_request.show-detail', compact('items', 'pr'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);

            PurchaseRequestItem::where('purchase_request_id', $id)->delete();

            $purchaseRequest->delete();

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
}
