<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Department;
use Modules\HRMS\App\Models\Designation;
use Modules\HRMS\App\Models\Employee;
use Modules\HRMS\App\Models\JobVacancy;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function show() {
        $departments = Department::select('id','name')->get();
        $designations = Designation::select('id', 'name')->get();
        $employees = Employee::whereIn('role', [ 'super_admin', 'manager', 'hr-executive', 'team_leader'])
                ->select('id','name')
                ->get();

        $applications = JobVacancy::with(['department', 'interviewer'])
            ->latest()
            ->get();

        $pendingCount = JobVacancy::where('status', 'pending')->count();
        $awaitedCount = JobVacancy::where('status', 'awaited')->count();
        $rejectedCount = JobVacancy::where('status', 'rejected')->count();
        $selectedCount = JobVacancy::where('status', 'selected')->count();

        return view('hrms::vacancy.index', compact('departments', 'designations', 'employees', 'applications', 'pendingCount', 'awaitedCount', 'rejectedCount', 'selectedCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'resume' => 'required|mimes:pdf,doc,docx|max:2048'
        ]);

        $data = $request->all();

        if($request->hasFile('resume')){
            $data['resume'] =
            $request->file('resume')
            ->store('resumes','public');
        }

        JobVacancy::create($data);

        return back()
            ->with('success',
            'Candidate saved successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $app = JobVacancy::findOrFail($id);
        $app->status = $request->status;
        $app->save();

        return back()->with('success', 'Status updated successfully');
    }
}
