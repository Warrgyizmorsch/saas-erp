<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Grn;
use Modules\Inventory\App\Models\GrnRow; // ya GrnItem (jo aap use kar rahe)
use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\PurchaseOrder;
use Modules\Inventory\App\Models\PurchaseOrderItem;
use Modules\Inventory\App\Models\StockTransaction;
use Modules\Inventory\App\Models\Supplier;
use Modules\Shared\App\Models\User;
use Modules\Inventory\App\Notifications\GrnCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrnController extends Controller
{
    public function index(Request $request)
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
        $query = PurchaseOrder::with(['supplier', 'creator'])
            ->whereIn('status', ['Approved', 'Partially Received']);

        if ($request->filled('po_code')) {
            $query->where('po_number', 'like', '%' . $request->po_code . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $po = $query->latest()->paginate(10)->withQueryString();

        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('inventory::grn.index', compact('po', 'suppliers'));
    }

    public function create(Request $request)
    {
        if (!$request->filled('po_id')) {
            return redirect()->route('grn.index')->with('error', 'Please select a Purchase Order to create a GRN.');
        }

        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.inventory'])
            ->findOrFail($request->po_id);

        $nextId = (Grn::max('id') ?? 0) + 1;
        $grnNo  = 'GRN-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $today  = now()->format('Y-m-d');

        return view('inventory::grn.create', compact('purchaseOrder', 'grnNo', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_id' => ['required', 'exists:purchase_orders,id'],
            'grn_no' => ['required', 'string', 'max:50', 'unique:grns,grn_number'],
            'grn_date' => ['required', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.po_item_id' => ['required', 'exists:purchase_order_items,id'],
            'items.*.inventory_id' => ['required', 'exists:inventories,id'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.rejected_qty' => ['required', 'numeric', 'min:0'],
            'items.*.accepted_qty' => ['required', 'numeric', 'min:0'],
            'items.*.remark' => ['nullable', 'string', 'max:250'],
        ]);

        DB::transaction(function () use ($request) {

            // Lock PO to avoid parallel GRN issue
            $po = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($request->po_id);

            // Create GRN
            $grn = Grn::create([
                'grn_number' => $request->grn_no,
                'purchase_order_id' => $po->id,
                'grn_date' => $request->grn_date,
                'invoice_no' => $request->invoice_no,
                'remarks' => $request->remarks,

            ]);

            // Loop items
            foreach ($request->items as $row) {

                $poItem = PurchaseOrderItem::lockForUpdate()->findOrFail($row['po_item_id']);

                // Ensure item belongs to this PO
                if ((int)$poItem->purchase_order_id !== (int)$po->id) {
                    throw new \Exception("PO Item mismatch with Purchase Order.");
                }

                $orderedQty = (float)$poItem->ordered_qty;
                $receivedBefore = (float)($poItem->received_qty ?? 0);
                $remaining = max($orderedQty - $receivedBefore, 0);

                $receivedNow = (float)$row['received_qty'];
                $rejectedNow = (float)$row['rejected_qty'];
                $acceptedNow = (float)$row['accepted_qty'];

                // ✅ Rules
                if ($receivedNow > $remaining) {
                    throw new \Exception("Received qty cannot exceed remaining qty for item ID: {$poItem->id}");
                }

                if (round($acceptedNow + $rejectedNow, 2) !== round($receivedNow, 2)) {
                    throw new \Exception("Accepted + Rejected must equal Received for inventory: {$row['inventory_id']}");
                }

                if ($receivedNow <= 0) {
                    // agar 0 ho to skip row create nahi karna (optional)
                    continue;
                }

                // Save GRN Row/Item
                GrnRow::create([
                    'grn_id' => $grn->id,
                    'inventory_id' => (int)$row['inventory_id'],
                    'received_qty' => $receivedNow,
                    'accepted_qty' => $acceptedNow,
                    'rejected_qty' => $rejectedNow,
                    'description' => $row['remark'] ?? null, // agar column hai
                ]);

                // Update PO item cumulative received_qty
                $poItem->received_qty =  $receivedBefore + $acceptedNow;
                $poItem->save();

                // ✅ Stock Transaction (only accepted stock IN)
                if ($acceptedNow > 0) {
                    StockTransaction::create([
                        'project_id' => null,
                        'machine_id' => null,
                        'inventory_id' => (int)$row['inventory_id'],
                        'txn_date' => $request->grn_date,
                        'txn_type' => 'In',
                        'quantity' => $acceptedNow,
                        'ref_type' => 'GRN',
                        'ref_no' => $grn->grn_number,
                        'remarks' => trim(
                            ($request->remarks ?? '') .
                                (!empty($row['remark']) ? ' | Item: ' . $row['remark'] : '')
                        ),
                        'issued_to' => null,
                        'issue_by' => auth()->id(),
                        'requision_id' => null,
                        'issue_slip_id' => null,
                        'supplier_id' => $po->supplier_id,
                    ]);
                }
            }

            $po->load('items');

            $allCompleted = true;
            $anyReceived  = false;

            foreach ($po->items as $it) {
                $ord = (float)$it->ordered_qty;
                $rec = (float)($it->received_qty ?? 0);

                if ($rec > 0) $anyReceived = true;

                if ($rec < $ord) {
                    $allCompleted = false;
                }
            }

            if (!$anyReceived) {
                $po->status = 'Approved';
            } elseif ($allCompleted) {
                $po->status = 'Completed';
                $po->completed_at = now();
            } else {
                $po->status = 'Partially Received';
            }

            $po->save();


            $created_by = $po->created_by;

            if ($created_by) {
                Notification::create([
                    'notify_id' => $created_by,
                    "data" => [
                        'module'       => 'grn',
                        'grn_id'        => $grn->id,
                        'grn_number'      => $grn->grn_number,
                        'po_id'      => $po->id,
                        'po_number'  => $po->po_number,
                        'status'  => $po->status,
                        'auth'   => auth()->user()->name ?? 'Unknown',
                        'status_color' => match ($po->status) {
                            'Completed' => 'text-info',
                            'Rejected' => ' text-danger',
                            'Partially Received' => 'text-warning',
                            default => 'text-dark',
                        },
                        'message'      => "GRN created for {$po->po_number}.",
                    ],
                ]);
            }
        });

        return redirect()->route('grn.index')->with('success', 'GRN saved & PO status updated!');
    }

    public function grnList(Request $request)
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

        $query = Grn::with(['purchaseOrder.supplier'])
            ->withSum('items', 'accepted_qty')
            ->withSum('items', 'rejected_qty');


        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date,
                $request->to_date
            ]);
        }

        if ($request->filled('po_code')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('po_number', 'LIKE', '%' . trim($request->po_code) . '%');
            });
        }

        if ($request->filled('grn_no')) {
            $query->where('grn_number', 'LIKE', '%' . trim($request->grn_no) . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->whereHas('purchaseOrder', function ($q) use ($request) {
                $q->where('supplier_id', $request->supplier_id);
            });
        }

        $grns = $query->latest()->paginate(10);


          $selectedSupplierName = null;


        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $selectedSupplierName = $supplier?->supplier_name;
        }


        return view('inventory::grn.grn-list', compact('grns','selectedSupplierName'));
    }

    public function show($id)
    {
        $grn = Grn::with([
            'purchaseOrder.supplier',
            'purchaseOrder.items.inventory',
            'items.inventory', // items = GrnRow/GrnItem relation
        ])
            ->findOrFail($id);

        // Totals
        $totals = [
            'received' => (float) $grn->items->sum('received_qty'),
            'rejected' => (float) $grn->items->sum('rejected_qty'),
            'accepted' => (float) $grn->items->sum('accepted_qty'),
        ];

        return view('inventory::grn.show', compact('grn', 'totals'));
    }

    public function updateStatus(Request $request, $id)
    {
        PurchaseOrder::where('id', $id)
            ->update([
                'status' => $request->status,
                'completed_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Status updated successfully');
    }
}
