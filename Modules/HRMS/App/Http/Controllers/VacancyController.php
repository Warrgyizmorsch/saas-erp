<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Department;
use Modules\HRMS\App\Models\Designation;
use Modules\HRMS\App\Models\Employee;
use Modules\HRMS\App\Models\JobVacancy;
use Modules\HRMS\App\Models\JobRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VacancyController extends Controller
{
    public function show(Request $request) {

        $departments = Department::select('id','name')->get();
        $designations = Designation::select('id', 'name')->get();
        $employees = Employee::select('id', 'name')->orderBy('name')->get();

        $selectedRole = $request->query('role');

        $applicationsQuery = JobVacancy::with(['department', 'interviewer']);

        $statsQuery = JobVacancy::query();

        if (!empty($selectedRole)) {
            $applicationsQuery->where('designation', $selectedRole);
            $statsQuery->where('designation', $selectedRole);
        }

        if (!empty($selectedRole)) {
            $applicationsQuery->where('designation', $selectedRole);
        }

        $applications = $applicationsQuery->latest()->get();

        $pendingCount  = (clone $statsQuery)->where('status', 'pending')->count();
        $awaitedCount  = (clone $statsQuery)->where('status', 'awaited')->count();
        $rejectedCount = (clone $statsQuery)->where('status', 'rejected')->count();
        $selectedCount = (clone $statsQuery)->where('status', 'selected')->count();

        return view('hrms::vacancy.index', compact('departments', 'designations', 'employees', 'applications', 'pendingCount', 'awaitedCount', 'rejectedCount', 'selectedCount', 'selectedRole'));
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


    public function showRequirements() {
        $roles = DB::table('designations')->get();

        $requirements = DB::table('job_requirements')
            ->leftJoin(
                'designations',
                'job_requirements.role_id',
                '=',
                'designations.id'
            )
            ->select(
                'job_requirements.*',
                'designations.name as role_name'
            )
            ->selectSub(function($query) {
                $query->from('job_applications')
                    ->whereColumn('job_applications.designation', 'designations.name')
                    ->selectRaw('count(*)');
            }, 'applications_count')
            ->latest()
            ->get();

        $departments = Department::select('id','name')->get();
        $designations = Designation::select('id', 'name')->get();
        $employees = Employee::select('id', 'name')->orderBy('name')->get();

        $applications = JobVacancy::with(['department', 'interviewer'])
            ->latest()
            ->get();

        return view('hrms::vacancy.job_requirement', compact('roles', 'requirements', 'departments', 'designations', 'employees', 'applications'));
    }

    public function storeRequirement(Request $request)
    {
        $request->validate([
            'role_id'=>'required',
            'priority'=>'required',
            'date'=>'required',
            'candidate_type'=>'required',
            'skills'=>'required'
        ]);
        JobRequirement::create([
            'role_id'=>$request->role_id,
            'priority'=>$request->priority,
            'date'=>$request->date,
            'candidate_type'=>$request->candidate_type,
            'minimum_experience'=>
                $request->candidate_type=="Experience"
                ? $request->minimum_experience
                : null,
            'skills' => array_map('trim', explode(',', $request->skills))
        ]);
        return back()->with('success', 'Saved Successfully');
    }

    public function updateStatusofRequirement(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:job_requirements,id',
            'status' => 'required|in:hold,hiring,hired'
        ]);

        $requirement = JobRequirement::findOrFail($request->id);
        $requirement->status = $request->status;
        $requirement->save();

        return response()->json(['success' => true]);
    }
}
