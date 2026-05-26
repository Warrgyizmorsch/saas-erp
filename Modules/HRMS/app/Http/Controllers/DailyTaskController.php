<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\DailyTask;
use Modules\HRMS\App\Models\Project;
use Modules\HRMS\App\Models\Employee;
use Modules\HRMS\App\Models\TaskFollowUp;
use Modules\HRMS\App\Models\TaskStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyTask::with(['project', 'employee', 'creator', 'followUps']);

        // Data Restriction Logic
        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        // Ensure "OTHER" project exists for general tasks
        $otherProject = Project::where('name', 'OTHER')->first();
        if (!$otherProject) {
            $otherProject = Project::create([
                'name' => 'OTHER',
                'slug' => 'other',
                'status' => 'Ongoing',
                'description' => 'General tasks not related to a specific project',
            ]);
        }
        // Sync all employees to OTHER project so they all appear in the list
        $otherProject->update(['members' => Employee::pluck('id')->toArray()]);

        if ($role == 'team_leader') {

            $teamLeaderDepartment = auth()->user()->employee?->department ?? null;

            // Show tasks of employees from same department
            $departmentEmployeeIds = Employee::where('department', $teamLeaderDepartment)
                ->pluck('id');

            $query->whereIn('employee_id', $departmentEmployeeIds);

            // Employees dropdown
            $employees = Employee::where('department', $teamLeaderDepartment)->get();

            // Projects assigned to department employees
            $projects = Project::where(function ($q) use ($departmentEmployeeIds) {

                foreach ($departmentEmployeeIds as $employeeId) {
                    $q->orWhereJsonContains('members', (string) $employeeId);
                }

            })->orderBy('name')->get();

        } elseif (!$isAdmin) {
            // $query->where('employee_id', auth()->user()->employee_id);
            // $employees = Employee::where('id', auth()->user()->employee_id)->get();
            // $projects = Project::orderBy('name')->get();
            $employeeId = auth()->user()->employee_id;

            $query->where('employee_id', $employeeId);

            $employees = Employee::where('id', $employeeId)->get();

            // Show only assigned projects
            $projects = Project::whereJsonContains('members', (string) $employeeId)
                ->orderBy('name')
                ->get();

        } else {
            $projects = Project::orderBy('name')->get();
            $employees = Employee::orderBy('name')->get();
        }

        // Filtering
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->from_date) {
            $query->where('start_date', '>=', $request->from_date);
        }
        if ($request->upto_date) {
            $query->where('end_date', '<=', $request->upto_date);
        }

        $tasks = $query->latest()->get();

        return view('hrms::projects.tasks.index', compact('tasks', 'projects', 'employees', 'isAdmin'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|string',
            'status' => 'required|string',
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar|max:10240',
        ]);

        $validated['assigned_by'] = Auth::id();

        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        if (!$isAdmin) {
            $project = Project::find($validated['project_id']);
            $isLeader = $project && is_array($project->leaders) && in_array(auth()->user()->employee_id, $project->leaders);
            
            if (!$isLeader) {
                // If not admin and not project leader, they can only assign task to themselves
                $validated['employee_id'] = auth()->user()->employee_id;
            } else {
                // If leader, verify the assignee is in the project
                $allowed = array_merge((array)($project->leaders ?? []), (array)($project->members ?? []));
                if (!in_array($validated['employee_id'], $allowed)) {
                    return response()->json(['error' => 'You can only assign tasks to project members.'], 403);
                }
            }
        }

        if (isset($validated['end_date']) && $validated['end_date']) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('daily_tasks', 'public');
            $validated['photo'] = $path;
        }

        DailyTask::create($validated);

        return response()->json(['success' => 'Task created successfully!']);
    }

    public function update(Request $request, DailyTask $dailyTask)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|string',
            'status' => 'required|string',
            'employee_id' => 'required|exists:employees,id',
            'description' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar|max:10240',
        ]);

        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        $project = $dailyTask->project;
        $isLead = ($project && is_array($project->leaders) && in_array(auth()->user()->employee_id, $project->leaders));
        $isOwner = auth()->user()->employee_id == $dailyTask->employee_id;

        if (!$isAdmin && !$isLead && !$isOwner) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        if ($request->hasFile('photo')) {
            if ($dailyTask->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($dailyTask->photo);
            }
            $path = $request->file('photo')->store('daily_tasks', 'public');
            $validated['photo'] = $path;
        }

        if (isset($validated['end_date']) && $validated['end_date']) {
            $validated['end_date'] = \Carbon\Carbon::parse($validated['end_date'])->endOfDay();
        }

        $oldStatus = $dailyTask->status;
        $newStatus = $validated['status'];

        if (strcasecmp((string) $oldStatus, (string) $newStatus) !== 0) {
            $validated['status_changed_at'] = now();
        }

        $dailyTask->update($validated);

        return response()->json(['success' => 'Task updated successfully!']);
    }

    public function destroy(DailyTask $dailyTask)
    {
        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        $project = $dailyTask->project;
        $isLead = ($project && is_array($project->leaders) && in_array(auth()->user()->employee_id, $project->leaders));
        $isOwner = auth()->user()->employee_id == $dailyTask->employee_id;

        if (!$isAdmin && !$isLead && !$isOwner) {
            return back()->with('error', 'Unauthorized action.');
        }

        $dailyTask->delete();
        return back()->with('success', 'Task deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if ($ids && is_array($ids)) {
            DailyTask::whereIn('id', $ids)->delete();
            return response()->json(['success' => 'Tasks deleted successfully!']);
        }
        return response()->json(['error' => 'No tasks selected.'], 400);
    }

    public function storeFollowUp(Request $request)
    {
        $validated = $request->validate([
            'daily_task_id' => 'required|exists:daily_tasks,id',
            'work_description' => 'required|string',
            'reference_name' => 'nullable|string',
            'time_taken' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar|max:10240',
        ]);

        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        if (!$isAdmin) {
            $validated['reference_name'] = auth()->user()->name;
        }

        $task = DailyTask::with('employee')->find($validated['daily_task_id']);
        $validated['reference_name'] = $task->employee?->name ?? auth()->user()->name ?? 'Employee';

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('task_followups', 'public');
            $validated['photo'] = $path;
        }

        TaskFollowUp::create($validated);

        return response()->json(['success' => 'Reply submitted successfully!']);
    }

    public function updateFollowUp(Request $request, $id)
    {
        $followUp = TaskFollowUp::findOrFail($id);
        
        $validated = $request->validate([
            'work_description' => 'required|string',
            'time_taken' => 'nullable|string',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,pdf,doc,docx,xls,xlsx,csv,txt,zip,rar|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            if ($followUp->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($followUp->photo);
            }
            $path = $request->file('photo')->store('task_followups', 'public');
            $validated['photo'] = $path;
        }

        $followUp->update($validated);

        return response()->json(['success' => 'Task history updated successfully!']);
    }

    public function getFollowUps($taskId)
    {
        $followUps = TaskFollowUp::where('daily_task_id', $taskId)
            ->latest()
            ->get()
            ->map(function ($followUp) {
                $followUp->employee_name = $followUp->reference_name ?: 'Employee';
                $followUp->employee = null;
                return $followUp;
            });

        return response()->json($followUps);
    }

    public function destroyFollowUp($id)
    {
        $followUp = TaskFollowUp::findOrFail($id);
        if ($followUp->photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($followUp->photo);
        }
        $followUp->delete();
        return response()->json(['success' => 'Task history description deleted successfully!']);
    }
    public function updateStatus(Request $request, DailyTask $dailyTask)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Pending,In Process,Completed,On Hold,Review,Rework,Reassign', 'comment'=>'nullable|string', 'employee_id'=>'nullable'
        ]);

        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        $project = $dailyTask->project;
        $isLead = false;
        if ($project && is_array($project->leaders)) {
            $isLead = in_array(auth()->user()->employee_id, $project->leaders);
        }

        $isOwner = auth()->user()->employee_id == $dailyTask->employee_id;

        if (!$isAdmin && !$isLead && !$isOwner) {
            return response()->json(['error' => 'Only Admin, Project Lead or Task Owner can change status.'], 403);
        }

        $updateData = ['status' => $validated['status']];
        if($request->status=="Reassign" && $request->employee_id){
            $updateData['employee_id'] = $request->employee_id;
        }

        if (strcasecmp((string) $dailyTask->status, (string) $validated['status']) !== 0) {
            $updateData['status_changed_at'] = now();
        }

        TaskStatusHistory::create([
            'task_id'=>$dailyTask->id,

            'old_status'=>$dailyTask->status,

            'new_status'=>$request->status,

            'comment'=>$request->comment,

            'updated_by'=>auth()->id()
        ]);
        $dailyTask->update($updateData);

        return response()->json(['success' => 'Task status updated successfully!']);
    }

    public function statusHistory(DailyTask $task)
    {
        return response()->json(
            $task->statusHistory()
                ->with('user')
                ->latest()
                ->get()
        );
    }

    public function updatePriority(Request $request, DailyTask $dailyTask)
    {
        $validated = $request->validate([
            'priority' => 'required|string|in:Hard,Medium,Low,Normal',
        ]);

        $role = str_replace(' ', '_', strtolower(auth()->user()->hrm_role ?? 'employee'));
        $adminRoles = ['super_admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head', 'team_leader'];
        $isAdmin = in_array($role, $adminRoles);

        $project = $dailyTask->project;
        $isLead = ($project && is_array($project->leaders) && in_array(auth()->user()->employee_id, $project->leaders));
        $isOwner = auth()->user()->employee_id == $dailyTask->employee_id;

        if (!$isAdmin && !$isLead && !$isOwner) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $dailyTask->update(['priority' => $validated['priority']]);

        return response()->json(['success' => 'Task priority updated successfully!']);
    }
}
