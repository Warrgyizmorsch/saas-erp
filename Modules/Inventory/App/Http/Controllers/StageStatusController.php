<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\StageStatus;
use Illuminate\Http\Request;

class StageStatusController extends Controller
{
    // LIST PAGE
    public function index()
    {
        $parentStatuses = StageStatus::where('type', 'parent')
            ->orderBy('order_no')
            ->get();

        $subStatuses = StageStatus::where('type', 'sub')
            ->orderBy('order_no')
            ->get();

        $editStatus = null;

        return view('inventory::project.stage-status.index',
            compact(
                'parentStatuses',
                'subStatuses',
                'editStatus'
            )
        );
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:parent,sub',
            'order_no' => 'nullable|integer'
        ]);

        StageStatus::create([
            'name' => trim($request->name),
            'type' => $request->type,
            'order_no' => $request->order_no ?? 1,
        ]);

        return redirect()
            ->route('stage-status.index')
            ->with('success', 'Status Added Successfully');
    }

    // EDIT
    public function edit($id)
    {
        $parentStatuses = StageStatus::where('type', 'parent')
            ->orderBy('order_no')
            ->get();

        $subStatuses = StageStatus::where('type', 'sub')
            ->orderBy('order_no')
            ->get();

        $editStatus = StageStatus::findOrFail($id);

        return view('inventory::project.stage-status.index',
            compact(
                'parentStatuses',
                'subStatuses',
                'editStatus'
            )
        );
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:parent,sub',
            'order_no' => 'nullable|integer'
        ]);

        $status = StageStatus::findOrFail($id);

        $status->update([
            'name' => trim($request->name),
            'type' => $request->type,
            'order_no' => $request->order_no ?? 1,
        ]);

        return redirect()
            ->route('stage-status.index')
            ->with('success', 'Status Updated Successfully');
    }

    // DELETE
    public function destroy($id)
    {
        StageStatus::findOrFail($id)->delete();

        return back()->with(
            'success',
            'Status Deleted Successfully'
        );
    }
}