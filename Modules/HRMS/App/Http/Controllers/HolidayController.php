<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
 public function index(Request $request)
{
    $search = $request->search;

    // USER SELECT
    $perPage = $request->show ?? 10;

    // LIMIT FIX (MAX 20)
    if ($perPage > 20) {
        $perPage = 20;
    }

    $holidays = Holiday::when($search, function ($q) use ($search) {
        $q->where('title', 'like', "%$search%");
    })
    ->orderBy('date', 'desc')
    ->paginate($perPage)
    ->withQueryString();

    return view('hrms::holidays.index', compact('holidays'));
}

    public function store(Request $request)
{
    Holiday::create([
        'title' => strtoupper($request->title),
        'date' => $request->date,
    ]);

    return back()->with('success', 'Holiday added');
}
    public function edit($id)
{
    $holiday = Holiday::findOrFail($id);
    return view('hrms::holidays.edit', compact('holiday'));
}

public function update(Request $request, $id)
{
    $holiday = Holiday::findOrFail($id);

    $holiday->update([
        'title' => strtoupper($request->title),
        'date' => $request->date,
    ]);

    return redirect()->route('holidays.index')->with('success', 'Updated');
}

public function destroy($id)
{
    Holiday::findOrFail($id)->delete();
    return redirect()->route('holidays.index')->with('success', 'Deleted');
}
}