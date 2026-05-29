<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Events\NotificationSent;
use Modules\Inventory\App\Exports\PurchaseOrderExport;
use Modules\Inventory\App\Models\Firm;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\PoStatusLog;
use Modules\Inventory\App\Models\PoTransaction;
use Modules\Inventory\App\Models\PurchaseOrder;
use Modules\Inventory\App\Models\PurchaseOrderItem;
use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\PurchaseRequestItem;
use Modules\Inventory\App\Models\Supplier;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Models\Category;
use Modules\Inventory\App\Models\Placement;
use Modules\Inventory\App\Models\Unit;
use Modules\Inventory\App\Models\Vendor;
use Modules\Inventory\App\Notifications\PurchaseOrderApproved;
use Modules\Inventory\App\Notifications\PurchaseOrderCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with('creator')
            ->whereIn('status', ['APPROVED', 'PARTIALLY_ORDERED'])
            ->whereHas('items', function ($q) {
                $q->whereColumn('ordered_qty', '<', 'requested_qty');
            })->orderBy('id', 'desc');

        // Filters
        if ($request->filled('po_number')) {
            $query->where('pr_no', 'like', '%' . $request->po_number . '%');
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('request_by')) {
            $query->where('requested_by', $request->request_by);
        }

        $Request_items = $query->paginate(10)->withQueryString();

        // ONLY store department users
        $users = User::where('department_id', '5')
            ->orderBy('name')
            ->get();

        return view('inventory::purchase-order.add', compact('Request_items', 'users'));
    }

    public function create(Request $request)
    {
        if ($request->has('req_ids')) {
            $reqIds = explode(',', $request->req_ids);
        } elseif ($request->has('req_id')) {
            $reqIds = [$request->req_id];
        } else {
            $reqIds = [];
        }

        if (!empty($reqIds)) {
            $Request_items = PurchaseRequestItem::with('inventory')
                ->whereIn('purchase_request_id', $reqIds)
                ->whereColumn('ordered_qty', '<', 'requested_qty')
                ->get()
                ->map(function ($item) {

                    $remaining = $item->requested_qty - ($item->ordered_qty ?? 0);

                    // overwrite requested_qty with remaining qty (for display only)
                    $item->requested_qty = max(0, $remaining);

                    return $item;
                });
        } else {
            $Request_items = collect();
        }


        $Suppliers = Supplier::all();

        $Firms = Firm::select('id', 'name')->get();

        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();

        $today = now()->format('Y-m-d');

        // ── Variables needed for Add Inventory modal ─────────────────
        $categories = Category::orderBy('name')->get();
        $placements = Placement::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        // Financial Year
        $currentMonth = date('n');
        $currentYear  = date('Y');

        if ($currentMonth >= 4) {
            $fyStart = $currentYear;
            $fyEnd   = $currentYear + 1;
        } else {
            $fyStart = $currentYear - 1;
            $fyEnd   = $currentYear;
        }

        $financialYear = $fyStart . '-' . $fyEnd;

        $MHELPrefix = 'MHEL';
        $MTPLPrefix = 'MTPL';
        // Get last PO
        $MHELlastPO = PurchaseOrder::where('po_number', 'like', $MHELPrefix . '/PO/%')->orderBy('id', 'desc')->first();
        $MTPLlastPO = PurchaseOrder::where('po_number', 'like', $MTPLPrefix . '/PO/%')->orderBy('id', 'desc')->first();


        if ($MHELlastPO) {
            $lastNumber = explode('/', $MHELlastPO->po_number)[2];
            $nextNumber = (int)$lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        if ($MTPLlastPO) {
            $lastMTPLNumber = explode('/', $MTPLlastPO->po_number)[2];
            $nextMTPLNumber = (int)$lastMTPLNumber + 1;
        } else {
            $nextMTPLNumber = 1;
        }

        // 00001 format
        $poSequence = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $MTPLSequence = str_pad($nextMTPLNumber, 5, '0', STR_PAD_LEFT);

        // Final PO Number
        $nextPrNo = 'MHEL/PO/' . $poSequence . '/' . $financialYear;

        $nextMTPLNo = 'MTPL/PO/' . $MTPLSequence . '/' . $financialYear;

        $oldItems = old('items');

        if ($oldItems && is_array($oldItems)) {
            foreach ($oldItems as $key => $item) {
                if (!empty($item['item_id'])) {
                    $inventory = Inventory::find($item['item_id']);
                    $oldItems[$key]['item_name'] = $inventory?->name . ' ' . $inventory?->model;
                }
            }
        }

        return view('inventory::purchase-order.create', compact('Request_items', 'Firms', 'inventories', 'today', 'nextPrNo', 'Suppliers', 'oldItems', 'categories', 'placements', 'units', 'nextMTPLNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_date' => 'required|date',
            'purchase_order_no' => 'required|string',
            'status' => 'required|in:Draft,Approved,Submitted,Partially Received,Completed,Cancelled',
            'supplier' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'sometimes|nullable|date|after_or_equal:purchase_order_date',
            'firm' => 'required',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventories,id',
            'items.*.requested_qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_type' => 'required|in:IGST,Other',
            'items.*.tax' => 'required|numeric',
        ], [
            'purchase_order_date.required' => 'Purchase order date is required',
            'purchase_order_no.required'   => 'Purchase order number is required',
            'status.required'              => 'Status is required',
            'firm'                         => 'Please Select Firm',
            'supplier.required'            => 'Please select a supplier',
            'items.required'               => 'At least one item is required',
            'items.*.item_id.required' => 'Please select an inventory item',
            'items.*.requested_qty.required' => 'Quantity is required',
            'items.*.requested_qty.min' => 'Quantity must be at least 1',
            'items.*.price.required' => 'Price is required',
            'items.*tax.required' => 'please select tax',
        ]);


        DB::beginTransaction();

        try {
            $status = $request->status;
            $finalTotal = (float) $request->final_total;

            if ($status === 'Submitted') {
                if ($finalTotal > 50000) {
                    $status = 'Approved';
                }
            }


            $purchaseOrder = PurchaseOrder::create([
                'po_date' => $request->purchase_order_date,
                'po_number'   => $request->purchase_order_no,
                'status'              =>  $status,
                'supplier_id'           => $request->supplier,
                'firm'                  => $request->firm,
                'expected_delivery' => $request->expected_delivery_date,
                'remarks' => $request->quotation_number,
                'total_qty'   => $request->total_qty,
                'subtotal'   => $request->total,
                'subtotal_discount_amount' => $request->total_discount_amount,
                'tax_amount'   => $request->total_tax,
                'total_amount' => $request->final_total,
                'loading_cutting_charges' => $request->final_loading_cutting_charges ?? 0,
                'freight_charges' => $request->final_freight_charges ?? 0,
                'final_discount' => $request->final_discount_amount ?? 0,
                'remaining_amount' => $request->remaining_amount ?? 0,
                'advance_amount' => $request->advance_amount ?? 0,
                'balance_amount' => $request->balance_amount ?? 0,
                'terms_and_conditions' => $request->terms_and_conditions,
                'delivery_status' => 'Pending',
                'created_by' => auth()->id(),
            ]);


            $affectedPrIds = [];
            foreach ($request->items as $item) {
                $rowId = isset($item['row_id']) ? $item['row_id'] : null;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'inventory_id' => $item['item_id'],
                    'hsn'         => $item['hsn'],
                    'pr_item_id' => $rowId,
                    'ordered_qty' => $item['requested_qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'discount_amount' => $item['discount_amount'],
                    'line_total' => $item['taxable_total'], // blade file name regarding save don't confuse
                    'tax_percent' => $item['tax'],
                    'tax_type' => $item['tax_type'],
                    'tax_amount' => $item['tax_amount'],
                    'taxable_total' => $item['total_amount'],  // blade file name regarding save don't confuse
                    'item_not'   => $item['note'] ?? null,
                ]);
                if ($rowId && ($purchaseOrder->status !== 'Draft')) {
                    $purchaseRequestItem = PurchaseRequestItem::find($item['row_id']);
                    if ($purchaseRequestItem) {
                        $requested      = (float) $purchaseRequestItem->requested_qty;
                        $alreadyOrdered = (float) $purchaseRequestItem->ordered_qty;
                        $incomingQty    = (float) $item['requested_qty'];

                        $maxRemaining = $requested - $alreadyOrdered;

                        $finalQtyToAdd = min($incomingQty, $maxRemaining);

                        if ($finalQtyToAdd < 0) {
                            $finalQtyToAdd = 0;
                        }

                        $purchaseRequestItem->increment('ordered_qty', $finalQtyToAdd);

                        $requested = (float) $purchaseRequestItem->requested_qty;
                        $ordered   = (float) $purchaseRequestItem->ordered_qty;
                        if ($ordered == $requested) {
                            $purchaseRequestItem->status = "ORDERED";
                        } elseif ($ordered < $requested) {
                            $purchaseRequestItem->status = "PARTIALLY_ORDERED";
                        }
                        $purchaseRequestItem->save();

                        $affectedPrIds[] = $purchaseRequestItem->purchase_request_id;
                    }
                }
            }

            $affectedPrIds = array_unique($affectedPrIds);

            foreach ($affectedPrIds as $prId) {
                $this->updatePurchaseRequestStatus($prId);
            }
            if ($request->advance_amount > 0) {
                PoTransaction::create([
                    'po_id' => $purchaseOrder->id,
                    'pay_amount' => $request->advance_amount,
                ]);
            }

            PoStatusLog::create([
                'purchase_order_id' => $purchaseOrder->id,
                'status' =>  $status,
                'changed_by' => auth()->id(),
            ]);


            $currentUser = Auth::user();

            $authorityId = $currentUser->authority_id;

            if ($authorityId) {
                $noti = Notification::create([
                    'role_id' => $authorityId,
                    "data" => [
                        'module'       => 'purchase_order',
                        'purchase_Request_id'        => $purchaseOrder->id,
                        'purchase_request_number'      => $purchaseOrder->po_number,
                        'auth'   => $purchaseOrder->creator->name ?? 'Unknown',
                        'message'      => "New Purchase Order generated: " . ($purchaseOrder->creator->name ?? 'N/A'),
                        'url'          => route('purchase-order.approval'),
                    ],
                ]);

                try {
                    broadcast(new NotificationSent($noti, $authorityId));
                } catch (Throwable $e) {
                }
            }

            DB::commit();

            return redirect()
                ->route('purchase-order.view')
                ->with('success', 'Purchase Order Created Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function view(Request $request)
    {
        $request->validate(
            [
                'from_date' => 'nullable|date|',
                'to_date'   => 'nullable|date|after_or_equal:from_date',
                'ex_from_date' => 'nullable|date|',
                'ex_to_date'   => 'nullable|date|after_or_equal:ex_from_date',

            ],
            [
                'from_date.before_or_equal' => 'From date must be earlier than To date',
                'to_date.after_or_equal'    => 'To date must be after From date',
                'ex_from_date.before_or_equal' => 'From date must be earlier than To date',
                'ex_to_date.after_or_equal'    => 'To date must be after From date',
            ]
        );

        $query = PurchaseOrder::with([
            'supplier',
            'firmData',
            'paymentRecord' => function ($q) {
                $q->orderBy('id', 'desc')->limit(10);
            },
            'grns.items.inventory'
        ]);



        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('po_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('po_date', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('po_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        if ($request->filled('ex_from_date') && !$request->filled('ex_to_date')) {
            $query->whereDate('expected_delivery', '>=', $request->ex_from_date);
        }

        if ($request->filled('ex_to_date') && !$request->filled('ex_from_date')) {
            $query->whereDate('expected_delivery', '<=', $request->ex_to_date);
        }
        if ($request->filled('ex_from_date') && $request->filled('ex_to_date')) {
            $query->whereBetween('expected_delivery', [
                $request->from_date,
                $request->to_date
            ]);
        }


        if ($request->filled('po_no')) {
            $query->where('po_number', 'like', '%' . $request->po_no . '%');
        }


        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('firm_id')) {
            $query->where('firm', $request->firm_id);
        }

        // Calculate aggregate totals for the filtered results
        $summaryTotal = $query->sum('total_amount');
        $summaryAdvance = $query->sum('advance_amount');
        $summaryDue = $query->sum('balance_amount');

        $PurchaseOrders = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $selectedSupplierName = null;

        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $selectedSupplierName = $supplier?->supplier_name;
        }

        $firms = Firm::orderBy('id')->get();

        return view('inventory::purchase-order.view', compact('PurchaseOrders', 'selectedSupplierName', 'firms', 'summaryTotal', 'summaryAdvance', 'summaryDue'));
    }

    public function edit($id)
    {
        $PurchaseOrder = PurchaseOrder::with('items.prItem')->findOrFail($id);

        foreach ($PurchaseOrder->items as $item) {

            $prItem = $item->prItem;

            if ($prItem) {
                $item->max_qty =
                    $prItem->requested_qty
                    - ($prItem->ordered_qty - $item->ordered_qty);
            }
        }
        $today = now()->format('Y-m-d');
        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();

        $suppliers = Supplier::all();
        $Firms = Firm::select('id', 'name')->get();

        // ── Variables needed for Add Inventory modal ─────────────────
        $categories = Category::orderBy('name')->get();
        $placements = Placement::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        $oldItems = old('items');

        if ($oldItems && is_array($oldItems)) {
            foreach ($oldItems as $key => $item) {
                if (!empty($item['item_id'])) {
                    $inventory = Inventory::find($item['item_id']);
                    $oldItems[$key]['item_name'] = $inventory?->name . ' ' . $inventory?->model;
                }
            }
        }

        // Financial Year
        $currentMonth = date('n');
        $currentYear  = date('Y');

        if ($currentMonth >= 4) {
            $fyStart = $currentYear;
            $fyEnd   = $currentYear + 1;
        } else {
            $fyStart = $currentYear - 1;
            $fyEnd   = $currentYear;
        }

        $financialYear = $fyStart . '-' . $fyEnd;
        $MHELPrefix = 'MHEL';
        $MTPLPrefix = 'MTPL';

        $MHELlastPO = PurchaseOrder::where('po_number', 'like', $MHELPrefix . '/PO/%')->orderBy('id', 'desc')->first();
        $MTPLlastPO = PurchaseOrder::where('po_number', 'like', $MTPLPrefix . '/PO/%')->orderBy('id', 'desc')->first();


        $currentPo = $PurchaseOrder->po_number;


        $currentPrefix = explode('/', $currentPo)[0];

        if ($currentPrefix === $MHELPrefix) {
            $MHELNextPO = $currentPo;
        } else {
            if ($MHELlastPO) {
                $lastNumber = explode('/', $MHELlastPO->po_number)[2];
                $nextNumber = (int)$lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $MHELSequence = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            $MHELNextPO = 'MHEL/PO/' .  $MHELSequence . '/' . $financialYear;
        }

        if ($currentPrefix === $MTPLPrefix) {
            $MTPLNextPO = $currentPo;
        } else {
            if ($MTPLlastPO) {
                $lastNumber = explode('/', $MTPLlastPO->po_number)[2];
                $nextNumber = (int)$lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $MTPLSequence = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $MTPLNextPO = 'MTPL/PO/' . $MTPLSequence . '/' . $financialYear;
        }


        return view('inventory::purchase-order.edit', compact('today', 'PurchaseOrder', 'Firms', 'suppliers', 'inventories', 'oldItems', 'categories', 'placements', 'units', 'MHELNextPO', 'MTPLNextPO'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'purchase_order_date' => 'required|date',
            'purchase_order_no' => 'required|string',
            'status' => 'required|in:Draft,Approved,Submitted,Partially Received,Completed,Cancelled',
            'supplier' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'sometimes|nullable|date|after_or_equal:purchase_order_date',
            'firm' => 'required',


            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventories,id',
            'items.*.requested_qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_type' => 'required|in:IGST,Other',
            'items.*.tax' => 'required|numeric|in:5,18,28',
        ], [
            'purchase_order_date.required' => 'Purchase order date is required',
            'purchase_order_no.required'   => 'Purchase order number is required',
            'status.required'              => 'Status is required',
            'firm'                         => 'Please Select Firm',
            'supplier.required'            => 'Please select a supplier',
            'items.required'               => 'At least one item is required',
            'items.*.item_id.required' => 'Please select an inventory item',
            'items.*.requested_qty.required' => 'Quantity is required',
            'items.*.requested_qty.min' => 'Quantity must be at least 1',
            'items.*.price.required' => 'Price is required',
            'items.*tax.required' => 'please select tax',
        ]);

        DB::beginTransaction();

        try {

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            $status = $request->status;
            $finalTotal = (float) $request->final_total;

            if ($status === 'Submitted') {
                if ($finalTotal > 50000) {
                    $status = 'Approved';
                }
            }

            /* ======================
           UPDATE PO HEADER
        ====================== */
            $dbadvance_amount = ($purchaseOrder->advance_amount ?? 0) + ($request->advance_amount ?? 0);


            $purchaseOrder->update([
                'status' => $status,
                'supplier_id' => $request->supplier,
                'po_number'   => $request->purchase_order_no,
                'expected_delivery' => $request->expected_delivery_date,
                'remarks' => $request->quotation_number,
                'firm'                  => $request->firm,
                'total_qty' => $request->total_qty,
                'subtotal' => $request->total,
                'subtotal_discount_amount' => $request->total_discount_amount,
                'tax_amount' => $request->total_tax,
                'total_amount' => $request->final_total,
                'loading_cutting_charges' => $request->final_loading_cutting_charges,
                'freight_charges' => $request->final_freight_charges,
                'final_discount' => $request->final_discount_amount,
                'advance_amount' => $dbadvance_amount,
                'remaining_amount' => $request->remaining_amount,
                'balance_amount' => $request->balance_amount,
                'terms_and_conditions' => $request->terms_and_conditions,
                'po_date' => $request->purchase_order_date,
            ]);

            /* ======================
           HANDLE ITEMS
        ====================== */
            $existingItemIds = [];
            $affectedPrIds = [];

            foreach ($request->items as $item) {

                // 🔹 UPDATE EXISTING ITEM
                if (!empty($item['row_id'])) {

                    $poItem = PurchaseOrderItem::find($item['row_id']);


                    if ($poItem) {
                        $oldQty = $poItem->ordered_qty;
                        $newQty = (float) $item['requested_qty'];
                        $diff = $newQty - $oldQty;

                        $poItem->update([
                            'inventory_id' => $item['item_id'],
                            'hsn'         => $item['hsn'],
                            'ordered_qty' => $item['requested_qty'],
                            'unit_price' => $item['price'],
                            'discount' => $item['discount'],
                            'discount_amount' => $item['discount_amount'],
                            'line_total' => $item['taxable_total'],
                            'tax_percent' => $item['tax'],
                            'tax_type' => $item['tax_type'],
                            'tax_amount' => $item['tax_amount'],
                            'taxable_total' => $item['total_amount'],
                            'item_not'   => $item['note'] ?? null,
                        ]);

                        $existingItemIds[] = $poItem->id;

                        $purchaseRequestItem = PurchaseRequestItem::find($poItem->pr_item_id);

                        if ($purchaseRequestItem && ($purchaseOrder->status !== 'Draft')) {
                            $requested  = (float) $purchaseRequestItem->requested_qty;
                            $alreadyOrdered = (float) $purchaseRequestItem->ordered_qty;
                            $incomingQty    = (float) $item['requested_qty'];

                            $maxRemaining = $requested -  $alreadyOrdered;

                            $finalQtyToAdd = min($incomingQty, $maxRemaining);


                            if ($finalQtyToAdd < 0) {
                                $finalQtyToAdd = 0;
                            }

                            $purchaseRequestItem->increment('ordered_qty', $finalQtyToAdd);

                            $requested = (float) $purchaseRequestItem->requested_qty;
                            $ordered   = (float) $purchaseRequestItem->ordered_qty;
                            if ($ordered == 0) {
                                $purchaseRequestItem->status = "APPROVED";
                            } elseif ($ordered < $requested) {
                                $purchaseRequestItem->status = "PARTIALLY_ORDERED";
                            } else {
                                $purchaseRequestItem->status = "ORDERED";
                            }
                            $purchaseRequestItem->save();

                            $affectedPrIds[] = $purchaseRequestItem->purchase_request_id;
                        }
                    }
                }
                // 🔹 CREATE NEW ITEM
                else {

                    $newItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'inventory_id' => $item['item_id'],
                        'hsn'         => $item['hsn'],
                        'ordered_qty' => $item['requested_qty'],
                        'unit_price' => $item['price'],
                        'discount' => $item['discount'],
                        'discount_amount' => $item['discount_amount'],
                        'line_total' => $item['taxable_total'],
                        'tax_percent' => $item['tax'],
                        'tax_type' => $item['tax_type'],
                        'tax_amount' => $item['tax_amount'],
                        'taxable_total' => $item['total_amount'],
                        'item_not'   => $item['note'] ?? null,
                    ]);

                    $existingItemIds[] = $newItem->id;
                }
            }

            $affectedPrIds = array_unique($affectedPrIds);

            foreach ($affectedPrIds as $prId) {
                $this->updatePurchaseRequestStatus($prId);
            }

            /* ======================
           DELETE REMOVED ITEMS
        ====================== */
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
            if ($request->advance_amount > 0) {
                PoTransaction::create([
                    'po_id' => $purchaseOrder->id,
                    'pay_amount' => $request->advance_amount,
                ]);
            }
            /* ======================
           STATUS LOG
        ====================== */
            PoStatusLog::create([
                'purchase_order_id' => $purchaseOrder->id,
                'status' => $request->status,
                'changed_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchase-order.view')
                ->with('success', 'Purchase Order Updated Successfully');
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $po = PurchaseOrder::with('creator', 'items.inventory', 'supplier', 'firmData')->findorFail($id);
        return view('inventory::purchase-order.show', compact('po'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            PurchaseOrderItem::where('purchase_order_id', $id)->delete();

            $purchaseOrder->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Purchase Order deleted permanently');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            $purchaseOrder->update([
                'status'       => $request->status,
                'remarks' => $request->remarks,
                'approved_by'  => Auth::user()->id,
            ]);

            DB::commit();

            $creator = User::find($purchaseOrder->created_by);

            if ($creator) {
                Notification::create([
                    'notify_id' => $creator->id,
                    "data" => [
                        'module'            => 'purchase_order',
                        'purchaseOrder_slip_id'   => $purchaseOrder->id,
                        'purchaseOrder_slip_no'   => $purchaseOrder->po_number,

                        'status'            => $request->status,
                        'created_by'    =>          auth()->id(),
                        'auth'  => auth()->user()->name ?? 'System',
                        'status_color' => match ($request->status) {
                            'Completed' => 'text-info',
                            'Cancelled' => ' text-danger',
                            'Partially Received' => 'text-warning',
                            'Approved' => 'text-success',
                            'Submitted' => 'text-info',
                            'Draft' => 'text-warning',
                            default => 'text-dark',
                        },

                        'message' => "Your Purchase Request Slip {$purchaseOrder->po_number} has been .",
                    ],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Purchase Order status updated successfully');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approvalView(Request $request)
    {
        $request->validate(
            [
                'from_date' => 'nullable|date|',
                'to_date'   => 'nullable|date|after_or_equal:from_date',
                'ex_from_date' => 'nullable|date|',
                'ex_to_date'   => 'nullable|date|after_or_equal:ex_from_date',

            ],
            [
                'from_date.before_or_equal' => 'From date must be earlier than To date',
                'to_date.after_or_equal'    => 'To date must be after From date',
                'ex_from_date.before_or_equal' => 'From date must be earlier than To date',
                'ex_to_date.after_or_equal'    => 'To date must be after From date',
            ]
        );

        $query = PurchaseOrder::with('creator', 'supplier')
            ->where('status', 'Submitted');

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('po_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('po_date', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('po_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        if ($request->filled('ex_from_date') && !$request->filled('ex_to_date')) {
            $query->whereDate('expected_delivery', '>=', $request->ex_from_date);
        }

        if ($request->filled('ex_to_date') && !$request->filled('ex_from_date')) {
            $query->whereDate('expected_delivery', '<=', $request->ex_to_date);
        }
        if ($request->filled('ex_from_date') && $request->filled('ex_to_date')) {
            $query->whereBetween('expected_delivery', [
                $request->from_date,
                $request->to_date
            ]);
        }

        if ($request->filled('po_no')) {
            $query->where('po_number', 'like', '%' . $request->po_no . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $PurchaseOrders = $query
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        //  filtter dropdown data
        $suppliers = Supplier::orderBy('supplier_name')->get();

        $user = Auth::user();
        $users    = User::orderBy('name')->get();

        $selectedSupplierName = null;


        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $selectedSupplierName = $supplier?->supplier_name;
        }

        return view('inventory::purchase-order.approval', compact('PurchaseOrders', 'users', 'suppliers', 'selectedSupplierName'));
    }

    public function updateAdvance(Request $request, $id)
    {
        $request->validate([
            'pay_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $po = PurchaseOrder::findOrFail($id);

            $oldAdvance = $po->advance_amount ?? 0;
            $oldBalance = $po->balance_amount ?? 0;
            $pay_amounts = (float) $request->pay_amount;


            if ($pay_amounts > $oldBalance) {
                return back()->with('error', 'New advance amount cannot be greater than balance amount.');
            }

            $updatedAdvance = $oldAdvance + $pay_amounts;
            $updatedBalance = $po->balance_amount - $pay_amounts;

            $po->advance_amount = $updatedAdvance;
            $po->balance_amount = $updatedBalance;
            $po->save();

            PoTransaction::create([
                'po_id' => $id,
                'pay_amount' => $pay_amounts,
            ]);

            DB::commit();

            return back()->with('success', 'Advance amount updated successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteAdvance($id)
    {
        DB::beginTransaction();

        try {

            // transaction find
            $transaction = PoTransaction::findOrFail($id);

            // PO find
            $po = PurchaseOrder::findOrFail($transaction->po_id);

            $payAmount = $transaction->pay_amount;

            // reverse calculation
            $po->advance_amount = $po->advance_amount - $payAmount;

            $po->balance_amount = $po->balance_amount + $payAmount;

            $po->save();

            // delete transaction
            $transaction->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    private function updatePurchaseRequestStatus($purchaseRequestId)
    {
        $items = PurchaseRequestItem::where('purchase_request_id', $purchaseRequestId)->get();

        $totalRequested = $items->sum('requested_qty');
        $totalOrdered   = $items->sum('ordered_qty');

        if ($totalOrdered == 0) {
            $status = 'APPROVED'; // ya jo default ho
        } elseif ($totalOrdered < $totalRequested) {
            $status = 'PARTIALLY_ORDERED';
        } else {
            $status = 'ORDERED';
        }

        PurchaseRequest::where('id', $purchaseRequestId)->update([
            'status' => $status
        ]);
    }

    public function updateDeliveryStatus(Request $request, $id)
    {
        $request->validate([
            'delivery_status' => 'required|string'
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $po->delivery_status = $request->delivery_status;
        if ($request->expected_date) {
            $po->expected_delivery = $request->expected_date;
        }
        $po->save();

        return back()->with('success', 'Delivery status updated successfully.');
    }

    public function exportPO(Request $request)
    {
        return Excel::download(new PurchaseOrderExport($request), 'purchase_orders.xlsx');
    }
}
