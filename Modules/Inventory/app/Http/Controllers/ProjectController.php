<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\ProjectItem;
use DB;
use Illuminate\Http\Request;
use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\ProjectProduct;
use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\ProductItem;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\App\Events\ProjectCreated;
use Modules\Inventory\App\Models\ProjectMainStage;
use Modules\Inventory\App\Models\ProjectSubStage;
use Modules\Inventory\App\Models\RequisitionSlipRow;
use Modules\Inventory\App\Models\Stage;
use Modules\Inventory\App\Models\StageStatus;

class ProjectController extends Controller
{
    // Add + List same page
    public function index(Request $request)
    {
        $query = Project::with(['user', 'projectProducts.product.productItems.inventory' => function ($q) {
            $q->select('id', 'name'); // only inventory id and name
        }, 'user'])
            ->orderByRaw("
        FIELD(priority, 'URGENT', 'HIGH', 'NORMAL', 'LOW')
    ")->orderBy('id', 'desc');

        // Filter: Project Name
        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter: Created By (user name)
        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->user . '%');
            });
        }

        // Filter: Product inside Project
        if ($request->filled('machine_id')) {
            $query->whereHas('projectProducts', function ($q) use ($request) {
                $q->where('product_id', 'LIKE', '%' . $request->machine_id . '%');
            });
        }


        $projects = $query->with(['user', 'projectProducts'])->paginate(10);

        $projects->map(function ($project) {

            $progress = 0;

            // =========================================
            // MAIN STAGES
            // =========================================
            $mainStages = ProjectMainStage::with('mainStage')
                ->where('project_id', $project->id)
                ->orderBy('id', 'desc')
                ->get()
                ->unique('main_stage_id');

            // completed main stages tracker
            $completedMainStages = [];

            foreach ($mainStages as $main) {

                $mainPercent = $main->mainStage->present ?? 0;

                // latest main stage completed
                if ($main->status_id == 6) {

                    $progress += $mainPercent;

                    // save completed main stage
                    $completedMainStages[] = $main->main_stage_id;
                }
            }

            // =========================================
            // SUB STAGES
            // =========================================

            $subStages = ProjectSubStage::with('subStage')
                ->where('project_id', $project->id)
                ->orderBy('id', 'desc')
                ->get()
                ->unique('sub_stage_id');

            foreach ($subStages as $sub) {

                // only completed sub stages
                if ($sub->status_id != 7) {
                    continue;
                }

                // parent main stage
                $mainStage = ProjectMainStage::with('mainStage')
                    ->where('id', $sub->project_main_stage_id)
                    ->first();

                if (!$mainStage) {
                    continue;
                }

                // =========================================
                // IF MAIN STAGE ALREADY COMPLETED
                // THEN SKIP SUB STAGES
                // =========================================
                if (in_array($mainStage->main_stage_id, $completedMainStages)) {
                    continue;
                }

                $mainPercent = $mainStage->mainStage->present ?? 0;

                $subPercent = $sub->subStage->present ?? 0;

                $progress += ($mainPercent * $subPercent) / 100;
            }

            $project->progress = round($progress, 2);

            return $project;
        });

        // $projects->map(function ($project) use ($statusPercent) {

        //     $machines = collect($project->projectProducts ?? []);

        //     $progress = $machines->count()
        //         ? round($machines->map(fn($m) => $statusPercent[$m->status] ?? 0)->avg())
        //         : 0;

        //     $project->progress = $progress;
        //     return $project;
        // });

        $sections = Stage::whereNotNull('section')
            ->distinct()
            ->pluck('section');


        $project = null;
        $products = Product::where('is_deleted', 0)->orderBy('name')->get();
        $inventories = Inventory::where('is_deleted', 0)->orderBy('name')->get();
        $productsfilter = Project::orderBy('id')->get();

        return view('inventory::project.index', compact('projects', 'project', 'products', 'inventories', 'productsfilter', 'sections'));
    }


    // Store new project + its products
    // public function store(Request $request)
    // {
    //     $request->validate([
    //     'name'   => 'required|string',
    //     'status' => 'required|string',
    //     'budget' => 'required|numeric|min:0',
    //     'product_id.*' => 'nullable|exists:products,id',
    //     'quantity.*' => 'required|integer|min:1',
    //     'product_status.*' => 'nullable|string',
    // ], [
    //     'budget.required' => 'Project budget is required',
    //     'budget.integer'  => 'Budget must be a number',
    // ]);

    //     $project = Project::create([
    //     'name' => $request->name,
    //     'status' => $request->status,
    //     'budget' => $request->budget ?? 0, // 🔥 MUST
    //     'start_date' => $request->start_date,
    //     'end_date' => $request->end_date,
    //     'created_by' => Auth::id(),
    // ]);


    //     foreach ($request->product_id as $i => $pid) {
    //     ProjectProduct::create([
    //         'project_id' => $project->id,
    //         'product_id' => $pid,
    //         'quantity'   => $request->quantity[$i] ?? 1,

    //         // 🔥 nullable status
    //         'status'     => $request->product_status[$i] ?? null,

    //         'is_deleted' => 0,
    //     ]);
    // }

    // foreach ($request->inventory_id ?? [] as $i => $iid) {
    //                 $productId = $request->product_id[$i] ?? null;
    //              if ($iid) {
    //                  ProductItem::create([
    //                      'product_id' => $productId,
    //                      'inventory_id' => $iid,
    //                      'quantity' => $request->inventory_qty[$i] ?? 1,
    //                      'is_deleted' => 0,
    //                  ]);
    //              }
    //          }


    //     return back()->with('success', 'Project created successfully');
    // }
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name'       => 'required|string|max:255|unique:inventory_projects,name',
            'status'     => 'required|string',
            'priority'   => 'required|string',
            'budget'     => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'refurbish' => 'nullable|in:0,1',
            'not'        => 'nullable',

            'product_id'        => 'array',
            'product_id.*'      => 'nullable|exists:products,id',
            'product_status'    => 'array',
            'product_status.*'  => 'nullable|string',

            'inventory_id'      => 'array',
            'inventory_id.*'    => 'nullable|exists:inventories,id',
            'inventory_qty'     => 'array',
            'inventory_qty.*'   => 'nullable|integer|min:1',

        ], [
            'name.unique'   => 'This Project name already exists.',

        ]);

        DB::transaction(function () use ($request) {

            // 1) Create Project
            $project = Project::create([
                'name'       => $request->name,
                'status'     => $request->status,
                'priority'   => $request->priority,
                'comment'        => $request->not,
                'budget'     => $request->budget,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'created_by' => auth()->id(),
                'refurbish'  => (int) $request->input('refurbish', 0),
                'completion_date' => $request->status === 'completed' ? now()  : null,
                'work_flow'  => $request->work_flow ?? null,

            ]);

            // 2) Save Products (multiple)
            $productIds = $request->product_id ?? [];
            foreach ($productIds as $i => $pid) {
                if (empty($pid)) continue;

                ProjectProduct::create([
                    'project_id' => $project->id,
                    'product_id' => $pid,
                    'quantity'   =>  "1",
                    'status'     => $request->product_status[$i] ?? null,
                ]);
            }

            // 3) Save Inventory Items (multiple) -> ProjectItem table
            $invIds = $request->inventory_id ?? [];
            $invQty = $request->inventory_qty ?? [];
            $lengths = $request->length ?? [];

            // Optional: same inventory repeated ho to quantity add ho jaye
            $grouped = [];
            foreach ($invIds as $i => $invId) {
                if (empty($invId)) continue;
                $qty = (int)($invQty[$i] ?? 1);
                $len = $lengths[$i] ?? null;

                if (!isset($grouped[$invId])) $grouped[$invId] = ['quantity' => 0, 'length' => $len];
                $grouped[$invId]['quantity'] += $qty;
                $grouped[$invId]['length'] = $len; // store last entered length
            }

            foreach ($grouped as $invId => $data) {
                ProjectItem::create([
                    'project_id'   => $project->id,
                    'inventory_id' => $invId,
                    'quantity'     => $data['quantity'],
                    'length'       => $data['length'],  // ✅ saving length
                ]);
            }
        });

        return redirect()
            ->route('project.index')
            ->with('success', 'Project created successfully');
    }


    // Edit - same page pe form load
    public function edit($id)
    {
        $projects = Project::with([
            'user',
            'projectProducts.product',   // list table me products dikhane ke liye
            'projectItems.inventory',    // project items show karne ke liye
        ])->orderBy('id', 'desc')->paginate(10);

        $project = Project::with([
            'projectProducts.product',   // edit form me products ke liye
            'projectItems.inventory',    // ✅ edit form me inventory items ke liye
        ])->findOrFail($id);
        $productsfilter = Project::orderBy('id')->get();

        $sections = Stage::whereNotNull('section')
            ->distinct()
            ->pluck('section');

        return view('inventory::project.index', [
            'productsfilter' => $productsfilter,
            'projects'     => $projects,
            'project'      => $project,
            'products'     => Product::where('is_deleted', 0)->orderBy('name')->get(),
            'inventories'  => Inventory::where('is_deleted', 0)->orderBy('name')->get(),
            'sections'     => $sections,
        ]);
    }


    // Update project + products
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //     'name'   => 'required|string',
    //     'status' => 'required|string',
    //     'budget' => 'required|numeric|min:0',
    //     'product_id.*' => 'nullable|exists:products,id',
    //     'quantity.*' => 'required|integer|min:1',
    //     'product_status.*' => 'nullable|string',
    // ], [
    //     'budget.required' => 'Project budget is required',
    //     'budget.integer'  => 'Budget must be a number',
    // ]);

    //     $project = Project::findOrFail($id);
    //     $project->update([
    //     'name' => $request->name,
    //     'status' => $request->status,
    //     'budget' => $request->budget ?? 0,
    //     'start_date' => $request->start_date,
    //     'end_date' => $request->end_date,
    // ]);
    //     ProjectProduct::where('project_id', $id)->delete();

    // foreach ($request->product_id as $i => $pid) {
    //     ProjectProduct::create([
    //         'project_id' => $id,
    //         'product_id' => $pid,
    //         'quantity'   => $request->quantity[$i] ?? 1,
    //         'status'     => $request->product_status[$i] ?? null,
    //     ]);
    // }
    // ProductItem::where('product_id', $id)->delete();
    //  foreach ($request->product_id ?? [] as $i => $pid) {
    //          if ($pid) {
    //              ProjectProduct::create([
    //                  'project_id' => $project->id,
    //                  'product_id' => $pid,
    //                  'quantity' => $request->quantity[$i] ?? 1,

    //             ]);
    //          }
    //     }
    //     return redirect()->route('project.index')
    //         ->with('success', 'Project updated successfully');
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'status'     => 'required|string',
            'budget'     => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'refurbish' => 'nullable|in:0,1',


            'product_id'       => 'array',
            'product_id.*'     => 'nullable|exists:products,id',
            'product_status'   => 'array',
            'product_status.*' => 'nullable|string',

            'inventory_id'     => 'array',
            'inventory_id.*'   => 'nullable|exists:inventories,id',
            'inventory_qty'    => 'array',
            'inventory_qty.*'  => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $id) {

            $project = Project::findOrFail($id);


            // 1) Update Project
            $project->update([
                'name'       => $request->name,
                'status'     => $request->status,
                'priority'   => $request->priority,
                'comment'        => $request->not,
                'budget'     => $request->budget,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'refurbish'  => (int) $request->input('refurbish', 0),
                'completion_date' => $request->status === 'completed' ? now()  : null,
                'work_flow'      => $request->work_flow ?? null,

            ]);

            // 2) Clear old relations (same project)
            ProjectProduct::where('project_id', $project->id)->delete();
            ProjectItem::where('project_id', $project->id)->delete();

            ProjectMainStage::where('project_id',$project->id)->delete();
            ProjectSubStage::where('project_id', $project->id)->delete();

            // 3) Insert Products (multiple)
            $productIds = $request->product_id ?? [];
            foreach ($productIds as $i => $pid) {
                if (empty($pid)) continue;

                ProjectProduct::create([
                    'project_id' => $project->id,
                    'product_id' => $pid,
                    'quantity'   => '1',
                    'status'     => $request->product_status[$i] ?? null,
                ]);
            }

            $invIds = $request->inventory_id ?? [];
            $invQty = $request->inventory_qty ?? [];
            $lengths = $request->length ?? [];

            // Optional: same inventory repeated ho to quantity add ho jaye
            $grouped = [];
            foreach ($invIds as $i => $invId) {
                if (empty($invId)) continue;
                $qty = (int)($invQty[$i] ?? 1);
                $len = $lengths[$i] ?? null;

                if (!isset($grouped[$invId])) $grouped[$invId] = ['quantity' => 0, 'length' => $len];
                $grouped[$invId]['quantity'] += $qty;
                $grouped[$invId]['length'] = $len; // store last entered length
            }

            foreach ($grouped as $invId => $data) {
                ProjectItem::create([
                    'project_id'   => $project->id,
                    'inventory_id' => $invId,
                    'quantity'     => $data['quantity'],
                    'length'       => $data['length'],  // ✅ saving length
                ]);
            }
        });

        return redirect()
            ->route('project.index')
            ->with('success', 'Project updated successfully');
    }




    // Mark as deleted
    public function delete($id)
    {
        $project = Project::findOrFail($id);
        $project->is_deleted = 1;
        $project->save();

        return back()->with('success', 'Project deleted successfully');
    }

    // Restore
    public function restore($id)
    {
        $project = Project::findOrFail($id);
        $project->is_deleted = 0;
        $project->save();

        return back()->with('success', 'Project restored successfully');
    }

    public function show($id)
    {
        $project = Project::with([
            'user',

            // Machines + Product Details
            'projectProducts.product',

            // Machine ke items
            'projectProducts.product.productItems.inventory',

            // Project inventory items
            'projectItems.inventory'

        ])->findOrFail($id);

        // ===== Progress Calculation =====


        // $machines = collect($project->projectProducts ?? []);

        // $project->progress = $machines->count()
        //     ? round(
        //         $machines->map(fn($m) => $statusPercent[$m->status] ?? 0)->avg()
        //     )
        //     : 0;


        $progress = 0;

        // =========================================
        // MAIN STAGES
        // =========================================
        $mainStages = ProjectMainStage::with('mainStage')
            ->where('project_id', $project->id)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('main_stage_id');

        // completed main stages tracker
        $completedMainStages = [];

        foreach ($mainStages as $main) {

            $mainPercent = $main->mainStage->present ?? 0;

            // latest main stage completed
            if ($main->status_id == 6) {

                $progress += $mainPercent;

                // save completed main stage
                $completedMainStages[] = $main->main_stage_id;
            }
        }

        // =========================================
        // SUB STAGES
        // =========================================

        $subStages = ProjectSubStage::with('subStage')
            ->where('project_id', $project->id)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('sub_stage_id');

        foreach ($subStages as $sub) {

            // only completed sub stages
            if ($sub->status_id != 7) {
                continue;
            }

            // parent main stage
            $mainStage = ProjectMainStage::with('mainStage')
                ->where('id', $sub->project_main_stage_id)
                ->first();

            if (!$mainStage) {
                continue;
            }

            // =========================================
            // IF MAIN STAGE ALREADY COMPLETED
            // THEN SKIP SUB STAGES
            // =========================================
            if (in_array($mainStage->main_stage_id, $completedMainStages)) {
                continue;
            }

            $mainPercent = $mainStage->mainStage->present ?? 0;

            $subPercent = $sub->subStage->present ?? 0;

            $progress += ($mainPercent * $subPercent) / 100;
        }

        $project->progress = round($progress, 2);


        foreach ($project->projectProducts as $machine) {

            $machineId = $machine->product_id;

            // machine ke sab inventories
            foreach ($machine->product->productItems as $item) {

                $inventoryId = $item->inventory_id;

                // total issued qty
                $issuedQty = RequisitionSlipRow::where('project_id', $project->id)
                    ->where('machine_id', $machineId)
                    ->where('item_id', $inventoryId)
                    ->sum('issued_qty');


                // save for blade
                $item->issued_qty = $issuedQty;

                // required qty
                $requiredQty = $item->quantity ?? 0;

                // exceed qty
                $item->exceed_qty = max(0, $issuedQty - $requiredQty);

                // remaining qty
                $item->remaining_qty = max(0, $requiredQty - $issuedQty);
            }
        }

        // ======================================
        // WORKFLOW ANALYTICS
        // ======================================



        $mainStages = ProjectMainStage::with([
            'mainStage',
            'subs',
            'subs.subStage',
            'subs.status',
            'status',
        ])
            ->where('project_id', $project->id)
            ->whereNotNull('main_stage_id')
            ->get()
            ->filter(function ($row) {
                return $row->mainStage != null;
            })
            ->groupBy('main_stage_id');

        $stageAnalytics = [];
        $chartStages = [];

        $colors = [
            '#6366f1', // Indigo
            '#22c55e', // Green
            '#06b6d4', // Cyan
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#8b5cf6', // Purple
            '#ec4899', // Pink
            '#14b8a6', // Teal
            '#3b82f6', // Blue
            '#84cc16', // Lime
        ];

        foreach ($mainStages as $index => $rows) {

            // latest main stage
            $main = $rows
                ->sortByDesc('id')
                ->first();

            $allSubs = $rows
                ->flatMap(function ($row) {

                    return $row->subs;
                })
                ->sortByDesc('id')
                ->unique('sub_stage_id')
                ->values();
            // =====================================
            // ALL SUB STAGES FROM MASTER TABLE
            // =====================================

            $allSubStages = Stage::where(
                'parent_id',
                $main->main_stage_id
            )->orderBy('order_no')->get();

            // total sub stages
            $totalSubs = $allSubStages->count();

            // =====================================
            // COMPLETED SUB STAGES FROM PROJECT TABLE
            // =====================================

            $completedSubsData = collect($allSubs)
                ->filter(function ($sub) {
                    return $sub->status_id == 7;
                })
                ->groupBy('sub_stage_id');

            $completedSubs = $completedSubsData->count();

            // =====================================
            // IN PROGRESS SUB STAGES
            // =====================================


            $runningSubsData = collect($allSubs)
                ->filter(function ($sub) {

                    return strtolower($sub->status->name ?? '') == 'in progress';
                })
                ->groupBy('sub_stage_id');


            // running sub stage names
            $runningSubStages = $runningSubsData
                ->map(function ($items) {

                    return $items->first()->subStage->name ?? null;
                })
                ->filter()
                ->values()
                ->toArray();

            // =====================================
            // MAIN STAGE STATUS
            // =====================================

            $mainStageRunning = strtolower($main->status->name ?? '') == 'in progress';

            // =====================================
            // PROGRESS %
            // =====================================

            $progress = 0;

            foreach ($allSubStages as $subStage) {

                $isCompleted = $completedSubsData->has($subStage->id);

                if ($isCompleted) {

                    $progress += $subStage->present ?? 0;
                }
            }

            // NO SUB STAGES
            if ($totalSubs == 0) {

                $progress = $main->status_id == 6 ? 100 : 0;
            }


            $stageAnalytics[] = [

                'main_stage_id' => $main->main_stage_id,

                'stage_name' =>
                $main->mainStage->name,

                'percentage' =>
                round($progress),

                'color' =>
                $colors[$index % count($colors)],


                'completed_subs' =>
                $completedSubs,

                'total_subs' =>
                $totalSubs,
                'completed_stage_ids' =>
                $completedSubsData->keys()->values()->toArray(),

                'main_stage_running' =>
                $mainStageRunning,

                'running_sub_stages' =>
                $runningSubStages,

                'all_sub_stages' =>
                $allSubStages->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                    ];
                })->values()->toArray(),
            ];
        }

        $workflowStages = Stage::where('section', $project->work_flow)
            ->whereNull('parent_id')
            ->orderBy('order_no')
            ->get();


        foreach ($workflowStages as $index => $stage) {

            $existingStage = collect($stageAnalytics)
                ->firstWhere('main_stage_id', $stage->id);

            $chartStages[] = [

                'stage_name' => $stage->name,

                // master stage percentage
                'percentage' => $stage->present ?? 0,

                'color' => $existingStage['color']
                    ?? $colors[$index % count($colors)],

            ];
        }

        return view('inventory::project.show', compact('project', 'stageAnalytics', 'chartStages'));
    }

    public function projectstage($id)
    {
        // PROJECT
        $project = Project::findOrFail($id);

        // WORK FLOW
        $workFlow = $project->work_flow;

        // =========================
        // MAIN STAGES
        // =========================
        $parentStages = Stage::where('section', $workFlow)
            ->whereNull('parent_id')
            ->orderBy('order_no')
            ->get();

        // =========================
        // SUB STAGES
        // =========================
        $subStages = Stage::whereIn(
            'parent_id',
            $parentStages->pluck('id')
        )
            ->orderBy('order_no')
            ->get();

        // =========================
        // MAIN STAGE STATUS (LATEST)
        // =========================
        $mainStageStatuses = ProjectMainStage::where('project_id', $project->id)
            ->latest()
            ->get()
            ->groupBy('main_stage_id');

        // =========================
        // SUB STAGE STATUS (LATEST)
        // =========================
        $subStageStatuses = ProjectSubStage::where('project_id', $project->id)
            ->latest()
            ->get()
            ->groupBy('sub_stage_id');

        // =========================
        // ATTACH STATUS TO MAIN STAGES
        // =========================
        foreach ($parentStages as $stage) {

            $latestMain = $mainStageStatuses[$stage->id][0] ?? null;

            $stage->current_status_id = $latestMain?->status_id;
        }

        // =========================
        // ATTACH STATUS TO SUB STAGES
        // =========================
        foreach ($subStages as $sub) {

            $latestSub = $subStageStatuses[$sub->id][0] ?? null;

            $sub->current_status_id = $latestSub?->status_id;
        }

        // STATUS MASTER
        $parentStatuses = StageStatus::where('type', 'parent')->orderBy('order_no')->get();

        $subStatuses = StageStatus::where('type', 'sub')->orderBy('order_no')->get();



        return view('inventory::project.project-stage', compact(
            'project',
            'parentStages',
            'subStages',
            'parentStatuses',
            'subStatuses'
        ));
    }

    public function updateFlow(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $project->flow_type = $request->flow_type;
        $project->save();

        return back()->with('success', 'Flow updated');
    }
}
