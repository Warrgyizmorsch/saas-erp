<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\LeadQuestion;
use Illuminate\Http\Request;

class LeadQuestionController extends Controller
{
    public function index()
    {
        $questions = LeadQuestion::orderBy('id', 'desc')->paginate(10);
        return view('crm.lead_questions.index', compact('questions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        LeadQuestion::create([
            'field_name' => $request->field_name,
            'label' => $request->label,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('lead_questions.index')->with('success', 'Question added successfully.');
    }

    public function update(Request $request, LeadQuestion $question)
    {
        $request->validate([
            'field_name' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $question->update([
            'field_name' => $request->field_name,
            'label' => $request->label,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('lead_questions.index')->with('success', 'Question updated successfully.');
    }

    public function destroy(LeadQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Question deleted!');
    }

    public function toggle(LeadQuestion $question)
    {
        $question->update(['is_active' => !$question->is_active]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $question->is_active,
            ]);
        }

        return back()->with('success', 'Question status updated!');
    }

}


