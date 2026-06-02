<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\ProjectMainStage;
use Modules\Inventory\App\Models\ProjectSubStage;
use Modules\Inventory\App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StageController extends Controller
{

    // LIST PAGE
   public function index(Request $request)
{
    // all workflows names
    $workflows = Stage::select('section')
        ->whereNotNull('section')
        ->distinct()
        ->pluck('section');

    $stages = Stage::whereNull('parent_id')
        ->with('children')

        ->when($request->workflow, function ($q) use ($request) {

            $q->where('section', $request->workflow);

        })

        ->orderBy('order_no')
        ->get();

    return view('inventory::project.stages.index', compact('stages', 'workflows'));
}

    // CREATE FORM
    public function create()
    {
        $stages = collect();

        $sections = Stage::whereNotNull('section')
            ->distinct()
            ->pluck('section');


        return view('inventory::project.stages.form', compact('stages', 'sections'));
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'stages' => 'required|array',
            'section' => 'required|string'
        ]);

        // ===========================
        // MAIN TOTAL CHECK
        // ===========================
        $mainTotal = collect($request->stages)->sum(function ($stage) {

            return (float) ($stage['present'] ?? 0);
        });

        if ($mainTotal != 100) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'All Main Stage Present % total must be exactly 100%'
                );
        }

        // ===========================
        // UNIQUE MAIN STAGE NAME CHECK
        // ===========================
        $mainNames = collect($request->stages)
            ->pluck('name')
            ->map(function ($name) {

                return strtolower(trim($name));
            });

        if ($mainNames->count() != $mainNames->unique()->count()) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Duplicate Main Stage names are not allowed'
                );
        }

        // ===========================
        // VALIDATE EACH STAGE
        // ===========================
        foreach ($request->stages as $stageData) {

            $subStages = collect($stageData['sub_stages'] ?? []);

            // REMOVE EMPTY ROWS
            $filteredSubStages = $subStages->filter(function ($sub) {

                return !empty(trim($sub['name'] ?? '')) ||
                    $sub['present'] !== null;
            });

            // ===========================
            // SUB TOTAL
            // ===========================
            $subTotal = $filteredSubStages->sum(function ($sub) {

                return (float) ($sub['present'] ?? 0);
            });

            // ===========================
            // IF SUB EXISTS
            // TOTAL MUST BE 100
            // ===========================
            if ($filteredSubStages->count() > 0 && $subTotal != 100) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        $stageData['name'] . ' sub stage total must be exactly 100%'
                    );
            }

            // ===========================
            // UNIQUE SUB STAGE NAME CHECK
            // ===========================
            $subNames = $filteredSubStages
                ->pluck('name')
                ->map(function ($name) {

                    return strtolower(trim($name));
                });

            if ($subNames->count() != $subNames->unique()->count()) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Duplicate sub stage names found inside "' . $stageData['name'] . '"'
                    );
            }
        }

        // ===========================
        // SAVE DATA
        // ===========================
        // ===========================
        // SAVE DATA
        // ===========================
        DB::beginTransaction();

        try {

            // =========================================
            // DELETE ONLY CURRENT SECTION OLD DATA
            // =========================================

            // $oldMainStages = Stage::where('section', $request->section)
            //     ->whereNull('parent_id')
            //     ->pluck('id');

            // // DELETE CHILD FIRST
            // Stage::whereIn('parent_id', $oldMainStages)->delete();

            // // DELETE MAIN
            // Stage::whereIn('id', $oldMainStages)->delete();

            // =========================================
            // SAVE NEW DATA
            // =========================================

            foreach ($request->stages as $stageData) {

                $mainStage = Stage::updateOrCreate(
                    ['id' => $stageData['id'] ?? null],
                    [
                        'name'      => trim($stageData['name']),
                        'present'   => $stageData['present'],
                        'order_no'  => $stageData['order_no'],
                        'parent_id' => null,
                        'section'   => $request->section,
                    ]
                );

                if (isset($stageData['sub_stages'])) {

                    foreach ($stageData['sub_stages'] as $sub) {

                        if (
                            empty(trim($sub['name'] ?? '')) &&
                            (
                                ($sub['present'] ?? null) === null ||
                                ($sub['present'] ?? '') === ''
                            )
                        ) {
                            continue;
                        }

                        Stage::updateOrCreate(
                            ['id' => $sub['id'] ?? null],
                            [
                                'name'      => trim($sub['name']),
                                'present'   => $sub['present'],
                                'order_no'  => $sub['order_no'] ?? 1,
                                'parent_id' => $mainStage->id,
                                'section'   => $request->section,
                            ]
                        );
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('stages.index')
                ->with('success', 'Stages Saved Successfully');
        } catch (\Exception $e) {

            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
    // EDIT PAGE
    public function edit()
    {
        $stages = Stage::whereNull('parent_id')
            ->with('children')
            ->orderBy('order_no')
            ->get();

        return view('inventory::project.stages.form', compact('stages'));
    }

    // UPDATE
    public function update(Request $request)
    {
        return $this->store($request);
    }

    // DELETE
    public function destroy($id)
    {

        $stage = Stage::findOrFail($id);

        Stage::where('parent_id', $id)->delete();

        $stage->delete();

        return back()->with(
            'success',
            'Stage Deleted Successfully'
        );
    }
    public function getBySection(Request $request)
    {
        $section = $request->section;

        $stages = Stage::where('section', $section)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order_no')
            ->get();

        return response()->json($stages);
    }

    public function updateParentStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $PARENT_DONE_ID = 6;
            $SUB_DONE_ID = 7;

            // =========================================
            // IF MAIN STAGE COMPLETED
            // CHECK ALL SUB STAGES COMPLETED OR NOT
            // =========================================
            if ($request->parent_status_id == $PARENT_DONE_ID) {

                $masterSubStages = Stage::where('parent_id', $request->parent_stage_id)
                    ->orderBy('order_no')
                    ->get();

                if ($masterSubStages->count() > 0) {

                    foreach ($masterSubStages as $subStage) {

                        $lastSubEntry = ProjectSubStage::where('project_id', $request->project_id)
                            ->where('sub_stage_id', $subStage->id)
                            ->orderBy('id', 'desc')
                            ->first();

                        if (!$lastSubEntry || $lastSubEntry->status_id != $SUB_DONE_ID) {

                            DB::rollBack();

                            return response()->json([
                                'success' => false,
                                'message' => 'Please complete all sub stages before completing this main stage.'
                            ]);
                        }
                    }
                }
            }

            // =========================================
            // MAIN STAGE STATUS CREATE
            // =========================================
            $mainStage = ProjectMainStage::create([
                'main_stage_id' => $request->parent_stage_id,
                'status_id' => $request->parent_status_id,
                'project_id' => $request->project_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stage status updated successfully'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('updateMainStatus error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    // ==========================
    // UPDATE SUB STAGE
    // ==========================

    public function updateSubStatus(Request $request)
    {

        try {
            DB::beginTransaction();

            // =========================
            // MAIN STAGE
            // =========================
            $mainStage = ProjectMainStage::where('main_stage_id', $request->parent_stage_id)
                ->where('project_id', $request->project_id)
                ->latest()
                ->first();

            // CASE 1: NOT EXISTS → CREATE
            if (!$mainStage) {

                $mainStage = ProjectMainStage::create([
                    'main_stage_id' => $request->parent_stage_id,
                    'status_id'     => $request->parent_status_id,
                    'project_id'    => $request->project_id,
                    'created_by'    => auth()->id(),
                ]);
            }

            // CASE 2: EXISTS BUT STATUS DIFFERENT → CREATE NEW
            elseif ($mainStage->status_id != $request->parent_status_id) {

                $mainStage = ProjectMainStage::create([
                    'main_stage_id' => $request->parent_stage_id,
                    'status_id'     => $request->parent_status_id,
                    'project_id'    => $request->project_id,
                    'created_by'    => auth()->id(),
                ]);
            }

            // =========================
            // SUB STAGE
            // =========================
            ProjectSubStage::create([
                'project_id' => $request->project_id,
                'project_main_stage_id' => $mainStage->id,
                'sub_stage_id' => $request->sub_stage_id,
                'status_id' => $request->sub_status_id,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'main_stage_id' => $mainStage->id
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            // log error for backend debugging
            Log::error('updateSubStatus error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'req'  => $request->all(),

                'line' => $e->getLine(),
            ], 500);
        }
    }
}
