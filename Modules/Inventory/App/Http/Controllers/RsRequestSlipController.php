<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Consumption;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\Issue;
use Modules\Inventory\App\Models\IssueSlipRow;
use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\ProjectProduct;
use Modules\Inventory\App\Models\ProductItem;
use Modules\Inventory\App\Models\RequestSlip;
use Modules\Inventory\App\Models\RequestSlipHistory;
use Modules\Inventory\App\Models\RequestSlipItem;
use Modules\Inventory\App\Models\Project;
use Modules\Shared\App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\App\Models\Department;
use Modules\Inventory\App\Models\Notification;
use Modules\Inventory\App\Models\RequisitionSlipRow;
use Modules\Inventory\App\Models\RequisitionSlipRowPiece;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;




class RsRequestSlipController extends Controller
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

        $user = Auth::user();

        // Dropdowns
        $users     = User::orderBy('name')->get();
        $projects  = Project::orderBy('name')->get();
        $machines  = Product::orderBy('name')->get(); // ✅ ADD

        // Base Query
        $query = RequestSlip::query()->with([
            'creator',
            'rows.inventory',
            'rows.machine',
            'rows.pieces' => function ($q) {
                $q->where('is_completed', 0)
                    ->with('inventory'); 
            },
            'issue',
            'issue.rows.inventory',
            'histories.user',
            'project',

        ]);

        /**
         * ✅ VISIBILITY (UPDATED)
         * Ab sab roles ko khud ki bhi + baaki saari RS dikhni chahiye
         * (No role-wise filtering)
         */

        // ✅ Keep this same (as per your code): only entries where store_rs = 1
        $query->where('store_rs', 1)->where('employee_id',  auth()->user()->id);

        /**
         * FILTERS (same)
         */

        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_on', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('created_on', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_on', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('rs_code')) {
            $cleanCode = preg_replace('/\D+/', '', $request->input('rs_code'));
            if ($cleanCode !== '') {
                $query->where('rs_id', (int) $cleanCode);
            }
        }


        if ($request->filled('project')) {
            $query->where('project_id', $request->input('project'));
        }

        if ($request->filled('machine')) {

            $query->whereHas('rows', function ($q) use ($request) {

                $q->where('machine_id', $request->machine);
            });
        }

        $query->orderByDesc('id');

        $requestSlips = $query->paginate(10)->withQueryString();


        /**
         * ✅ Consumption sums + history + last selected machine/project maps
         */
        $rsIds = $requestSlips->getCollection()->pluck('id')->values()->all();

        $consumedQtyMapByRs = [];
        $consumedHMapByRs   = [];
        $consumedWMapByRs   = [];
        $consumptionHistoryByRs = [];

        $lastMachineMapByRs = []; // [rs_id][rs_row_id] => machine_id
        $lastProjectMapByRs = []; // [rs_id][rs_row_id] => project_id

        if (!empty($rsIds)) {

            $qtyRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($qtyRows as $row) {
                $consumedQtyMapByRs[$row->request_slips_id][$row->rs_row_id] = (float) $row->total_qty;
            }

            $hRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(height) as total_h'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($hRows as $row) {
                $consumedHMapByRs[$row->request_slips_id][$row->rs_row_id] = (float) $row->total_h;
            }

            $wRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(width) as total_w'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($wRows as $row) {
                $consumedWMapByRs[$row->request_slips_id][$row->rs_row_id] = (float) $row->total_w;
            }

            // ✅ history (latest first)
            $hist = Consumption::whereIn('request_slips_id', $rsIds)
             ->with(['inventory', 'user'])
                ->orderByDesc('id')
                ->get();

              
            foreach ($hist as $c) {
                $consumptionHistoryByRs[$c->request_slips_id][] = $c;

                // ✅ last selected machine/project (latest consumption wins)
                if (!isset($lastMachineMapByRs[$c->request_slips_id][$c->rs_row_id])) {
                    $lastMachineMapByRs[$c->request_slips_id][$c->rs_row_id] = $c->machine_id ?? null;
                }
                if (!isset($lastProjectMapByRs[$c->request_slips_id][$c->rs_row_id])) {
                    $lastProjectMapByRs[$c->request_slips_id][$c->rs_row_id] = $c->project_id ?? null;
                }
            }
        }

        // Attach to each RS
        $requestSlips->getCollection()->transform(function ($rs) use (
            $consumedQtyMapByRs,
            $consumedHMapByRs,
            $consumedWMapByRs,
            $consumptionHistoryByRs,
            $lastMachineMapByRs,
            $lastProjectMapByRs
        ) {
            $rs->consumedQtyMap = $consumedQtyMapByRs[$rs->id] ?? [];
            $rs->consumedHMap   = $consumedHMapByRs[$rs->id] ?? [];
            $rs->consumedWMap   = $consumedWMapByRs[$rs->id] ?? [];
            $rs->consHistory    = $consumptionHistoryByRs[$rs->id] ?? [];

            $rs->lastMachineMap = $lastMachineMapByRs[$rs->id] ?? [];
            $rs->lastProjectMap = $lastProjectMapByRs[$rs->id] ?? [];

            return $rs;
        });

        $isFilterActive = $request->filled('status')
            || $request->filled('rs_code')
            || $request->filled('user')
            || $request->filled('project');


           
             $modelprojects = Project::orderBy('name')->get();

             // Logged-in user department
            

           // Generate next Requisition Slip No (safe way using rs_id)
            $last = RequestSlip::orderBy('rs_id', 'desc')->first();
            $nextRsId = $last ? ($last->rs_id + 1) : 1;

            $nextSlipNo = 'RS-' . str_pad($nextRsId, 5, '0', STR_PAD_LEFT);



        return view('inventory::request_slip.index', compact(
            'requestSlips',
            'users',
            'projects',
            'machines',
            'isFilterActive',
            'modelprojects',  
             'nextSlipNo' 
        ));
    }




    public function create()
    {
        $projects = Project::orderBy('name')->get();

        // Logged-in user department
        $departmentId = Auth::user()->department_id;

        // Generate next Requisition Slip No (safe way using rs_id)
        $last = RequestSlip::orderBy('rs_id', 'desc')->first();
        $nextRsId = $last ? ($last->rs_id + 1) : 1;

        $nextSlipNo = 'RS-' . str_pad($nextRsId, 5, '0', STR_PAD_LEFT);

        return view('inventory::request_slip.create', [
            'projects'      => $projects,
            'products'      => [],          // agar view me use hota hai to keep
            'inventory'     => collect(),   // view crash na ho isliye empty
            'department_id' => $departmentId,
            'employee_id'   => Auth::id(),
            'nextSlipNo'    => $nextSlipNo,
            'requestSlip'   => null,
            'isEdit'        => false,
        ]);
    }

    public function edit($id)
    {
        $projects = Project::orderBy('name')->get();
        $requestSlip = RequestSlip::with(['rows.inventory', 'rows.machine'])->findOrFail($id);

        $nextSlipNo = $requestSlip->requisition_slip_no;

        return view('inventory::request_slip.create', [
            'projects'      => $projects,
            'products'      => [],          // matches create() variable signature
            'inventory'     => collect(),   // matches create() variable signature
            'department_id' => Auth::user()->department_id,
            'employee_id'   => Auth::id(),
            'nextSlipNo'    => $nextSlipNo,
            'requestSlip'   => $requestSlip,
            'isEdit'        => true,
        ]);
    }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id'             => 'required|exists:projects,id',
            'items.machine_id.*'     => 'required|exists:products,id',
            'items.inventory_id.*'   => 'required|exists:inventories,id',
            'items.quantity.*'       => 'required|numeric|min:1',
            'items.need_qty.*'       => 'required|numeric|min:0',
            'items.description.*'    => 'nullable|string|max:255',
        ], [
            'project_id.required'            => 'Project selection is required.',
            'items.machine_id.*.required'    => 'Please select a machine.',
            'items.inventory_id.*.required'  => 'Please select an inventory.',
            'items.quantity.*.required'      => 'Quantity is required.',
            'items.quantity.*.min'           => 'Quantity must be at least 1.',
            'items.need_qty.*.required'      => 'Need qty missing.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_add_form', true);
        }

        try {
            $payload = $this->validateAndNormalize($request);

            DB::beginTransaction();

            // ✅ main slip exited flag (any row exited => 1)
            $slipIsExited = 0;

            $slip = RequestSlip::create([
                'requisition_slip_no' => $payload['requisition_slip_no'],
                'transaction_date'    => $payload['transaction_date'],
                'employee_id'         => $payload['employee_id'],
                'project_id'          => $payload['project_id'],
                'comment'             => $payload['comment'],
                'created_by'          => Auth::id(),
                'total_qty'           => 0,
                'store_rs'            => 1,
                'edited_by'           => auth()->user()->id,
                'status'              => auth()->user()->role_id == 6 ? 'Approved HOD' : 'Pending',
            ]);

            if (!$slip) {
                throw new \Exception('Request Slip creation failed.');
            }

            $totalQty = 0;

            foreach ($payload['items'] as $row) {

                $qty     = (float) ($row['quantity'] ?? 0);    // user entered
                $needQty = (float) ($row['need_qty'] ?? 0);    // remaining/need (auto)
                $totalQty += (int) $qty;

                // ✅ Row exited only when user qty > needQty
                $isRowExited = ($qty > $needQty) ? 1 : 0;

                // ✅ Row exited qty (extra only), else 0
                $rowExitedQty = $isRowExited ? ($qty - $needQty) : 0;

                // ✅ Main slip exited if ANY row exited
                if ($isRowExited === 1) {
                    $slipIsExited = 1;
                }

                $rowCreate = RequisitionSlipRow::create([
                    'requisition_slip_id' => $slip->id,
                    'project_id'          => $slip->project_id,
                    'machine_id'          => $row['machine_id'],
                    'item_id'             => $row['inventory_id'],
                    'quantity'            => (int) $qty,
                    'description'         => $row['description'],
                    'issue_qty'           => 0,
                    'pending_qty'         => 0,
                    'order_pending_qty'   => 0,

                    // ✅ NEW REQUIREMENT
                    'is_exited'           => $isRowExited,     // ✅ row flag
                    'exited_qty'          => (int) $rowExitedQty, // ✅ extra only else 0
                ]);

                if (!$rowCreate) {
                    throw new \Exception('Failed to save item rows.');
                }

                $inventory = Inventory::find($row['inventory_id']);

                if ($inventory && strtolower(trim($inventory->unit)) === 'kg') {
                    for ($i = 0; $i < $qty; $i++) {

                        RequisitionSlipRowPiece::create([
                            'requisition_slip_row_id' => $rowCreate->id,
                            'item_id'                 => $row['inventory_id'],
                            'issued_height'           =>  null,
                            'issued_width'            =>  null,
                            'consumed_height'         => 0,
                            'consumed_width'          => 0,
                            'issued_qty'            => 0,
                            'consumed_qty'          => 0,
                            'shape'                  => null,
                        ]);
                    }
                } else {
                    RequisitionSlipRowPiece::create([
                        'requisition_slip_row_id' => $rowCreate->id,
                        'item_id'                 => $row['inventory_id'],
                        'issued_height'           =>  null,
                        'issued_width'            =>  null,
                        'consumed_height'         => 0,
                        'consumed_width'          => 0,
                        'issued_qty'            => 0,
                        'consumed_qty'          => 0,
                        'shape'                  => null,
                    ]);
                }
            }

            $slip->update([
                'total_qty' => $totalQty,
                'is_exited' => $slipIsExited,
            ]);

            RequestSlipHistory::create([
                'request_slip_id' => $slip->id,
                'action_by'       => Auth::id(),
                'action'          => 'Created',
                'status'          => $slip->status,
                'remarks'         => $slip->comment,
            ]);

            $currentUser = Auth::user();

            $authorityId = $currentUser->authority_id;

            if ($authorityId) {
                Notification::create([
                    'role_id' => $authorityId,
                    "data" => [
                        'module'       => 'request_slip',
                        'rs_id'        => $slip->id,
                        'rs_code'      => $slip->rs_id,
                        'rs_name'      => $slip->name,
                        'auth'   =>      $slip->creator->name ?? 'Unknown',
                        'project_name' => $slip->project->name ?? 'Manual / Other',
                        'message'      => "New Request Slip generated: ",
                        'url'          => route('requisition.index'),
                    ],
                ]);
            }


            DB::commit();



            return redirect()
                ->route('request-slip.index')
                ->with('success', 'Request Slip created successfully.');
        } catch (\Throwable $e) {

            DB::rollBack();


            \Log::error('RequestSlip Store Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the Request Slip. Please try again or contact admin.');
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'project_id'             => 'required|exists:projects,id',
            'items.machine_id.*'     => 'required|exists:products,id',
            'items.inventory_id.*'   => 'required|exists:inventories,id',
            'items.quantity.*'       => 'required|numeric|min:1',
            'items.description.*'    => 'nullable|string|max:255',
        ], [
            'project_id.required'            => 'Project selection is required.',
            'items.machine_id.*.required'    => 'Please select a machine.',
            'items.inventory_id.*.required'  => 'Please select an inventory.',
            'items.quantity.*.required'      => 'Quantity is required.',
            'items.quantity.*.min'           => 'Quantity must be at least 1.',
        ]);

        try {
            $payload = $this->validateAndNormalize($request);

            DB::transaction(function () use ($payload, $id) {

                $slip = RequestSlip::with(['rows.inventory'])->findOrFail($id);

                // ---------- BEFORE SNAPSHOT ----------
                $before = [
                    'transaction_date' => $slip->transaction_date,
                    'employee_id'      => $slip->employee_id,
                    'department_id'    => $slip->department_id,
                    'project_id'       => $slip->project_id,
                    'comment'          => $slip->comment,
                    'status'           => $slip->status,
                    'total_qty'        => $slip->total_qty,
                    'is_exited'        => (int)($slip->is_exited ?? 0),
                ];

                $beforeRows = $slip->rows->map(function ($r) {
                    return [
                        'id'          => $r->id,
                        'project_id'  => $r->project_id,
                        'machine_id'  => $r->machine_id ?? null,
                        'item_id'     => $r->item_id,
                        'item_name'   => $r->inventory->name ?? null,
                        'quantity'    => (int)($r->quantity ?? 0),
                        'order_qty'   => (int)($r->order_qty ?? 0),
                        'issue_qty'   => (int)($r->issue_qty ?? 0),
                        'pending_qty' => (int)($r->pending_qty ?? 0),
                        'description' => $r->description,
                        'is_exited'   => (int)($r->is_exited ?? 0),
                        'exited_qty'  => (int)($r->exited_qty ?? 0),
                    ];
                })->keyBy(fn($x) => (string)$x['id'])->toArray();

                // ---------- UPDATE SLIP ----------
                $slip->update([
                    'transaction_date' => $payload['transaction_date'],
                    'employee_id'      => $payload['employee_id'],
                    'department_id'    => $payload['department_id'],
                    'project_id'       => $payload['project_id'],
                    'comment'          => $payload['comment'],
                    'edited_by'        => Auth::id(),
                    'edited_on'        => now(),
                ]);

                // ---------- UPDATE ROWS ----------
                $keepIds     = [];
                $totalQty    = 0;
                $existingIds = array_keys($beforeRows);

                // ✅ MAIN SLIP FLAG (any row exited => 1)
                $slipIsExited = 0;

                foreach ($payload['items'] as $row) {

                    $rowId = !empty($row['row_id']) ? (string)$row['row_id'] : null;

                    $enteredQty = (int) ($row['quantity'] ?? 0);
                    $desc       = $row['description'] ?? null;

                    $totalQty += $enteredQty;

                    if ($rowId && in_array($rowId, $existingIds, true)) {

                        $dbRow = RequisitionSlipRow::where('id', $rowId)
                            ->where('requisition_slip_id', $slip->id)
                            ->firstOrFail();

                        // ✅ REQUIRED (fixed) qty
                        $requiredQty = (int) ($dbRow->order_qty ?? 0);

                        // ✅ already issued qty (pehle se done)
                        $issuedQty   = (int) ($dbRow->issue_qty ?? 0);

                        // ✅ entered qty cannot be less than already issued (optional safety)
                        if ($enteredQty < $issuedQty) {
                            throw new \Exception("Quantity cannot be less than issued qty ({$issuedQty}) for row ID {$rowId}.");
                        }

                        // ✅ EXITED calc: (issued + entered) - required
                        $rowExitedQty = max(0, ($issuedQty + $enteredQty) - $requiredQty);
                        $isRowExited  = ($rowExitedQty > 0) ? 1 : 0;

                        if ($isRowExited === 1) {
                            $slipIsExited = 1;
                        }

                        RequisitionSlipRow::where('id', $rowId)
                            ->where('requisition_slip_id', $slip->id)
                            ->update([
                                'machine_id'        => $row['machine_id'],
                                'item_id'           => $row['inventory_id'],
                                'quantity'          => $enteredQty,

                                // ✅ IMPORTANT: order_qty ko update mat karo (fixed required)
                                // 'order_qty'       => $enteredQty,  // ❌ remove

                                'description'       => $desc,

                                // ✅ keep issue_qty as-is
                                'issue_qty'         => $issuedQty,

                                // your existing behaviour
                                'pending_qty'       => 0,
                                'order_pending_qty' => 0,

                                // ✅ overwrite exited values (can become 0)
                                'exited_qty'        => $rowExitedQty,
                                'is_exited'         => $isRowExited,
                            ]);

                        $keepIds[] = $rowId;
                    } else {

                        // ✅ new row case: required = entered (or you can set from product_items)
                        $requiredQty = $enteredQty;
                        $issuedQty   = 0;

                        $rowExitedQty = max(0, ($issuedQty + $enteredQty) - $requiredQty); // always 0 here
                        $isRowExited  = ($rowExitedQty > 0) ? 1 : 0;

                        if ($isRowExited === 1) {
                            $slipIsExited = 1;
                        }

                        $new = RequisitionSlipRow::create([
                            'requisition_slip_id' => $slip->id,
                            'project_id'          => $slip->project_id,
                            'machine_id'          => $row['machine_id'],
                            'item_id'             => $row['inventory_id'],
                            'quantity'            => $enteredQty,

                            // ✅ required fixed
                            'order_qty'           => $requiredQty,

                            'description'         => $desc,
                            'issue_qty'           => 0,
                            'pending_qty'         => 0,
                            'order_pending_qty'   => 0,

                            'exited_qty'          => $rowExitedQty,
                            'is_exited'           => $isRowExited,
                        ]);

                        $keepIds[] = (string) $new->id;
                    }
                }

                // delete removed rows
                RequisitionSlipRow::where('requisition_slip_id', $slip->id)
                    ->when(count($keepIds) > 0, fn($q) => $q->whereNotIn('id', $keepIds))
                    ->when(count($keepIds) === 0, fn($q) => $q)
                    ->delete();

                // ✅ MAIN SLIP flag overwrite (can become 0)
                $slip->update([
                    'total_qty' => $totalQty,
                    'is_exited' => $slipIsExited,
                ]);

                // ---------- AFTER SNAPSHOT (history) ----------
                $slip->load(['rows.inventory']);

                $after = [
                    'transaction_date' => $slip->transaction_date,
                    'employee_id'      => $slip->employee_id,
                    'department_id'    => $slip->department_id,
                    'project_id'       => $slip->project_id,
                    'comment'          => $slip->comment,
                    'status'           => $slip->status,
                    'total_qty'        => $slip->total_qty,
                    'is_exited'        => (int)($slip->is_exited ?? 0),
                ];

                $afterRows = $slip->rows->map(function ($r) {
                    return [
                        'id'          => $r->id,
                        'machine_id'  => $r->machine_id ?? null,
                        'item_id'     => $r->item_id,
                        'item_name'   => $r->inventory->name ?? null,
                        'quantity'    => (int)($r->quantity ?? 0),
                        'order_qty'   => (int)($r->order_qty ?? 0),
                        'issue_qty'   => (int)($r->issue_qty ?? 0),
                        'pending_qty' => (int)($r->pending_qty ?? 0),
                        'description' => $r->description,
                        'is_exited'   => (int)($r->is_exited ?? 0),
                        'exited_qty'  => (int)($r->exited_qty ?? 0),
                    ];
                })->keyBy(fn($x) => (string)$x['id'])->toArray();

                $fieldChanges = [];
                foreach ($before as $k => $v) {
                    $newV = $after[$k] ?? null;
                    if ((string)$v !== (string)$newV) {
                        $fieldChanges[$k] = ['from' => $v, 'to' => $newV];
                    }
                }

                $rowChanges = ['added' => [], 'updated' => [], 'removed' => []];

                foreach ($beforeRows as $rid => $bRow) {
                    if (!isset($afterRows[$rid])) {
                        $rowChanges['removed'][] = $bRow;
                    }
                }

                foreach ($afterRows as $rid => $aRow) {
                    if (!isset($beforeRows[$rid])) {
                        $rowChanges['added'][] = $aRow;
                        continue;
                    }

                    $bRow = $beforeRows[$rid];
                    $diff = [];

                    foreach (['machine_id', 'item_id', 'quantity', 'description', 'is_exited', 'exited_qty'] as $col) {
                        if ((string)($bRow[$col] ?? '') !== (string)($aRow[$col] ?? '')) {
                            $diff[$col] = ['from' => $bRow[$col] ?? null, 'to' => $aRow[$col] ?? null];
                        }
                    }

                    if (!empty($diff)) {
                        $rowChanges['updated'][] = [
                            'id'        => $rid,
                            'item_name' => $aRow['item_name'] ?? $bRow['item_name'] ?? null,
                            'changes'   => $diff,
                        ];
                    }
                }

                $changeSet = [
                    'slip_changes' => $fieldChanges,
                    'row_changes'  => $rowChanges,
                ];

                RequestSlipHistory::create([
                    'request_slip_id' => $slip->id,
                    'action_by'       => Auth::id(),
                    'action'          => 'Updated',
                    'status'          => $slip->status,
                    'remarks'         => json_encode($changeSet, JSON_UNESCAPED_UNICODE),
                ]);

                return $slip;
            });

            return redirect()
                ->route('request-slip.index')
                ->with('success', 'Request Slip updated successfully.');
        } catch (\Throwable $e) {

            \Log::error('RS update failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => Auth::id(),
                'slip_id' => $id,
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }







    private function validateAndNormalize(Request $request): array
    {
        $request->validate([
            'requisition_slip_no'    => ['required'],
            'transaction_date'       => ['required', 'date'],
            'employee_id'            => ['required'],
            'project_id'             => ['required'],

            'items.machine_id'       => ['required', 'array'],
            'items.inventory_id'     => ['required', 'array'],
            'items.quantity'         => ['required', 'array'],
            'items.need_qty'         => ['required', 'array'],              // ✅ NEW

            'items.machine_id.*'     => ['required'],
            'items.inventory_id.*'   => ['required'],
            'items.quantity.*'       => ['required', 'numeric', 'min:1'],
            'items.need_qty.*'       => ['nullable', 'numeric', 'min:0'],   // ✅ NEW
        ]);

        $rowIds       = $request->input('items.row_id', []);
        $machineIds   = $request->input('items.machine_id', []);
        $inventoryIds = $request->input('items.inventory_id', []);
        $qtys         = $request->input('items.quantity', []);
        $needQtys     = $request->input('items.need_qty', []); // ✅ NEW
        $descs        = $request->input('items.description', []);

        $count = count($machineIds);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'row_id'       => $rowIds[$i] ?? null,
                'machine_id'   => $machineIds[$i] ?? null,
                'inventory_id' => $inventoryIds[$i] ?? null,
                'quantity'     => $qtys[$i] ?? 1,
                'need_qty'     => $needQtys[$i] ?? 0,  // ✅ NEW (default 0)
                'description'  => $descs[$i] ?? null,
            ];
        }

        return [
            'requisition_slip_no' => $request->input('requisition_slip_no'),
            'transaction_date'    => $request->input('transaction_date'),
            'employee_id'         => $request->input('employee_id'),
            'department_id'       => $request->input('department_id'),
            'project_id'          => $request->input('project_id'),
            'comment'             => $request->input('comment'),
            'items'               => $items,
        ];
    }



    public function show($id)
    {
        $rs = RequestSlip::with([
            'creator',
            'project',
            'rows.inventory',
            'rows.machine',
            'histories' => fn($q) => $q->orderBy('created_at', 'desc'),
        ])->findOrFail($id);



        // ✅ ALL issues for this requisition slip
        $issues = Issue::with([
            'rows' => fn($q) => $q->select(
                'id',
                'issue_slip_id',
                'requisition_slip_row_id',
                'item_id',
                'description',
                'status',
                'issue_qty',
                'order_qty',
                'pending_qty'
            )
        ])
            ->where('requisition_slip_id', $rs->id)
            ->orderBy('id', 'desc')
            ->get();

        // ✅ Flatten all issue rows
        $allIssueRows = $issues->flatMap(fn($iss) => $iss->rows ?? collect());

        // ✅ Group by same requisition_slip_row_id (important)
        $issueRowsGroupedByReqRowId = $allIssueRows->groupBy('requisition_slip_row_id');

        // ✅ (optional fallback) item wise grouping
        $issueRowsGroupedByItemId = $allIssueRows->groupBy('item_id');

        return view('inventory::request_slip.show', compact(
            'rs',
            'issues',
            'issueRowsGroupedByReqRowId',
            'issueRowsGroupedByItemId'
        ));
    }




    public function approve($id)
    {
        $rs   = RequestSlip::findOrFail($id);
        $user = Auth::user();

        if ($user->role_id != 4 && $user->role_id != 1) {
            return back()->with('error', 'You are not authorized to perform this action.');
        }

        if (!in_array($rs->status, ['pending_hod', 'rejected_hod'])) {
            return back()->with('error', 'Request Slip is not in an approvable state.');
        }

        $rs->update([
            'status'  => 'pending_store',
            'remarks' => null,
        ]);

        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => $user->id,
            'action'          => 'approved_hod',
            'remarks'         => 'Approved by HOD. Sent to Store Department.',
        ]);

        return redirect()
            ->route('request-slip.index')
            ->with('success', 'Request Slip approved and sent to Store Department.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['remarks' => 'required|string|min:5']);

        $rs   = RequestSlip::findOrFail($id);
        $user = Auth::user();

        if ($user->role_id != 4 && $user->role_id != 1) {
            return back()->with('error', 'You are not authorized to perform this action.');
        }

        if (!in_array($rs->status, ['pending_hod', 'rejected_hod'])) {
            return back()->with('error', 'Request Slip is not in a rejectable state.');
        }


        $status = Auth::user()->role_id == 1 ? 'rejected_admin' : 'rejected_hod';

        $rs->update([
            'status'  => $status,
            'remarks' => $request->remarks,
        ]);


        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => $user->id,
            'action'          => 'rejected_hod',
            'remarks'         => 'Rejected by HOD: ' . $request->remarks,
        ]);

        return redirect()
            ->route('request-slip.show', $rs->id)
            ->with('success', 'Request Slip rejected and sent back to Supervisor.');
    }

    public function resubmit($id)
    {
        $rs   = RequestSlip::findOrFail($id);
        $user = Auth::user();

        if ($rs->created_by != $user->id || $user->role_id != 3) {
            return back()->with('error', 'You are not authorized to resubmit this slip.');
        }

        if ($rs->status != 'rejected_hod') {
            return back()->with('error', 'Request Slip is not in a resubmittable state.');
        }

        $rs->update([
            'status'  => 'pending_hod',
            'remarks' => null,
        ]);

        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => $user->id,
            'action'          => 'resubmitted',
            'remarks'         => 'Resubmitted by Supervisor after correction.',
        ]);

        return redirect()
            ->route('request-slip.index')
            ->with('success', 'Request Slip resubmitted to HOD for approval.');
    }

    public function complete(Request $request, $id)
    {
        $rs   = RequestSlip::with('items')->findOrFail($id);
        $user = Auth::user();

        if (!in_array($user->role_id, [1, 5])) {
            return back()->with('error', 'You are not authorized to perform this action.');
        }

        if ($request->has('items')) {
            foreach ($request->items as $itemId => $data) {
                $item = RequestSlipItem::find($itemId);
                if (!$item) continue;

                $issued      = (int)($data['issued'] ?? 0);
                $pending     = (int)($data['pending'] ?? 0);
                $pendingDate = $data['pending_date'] ?? null;

                if ($issued + $pending > $item->quantity) {
                    return back()->with(
                        'error',
                        'Issued + Pending quantity cannot exceed requested quantity for item ID: ' . $itemId
                    );
                }

                $item->update([
                    'issued_quantity'  => $issued,
                    'pending_quantity' => $pending,
                    'pending_date'     => $pendingDate ?: null,
                ]);
            }
        }

        $rs->update(['status' => 'approved']);

        RequestSlipHistory::create([
            'request_slip_id' => $rs->id,
            'action_by'       => $user->id,
            'action'          => 'store_issued',
            'remarks'         => 'Items issued by Store Department (with pending quantities if any).',
        ]);

        return redirect()
            ->route('request-slip.show', $rs->id)
            ->with('success', 'Request Slip successfully completed by Store Department.');
    }

    public function getProductsByProject($projectId)
    {
        $project  = Project::findOrFail($projectId);
        $products = $project->projectProducts()->with('product')->get()->pluck('product');

        return response()->json($products);
    }

    /**
     * IMPORTANT:
     * - Create mode: /request-slip/product-items/{productId}
     *   -> returns all product items for that product
     * - Edit mode: /request-slip/product-items/{productId}?rs_id={rsId}
     *   -> returns only those product items whose inventory_id exists in this RS
     */
    public function getProductItemsByProduct(Request $request, $productId)
    {
        $projectId = $request->project_id;   // ?project_id=XX
        $rsId      = $request->rs_id;        // ?rs_id=XX (optional)

        $product = Product::where('is_deleted', 0)->find($productId);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        // ✅ Always fetch ALL product_items for this machine
        $query = ProductItem::with(['inventory', 'product'])
            ->where('product_id', $productId)
            ->where('is_deleted', 0);

        // ✅ Edit mode RS qty map: [item_id => qty]
        // (NO filtering product_items by these ids)
        $rsQtyMap = [];
        if (!empty($rsId)) {
            $rsQtyMap = RequisitionSlipRow::where('requisition_slip_id', $rsId)
                ->pluck('quantity', 'item_id') // item_id = inventory_id
                ->toArray();
        }

        $productItems = $query->get();

        // ✅ SAME project + SAME machine + SAME inventory ki pehle wali requisitions qty SUM
        // ✅ BUT: rejected slips ko count nahi karna
        $otherReqQtyMap = [];

        if (!empty($projectId) && $productItems->count() > 0) {

            $invIds = $productItems->pluck('inventory_id')->unique()->values()->toArray();

            if (!empty($invIds)) {
                $otherReqQtyMap = DB::table('requisition_slip_rows as r')
                    ->join('requisition_slips as s', 's.id', '=', 'r.requisition_slip_id')
                    ->select('r.item_id as inventory_id', DB::raw('SUM(r.quantity) as req_qty'))
                    ->where('s.project_id', $projectId)
                    ->where('s.status', '!=', 'Rejected')     // ✅ ignore rejected
                    ->where('r.machine_id', $productId)
                    ->whereIn('r.item_id', $invIds)
                    ->when(!empty($rsId), function ($q) use ($rsId) {
                        // ✅ edit mode: current RS ko minus mat karo
                        $q->where('r.requisition_slip_id', '!=', $rsId);
                    })
                    ->groupBy('r.item_id')
                    ->pluck('req_qty', 'inventory_id')
                    ->toArray();
            }
        }

        foreach ($productItems as $pi) {

            $invId = (int) $pi->inventory_id;

            // ✅ required qty:
            // edit mode => RS qty (agar is inventory ka row exist karta hai)
            // create mode => product_items qty
            $requiredQty = !empty($rsId) && array_key_exists($invId, $rsQtyMap)
                ? (float) ($rsQtyMap[$invId] ?? 0)
                : (float) ($pi->quantity ?? 1);

            $alreadyReq = (float) ($otherReqQtyMap[$invId] ?? 0);

            $needQty = $requiredQty - $alreadyReq;

            $pi->required_qty          = $requiredQty;
            $pi->already_requested_qty = $alreadyReq;
            $pi->need_qty              = max(0, $needQty);
            $pi->is_exited             = ($needQty <= 0) ? 1 : 0;

            // ✅ Frontend ko help: ye inventory is RS me selected thi ya nahi
            $pi->is_selected_in_rs = (!empty($rsId) && array_key_exists($invId, $rsQtyMap)) ? 1 : 0;
        }

        // ✅ RS exited (optional)
        $rs_is_exited = 0;
        if (!empty($rsId) && $productItems->count() > 0) {
            $rs_is_exited = $productItems->every(function ($pi) {
                return (int) $pi->is_exited === 1;
            }) ? 1 : 0;
        }

        return response()->json([
            'status'       => true,
            'rs_is_exited' => $rs_is_exited,
            'data'         => $productItems
        ]);
    }








    // public function destroy($id)
    // {
    //     $rs = RequestSlip::findOrFail($id);

    //     // Delete child rows
    //     RequisitionSlipRow::where('requisition_slip_id', $id)->delete();

    //     // Delete history
    //     RequestSlipHistory::where('request_slip_id', $id)->delete();

    //     // Delete main record
    //     $rs->delete();

    //     return redirect()
    //         ->route('request-slip.index')
    //         ->with('success', 'Request Slip deleted successfully.');
    // }
    public function destroy($id)
    {
        RequestSlip::findOrFail($id)->delete(); // HARD DELETE
        return back()->with('success', 'Request Slip deleted successfully!');
    }

    // safety

    private function validateAndNormalizeSafety(Request $request): array
    {
        $rules = [
            'requisition_slip_no'    => ['required'],
            'transaction_date'       => ['required', 'date'],
            'employee_id'            => ['required'],

            'items.inventory_id'     => ['required', 'array'],
            'items.quantity'         => ['required', 'array'],


            'items.inventory_id.*'   => ['required'],
            'items.quantity.*'       => ['required', 'numeric', 'min:1'],

        ];

        $request->validate($rules);

        $rowIds       = $request->input('items.row_id', []);
        $machineIds   = $request->input('items.machine_id', []);
        $inventoryIds = $request->input('items.inventory_id', []);
        $qtys         = $request->input('items.quantity', []);

        $descs        = $request->input('items.description', []);

        // $count = count($machineIds);
        $count = count($inventoryIds);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'row_id'       => $rowIds[$i] ?? null,
                'machine_id'   => $machineIds[$i] ?? null,
                'inventory_id' => $inventoryIds[$i] ?? null,
                'quantity'     => $qtys[$i] ?? 1,

                'description'  => $descs[$i] ?? null,
            ];
        }

        return [
            'requisition_slip_no' => $request->input('requisition_slip_no'),
            'transaction_date'    => $request->input('transaction_date'),
            'employee_id'         => $request->input('employee_id'),
            'department_id'       => $request->input('department_id'),
            'project_id'          => $request->input('project_id'),
            'comment'             => $request->input('comment'),
            'items'               => $items,
        ];
    }
    public function safetyIndex(Request $request)
    {
        $user = Auth::user();

        // Dropdowns
        $users    = User::orderBy('name')->get();

        // Base Query (use rows relation)
        $query = RequestSlip::query()->with([
            'creator',          // created_by user
            'rows.inventory',   // requisition_slip_rows.item_id -> inventory
            'issue',
            'histories.user',   // history action_by user
        ]);




        $query->where('store_rs', 0)->where('created_by', $user->id);


        /**
         * FILTERS
         */
        // Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // RS Code (rs_id numeric)
        if ($request->filled('rs_code')) {
            $cleanCode = preg_replace('/\D+/', '', $request->input('rs_code'));
            if ($cleanCode !== '') {
                $query->where('rs_id', (int)$cleanCode);
            }
        }

        // User
        if ($request->filled('user')) {
            $query->where('created_by', $request->input('user'));
        }

        $query->orderByDesc('id'); // safest default

        // Paginate
        $requestSlips = $query->paginate(10)->withQueryString();


        // Keep filter box open
        $isFilterActive = $request->filled('status')
            || $request->filled('rs_code')
            || $request->filled('user');

        return view('inventory::request_slip.safety_index', compact(
            'requestSlips',
            'users',
            'isFilterActive'
        ));
    }
    public function safetyCreate()
    {
        $inventory = Inventory::where('category_id', '!=', 1)->get();

        // Logged-in user department
        $departmentId = Auth::user()->department_id;

        // Generate next Requisition Slip No (safe way using rs_id)
        $last = RequestSlip::orderBy('rs_id', 'desc')->first();
        $nextRsId = $last ? ($last->rs_id + 1) : 1;

        $nextSlipNo = 'RS-' . str_pad($nextRsId, 5, '0', STR_PAD_LEFT);

        return view('inventory::request_slip.safety_create', [
            'products'      => [],          // agar view me use hota hai to keep
            'inventory'     => $inventory,   // view crash na ho isliye empty
            'department_id' => $departmentId,
            'employee_id'   => Auth::id(),
            'nextSlipNo'    => $nextSlipNo,
            'requestSlip'   => null,
            'isEdit'        => false,
        ]);
    }

    public function safetyStore(Request $request)
    {
        $rules = [
            'items.inventory_id.*'   => 'required|exists:inventories,id',
            'items.quantity.*'       => 'required|numeric|min:1',
            'items.need_qty.*'       => 'nullable|numeric',
            'items.description.*'    => 'nullable|string|max:255',
        ];

        $messages = [
            'items.inventory_id.*.required'  => 'Please select an inventory.',
            'items.quantity.*.required'      => 'Quantity is required.',
            'items.quantity.*.min'           => 'Quantity must be at least 1.',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);


        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_add_form', true);
        }

        try {
            $payload = $this->validateAndNormalizeSafety($request);

            DB::beginTransaction();

            $slip = RequestSlip::create([
                'requisition_slip_no' => $payload['requisition_slip_no'],
                'transaction_date'    => $payload['transaction_date'],
                'employee_id'         => $payload['employee_id'],

                'comment'             => $payload['comment'],
                'created_by'          => Auth::id(),
                'total_qty'           => 0,
                'store_rs'            => 0,

                'status'              => 'Pending',
                'is_exited'           => 0,
            ]);

            if (!$slip) {
                throw new \Exception('Request Slip creation failed.');
            }

            $totalQty = 0;

            foreach ($payload['items'] as $row) {

                $qty     = (float) ($row['quantity'] ?? 0);    // user entered

                $totalQty += (int) $qty;

                $rowCreate = RequisitionSlipRow::create([
                    'requisition_slip_id' => $slip->id,

                    'item_id'             => $row['inventory_id'],
                    'quantity'            => (int) $qty,
                    'order_qty'           => (int) $qty,
                    'description'         => $row['description'],
                    'issue_qty'           => 0,
                    'pending_qty'         => 0,
                    'order_pending_qty'   => 0,

                ]);

                if (!$rowCreate) {
                    throw new \Exception('Failed to save item rows.');
                }
            }

            $slip->update([
                'total_qty' => $totalQty,

            ]);

            RequestSlipHistory::create([
                'request_slip_id' => $slip->id,
                'action_by'       => Auth::id(),
                'action'          => 'Created',
                'status'          => $slip->status,
                'remarks'         => $slip->comment,
            ]);

            DB::commit();

            return redirect()
                ->route('request-slip.safety.index')
                ->with('success', 'Request Slip created successfully.');
        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('RequestSlip Store Failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong while saving the Request Slip. Please try again or contact admin.');
        }
    }

    public function safetyShow($id)
    {
        $rs = RequestSlip::with([
            'creator',
            'rows.inventory',
        ])->findOrFail($id);

        // 2) Find latest issue slip for this requisition/request slip
        $issue = Issue::where('requisition_slip_id', $rs->id)
            ->latest('id')
            ->first();

        // 3) Issue rows map (key by requisition_slip_row_id OR item_id)
        $issueRowsByReqRowId = collect();
        $issueRowsByItemId   = collect();

        if ($issue) {
            $issueRows = IssueSlipRow::where('issue_slip_id', $issue->id)
                ->select('requisition_slip_row_id', 'item_id', 'description', 'status', 'quantity')
                ->get();

            $issueRowsByReqRowId = $issueRows->keyBy('requisition_slip_row_id');
            $issueRowsByItemId   = $issueRows->keyBy('item_id');

            return view('inventory::request_slip.safety_show1', compact('issue'));
        }

        return view('inventory::request_slip.safety_show', compact(
            'rs',
            'issue',
            'issueRowsByReqRowId',
            'issueRowsByItemId'
        ));
    }

    public function safetyEdit($id)
    {
        $inventory = Inventory::where('category_id', '!=', 1)->get();

        $requestSlip = RequestSlip::with(['rows.inventory'])->findOrFail($id);

        // edit mode => keep same slip no
        $nextSlipNo = $requestSlip->requisition_slip_no;

        return view('inventory::request_slip.safety_create', [
            'inventory'     => $inventory,
            'department_id' => Auth::user()->department_id,
            'employee_id'   => Auth::id(),
            'nextSlipNo'    => $nextSlipNo,
            'requestSlip'   => $requestSlip,
            'isEdit'        => true,
        ]);
    }

    public function safetyUpdate(Request $request, $id)
    {
        $request->validate([
            'items.inventory_id.*'   => 'required|exists:inventories,id',
            'items.quantity.*'       => 'required|numeric|min:1',
            'items.description.*'    => 'nullable|string|max:255',
        ], [
            'items.inventory_id.*.required'  => 'Please select an inventory.',
            'items.quantity.*.required'      => 'Quantity is required.',
            'items.quantity.*.min'           => 'Quantity must be at least 1.',
        ]);

        try {
            $payload = $this->validateAndNormalizeSafety($request);

            DB::transaction(function () use ($payload, $id) {

                $slip = RequestSlip::with(['rows.inventory'])->findOrFail($id);

                // ---------- BEFORE SNAPSHOT ----------
                $before = [
                    'transaction_date' => $slip->transaction_date,
                    'employee_id'      => $slip->employee_id,
                    'department_id'    => $slip->department_id,

                    'comment'          => $slip->comment,
                    'status'           => $slip->status,
                    'total_qty'        => $slip->total_qty,

                ];

                $beforeRows = $slip->rows->map(function ($r) {
                    return [
                        'id'          => $r->id,

                        'item_id'     => $r->item_id,
                        'item_name'   => $r->inventory->name ?? null,
                        'quantity'    => (int)($r->quantity ?? 0),
                        'order_qty'   => (int)($r->order_qty ?? 0),
                        'issue_qty'   => (int)($r->issue_qty ?? 0),
                        'pending_qty' => (int)($r->pending_qty ?? 0),
                        'description' => $r->description,

                    ];
                })->keyBy(fn($x) => (string)$x['id'])->toArray();

                // ---------- UPDATE SLIP ----------
                $slip->update([
                    'transaction_date' => $payload['transaction_date'],
                    'employee_id'      => $payload['employee_id'],
                    'department_id'    => $payload['department_id'],

                    'comment'          => $payload['comment'],
                    'edited_by'        => Auth::id(),
                    'edited_on'        => now(),
                ]);

                // ---------- UPDATE ROWS ----------
                $keepIds     = [];
                $totalQty    = 0;
                $existingIds = array_keys($beforeRows);



                foreach ($payload['items'] as $row) {

                    $rowId = !empty($row['row_id']) ? (string)$row['row_id'] : null;

                    $enteredQty = (int) ($row['quantity'] ?? 0);
                    $desc       = $row['description'] ?? null;

                    $totalQty += $enteredQty;

                    if ($rowId && in_array($rowId, $existingIds, true)) {

                        $dbRow = RequisitionSlipRow::where('id', $rowId)
                            ->where('requisition_slip_id', $slip->id)
                            ->firstOrFail();

                        // ✅ REQUIRED (fixed) qty
                        $requiredQty = (int) ($dbRow->order_qty ?? 0);

                        // ✅ already issued qty (pehle se done)
                        $issuedQty   = (int) ($dbRow->issue_qty ?? 0);

                        // ✅ entered qty cannot be less than already issued (optional safety)
                        if ($enteredQty < $issuedQty) {
                            throw new \Exception("Quantity cannot be less than issued qty ({$issuedQty}) for row ID {$rowId}.");
                        }

                        // ✅ EXITED calc: (issued + entered) - required
                        $rowExitedQty = max(0, ($issuedQty + $enteredQty) - $requiredQty);

                        RequisitionSlipRow::where('id', $rowId)
                            ->where('requisition_slip_id', $slip->id)
                            ->update([

                                'item_id'           => $row['inventory_id'],
                                'quantity'          => $enteredQty,

                                'description'       => $desc,

                                // ✅ keep issue_qty as-is
                                'issue_qty'         => $issuedQty,

                                // your existing behaviour
                                'pending_qty'       => 0,
                                'order_pending_qty' => 0,


                            ]);

                        $keepIds[] = $rowId;
                    } else {

                        // ✅ new row case: required = entered (or you can set from product_items)
                        $requiredQty = $enteredQty;
                        $issuedQty   = 0;

                        $rowExitedQty = max(0, ($issuedQty + $enteredQty) - $requiredQty); // always 0 here
                        $isRowExited  = ($rowExitedQty > 0) ? 1 : 0;


                        $new = RequisitionSlipRow::create([
                            'requisition_slip_id' => $slip->id,

                            'item_id'             => $row['inventory_id'],
                            'quantity'            => $enteredQty,

                            // ✅ required fixed
                            'order_qty'           => $requiredQty,

                            'description'         => $desc,
                            'issue_qty'           => 0,
                            'pending_qty'         => 0,
                            'order_pending_qty'   => 0,


                        ]);

                        $keepIds[] = (string) $new->id;
                    }
                }

                // delete removed rows
                RequisitionSlipRow::where('requisition_slip_id', $slip->id)
                    ->when(count($keepIds) > 0, fn($q) => $q->whereNotIn('id', $keepIds))
                    ->when(count($keepIds) === 0, fn($q) => $q)
                    ->delete();

                // ✅ MAIN SLIP flag overwrite (can become 0)
                $slip->update([
                    'total_qty' => $totalQty,
                ]);

                // ---------- AFTER SNAPSHOT (history) ----------
                $slip->load(['rows.inventory']);

                $after = [
                    'transaction_date' => $slip->transaction_date,
                    'employee_id'      => $slip->employee_id,
                    'department_id'    => $slip->department_id,

                    'comment'          => $slip->comment,
                    'status'           => $slip->status,
                    'total_qty'        => $slip->total_qty,

                ];

                $afterRows = $slip->rows->map(function ($r) {
                    return [
                        'id'          => $r->id,

                        'item_id'     => $r->item_id,
                        'item_name'   => $r->inventory->name ?? null,
                        'quantity'    => (int)($r->quantity ?? 0),
                        'order_qty'   => (int)($r->order_qty ?? 0),
                        'issue_qty'   => (int)($r->issue_qty ?? 0),
                        'pending_qty' => (int)($r->pending_qty ?? 0),
                        'description' => $r->description,

                    ];
                })->keyBy(fn($x) => (string)$x['id'])->toArray();

                $fieldChanges = [];
                foreach ($before as $k => $v) {
                    $newV = $after[$k] ?? null;
                    if ((string)$v !== (string)$newV) {
                        $fieldChanges[$k] = ['from' => $v, 'to' => $newV];
                    }
                }

                $rowChanges = ['added' => [], 'updated' => [], 'removed' => []];

                foreach ($beforeRows as $rid => $bRow) {
                    if (!isset($afterRows[$rid])) {
                        $rowChanges['removed'][] = $bRow;
                    }
                }

                foreach ($afterRows as $rid => $aRow) {
                    if (!isset($beforeRows[$rid])) {
                        $rowChanges['added'][] = $aRow;
                        continue;
                    }

                    $bRow = $beforeRows[$rid];
                    $diff = [];

                    foreach (['item_id', 'quantity', 'description', 'is_exited', 'exited_qty'] as $col) {
                        if ((string)($bRow[$col] ?? '') !== (string)($aRow[$col] ?? '')) {
                            $diff[$col] = ['from' => $bRow[$col] ?? null, 'to' => $aRow[$col] ?? null];
                        }
                    }

                    if (!empty($diff)) {
                        $rowChanges['updated'][] = [
                            'id'        => $rid,
                            'item_name' => $aRow['item_name'] ?? $bRow['item_name'] ?? null,
                            'changes'   => $diff,
                        ];
                    }
                }

                $changeSet = [
                    'slip_changes' => $fieldChanges,
                    'row_changes'  => $rowChanges,
                ];

                RequestSlipHistory::create([
                    'request_slip_id' => $slip->id,
                    'action_by'       => Auth::id(),
                    'action'          => 'Updated',
                    'status'          => $slip->status,
                    'remarks'         => json_encode($changeSet, JSON_UNESCAPED_UNICODE),
                ]);

                return $slip;
            });

            return redirect()
                ->route('request-slip.safety.index')
                ->with('success', 'Request Slip updated successfully.');
        } catch (\Throwable $e) {

            \Log::error('RS update failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'user_id' => Auth::id(),
                'slip_id' => $id,
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function safetyDestroy($id)
    {
        RequestSlip::findOrFail($id)->delete(); // HARD DELETE
        return back()->with('success', 'Request Slip deleted successfully!');
    }

    public function viewAll(Request $request)
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

        $user = Auth::user();

        // Dropdowns
        $users     = User::orderBy('name')->get();
        $projects  = Project::orderBy('name')->get();
        $machines  = Product::orderBy('name')->get();

        // Base Query
        $query = RequestSlip::query()->with([
            'creator',
            'rows.inventory',
            'rows.machine',
            'rows.pieces' => function ($q) {
                $q->where('is_completed', 0)
                    ->with('inventory'); 
            },
            'issue',
            'issue.rows.inventory',
            'histories.user',
            'project',

        ]);

        // ✅ Show all RS (no role-wise restriction)
        // ✅ Keep store_rs filter same (remove if you want ALL without store_rs condition)
        $query->where('store_rs', 1);

        /**
         * FILTERS (same)
         */
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('rs_code')) {
            $cleanCode = preg_replace('/\D+/', '', $request->input('rs_code'));
            if ($cleanCode !== '') {
                $query->where('rs_id', (int)$cleanCode);
            }
        }


        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_on', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && !$request->filled('from_date')) {
            $query->whereDate('created_on', '<=', $request->to_date);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_on', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        }

        if ($request->filled('project')) {
            $query->where('project_id', $request->input('project'));
        }

        if ($request->filled('machine')) {

            $query->whereHas('rows', function ($q) use ($request) {

                $q->where('machine_id', $request->machine);
            });
        }

        $query->orderByDesc('id');

        $requestSlips = $query->paginate(10)->withQueryString();

        /**
         * ✅ Consumption sums + history + last selected machine/project maps
         */
        $rsIds = $requestSlips->getCollection()->pluck('id')->values()->all();

        $consumedQtyMapByRs = [];
        $consumedHMapByRs   = [];
        $consumedWMapByRs   = [];
        $consumptionHistoryByRs = [];

        $lastMachineMapByRs = [];
        $lastProjectMapByRs = [];

        if (!empty($rsIds)) {

            $qtyRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(quantity) as total_qty'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($qtyRows as $row) {
                $consumedQtyMapByRs[$row->request_slips_id][$row->rs_row_id] = (float)$row->total_qty;
            }

            $hRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(height) as total_h'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($hRows as $row) {
                $consumedHMapByRs[$row->request_slips_id][$row->rs_row_id] = (float)$row->total_h;
            }

            $wRows = Consumption::whereIn('request_slips_id', $rsIds)
                ->select('request_slips_id', 'rs_row_id', DB::raw('SUM(width) as total_w'))
                ->groupBy('request_slips_id', 'rs_row_id')
                ->get();

            foreach ($wRows as $row) {
                $consumedWMapByRs[$row->request_slips_id][$row->rs_row_id] = (float)$row->total_w;
            }

            // ✅ history (latest first)
             $hist = Consumption::whereIn('request_slips_id', $rsIds)
             ->with(['inventory', 'user'])
                ->orderByDesc('id')
                ->get();


                
            foreach ($hist as $c) {
                $consumptionHistoryByRs[$c->request_slips_id][] = $c;

                // latest consumption wins
                if (!isset($lastMachineMapByRs[$c->request_slips_id][$c->rs_row_id])) {
                    $lastMachineMapByRs[$c->request_slips_id][$c->rs_row_id] = $c->machine_id ?? null;
                }
                if (!isset($lastProjectMapByRs[$c->request_slips_id][$c->rs_row_id])) {
                    $lastProjectMapByRs[$c->request_slips_id][$c->rs_row_id] = $c->project_id ?? null;
                }
            }
        }

        // Attach to each RS
        $requestSlips->getCollection()->transform(function ($rs) use (
            $consumedQtyMapByRs,
            $consumedHMapByRs,
            $consumedWMapByRs,
            $consumptionHistoryByRs,
            $lastMachineMapByRs,
            $lastProjectMapByRs
        ) {
            $rs->consumedQtyMap = $consumedQtyMapByRs[$rs->id] ?? [];
            $rs->consumedHMap   = $consumedHMapByRs[$rs->id] ?? [];
            $rs->consumedWMap   = $consumedWMapByRs[$rs->id] ?? [];
            $rs->consumedWMap   = $consumedWMapByRs[$rs->id] ?? [];
            $rs->consHistory    = $consumptionHistoryByRs[$rs->id] ?? [];

            $rs->lastMachineMap = $lastMachineMapByRs[$rs->id] ?? [];
            $rs->lastProjectMap = $lastProjectMapByRs[$rs->id] ?? [];

            return $rs;
        });

        $isFilterActive =
            $request->filled('status') ||
            $request->filled('rs_code') ||
            $request->filled('user') ||
            $request->filled('project');

            $modelprojects = Project::orderBy('name')->get();

             $last = RequestSlip::orderBy('rs_id', 'desc')->first();
            $nextRsId = $last ? ($last->rs_id + 1) : 1;

            $nextSlipNo = 'RS-' . str_pad($nextRsId, 5, '0', STR_PAD_LEFT);

        return view('inventory::request_slip.view-all', compact(
            'requestSlips',
            'users',
            'projects',
            'machines',
            'isFilterActive',
             'modelprojects',  
             'nextSlipNo' 
        ));
    }
}
