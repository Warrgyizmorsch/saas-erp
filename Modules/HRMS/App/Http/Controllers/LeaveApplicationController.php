<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Employee;
use Modules\HRMS\App\Models\LeaveApplication;
use Modules\HRMS\App\Models\Attendance;
use Modules\HRMS\App\Models\Holiday;
use App\Exports\LeaveApplicationsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveApplicationMail;
use App\Mail\LeaveStatusUpdatedMail;

class LeaveApplicationController extends Controller
{
    private function getHolidayDatesBetween(Carbon $startDate, Carbon $endDate): array
    {
        return Holiday::whereBetween('date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();
    }

    private function calculateWorkingLeaveDays(string $startDate, ?string $endDate = null): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate ?: $startDate)->startOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $holidayDates = $this->getHolidayDatesBetween($start, $end);
        $count = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isSunday()) {
                continue;
            }

            if (in_array($date->toDateString(), $holidayDates, true)) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    private function shouldSkipNonWorkingDays(LeaveApplication $leave): bool
    {
        $normalizedCategory = strtolower($leave->leave_category);
        $normalizedType = strtolower($leave->leave_type ?? '');

        return !str_contains($normalizedCategory, 'gatepass')
            && !str_contains($normalizedType, 'half');
    }

    private function applyCategoryFilter($query, string $category): void
    {
        $normalizedCategory = strtolower(trim($category));

        if ($normalizedCategory === 'early leave') {
            $query->where('leave_category', 'LIKE', '%Gatepass Leave%');
            return;
        }

        if ($normalizedCategory === 'wfh') {
            $query->where('leave_category', 'LIKE', '%wfh%');
            return;
        }

        $query->where('leave_category', 'LIKE', '%' . $category . '%');
    }

    private function getAttendanceStatusFromLeave(string $leaveCategory, ?string $leaveType = null): string
    {
        $normalizedCategory = strtolower($leaveCategory);
        $normalizedType = strtolower($leaveType ?? '');

        if (str_contains($normalizedType, 'half')) {
            return 'half_day';
        }

        if (str_contains($normalizedCategory, 'wfh')) {
            return 'wfh';
        }

        if (str_contains($normalizedCategory, 'gatepass')) {
            return 'early_leave';
        }

        return 'leave';
    }

    private function getAttendanceHoursFromLeave(string $leaveCategory, ?string $leaveType = null): int
    {
        $normalizedCategory = strtolower($leaveCategory);
        $normalizedType = strtolower($leaveType ?? '');

        if (str_contains($normalizedType, 'half')) {
            return 4;
        }

        if (str_contains($normalizedCategory, 'wfh')) {
            return 8;
        }

        return 0;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $role = str_replace(' ', '_', strtolower($user->hrm_role ?? 'employee'));
        $isAdmin = in_array($role, [
            'super_admin',
            'manager',
            'hr_executive',
            'hr_intern',
            'business_operation_head'
        ]);

        $isTeamLeader = in_array($role, [
            'team_leader'
        ]);

        $query = LeaveApplication::with('employee');

        // Search Filters
        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category')) {
            $this->applyCategoryFilter($query, $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('start_date', '<=', $request->to_date);
        }

        if ($isAdmin) {
            $employees = Employee::all();
        } elseif ($isTeamLeader) {
            $department = $user->employee->department ?? null;
            if ($department) {
                $query->whereHas('employee', function ($q) use ($department) {
                    $q->where('department', $department);
                });
                $employees = Employee::where('department', $department)->get();
            } else {
                $employees = collect();
            }
        } else {
            $query->where('employee_id', $user->employee_id);
            $employees = Employee::where('id', $user->employee_id)->get();
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);
        $holidays = Holiday::pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->values();

        return view('hrms::leave.history', compact('leaves', 'employees', 'holidays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'nullable|string',
            'leave_category' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'reason' => 'required',
        ]);

        $data = $request->only(['employee_id', 'leave_type', 'leave_category', 'start_date', 'end_date', 'reason', 'message', 'total_days', 'start_time', 'end_time']);
        $data['status'] = 'pending';

        // Ensure employee_id is set (fallback for non-admin users)
        $employeeId = $data['employee_id'] ?? auth()->user()->employee_id;
        if (empty($employeeId)) {
            return response()->json(['success' => false, 'message' => 'No employee linked.'], 403);
        }
        $data['employee_id'] = $employeeId;

        $employee = Employee::findOrFail($employeeId);
        if (auth()->user()->employee_id != $employee->id && !canManageEmployee(auth()->user(), $employee)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if ($request->leave_category === 'Gatepass Leave') {
            $data['leave_type'] = 'Early Leave';
            $data['total_days'] = 0.125; // 1 hour
        } elseif (str_contains(strtolower($request->leave_type ?? ''), 'half')) {
            $data['total_days'] = 0.5;
        } else {
            $data['total_days'] = $this->calculateWorkingLeaveDays(
                $request->start_date,
                $request->end_date ?? $request->start_date
            );
        }

        if ($request->leave_category === 'Gatepass Leave') {
            $data['end_date'] = $request->start_date;
            if ($request->filled('start_time')) {
                try {
                    $startTime = \Carbon\Carbon::parse($request->start_time);
                    $data['end_time'] = $startTime->copy()->addHour()->format('H:i');
                } catch (\Exception $e) {
                    // Fallback or ignore if time format is invalid
                }
            }
        } else {
            $data['end_date'] = $request->end_date ?? $request->start_date;
        }

        // LeaveApplication::create($data);
        $leave = LeaveApplication::create($data);

        Mail::to('mdkaif14104@gmail.com')
            ->send((new LeaveApplicationMail($leave, $employee))->replyTo($employee->email));

        return response()->json(['success' => true, 'message' => 'Leave application submitted successfully']);
    }

    public function export(Request $request)
    {
        $query = LeaveApplication::with('employee');

        if (auth()->user()->hrm_role == 'team_leader') {
            $query->whereHas('employee', function ($q) {
                $q->where('department', auth()->user()->employee?->department);
            });
        }
        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category')) {
            $this->applyCategoryFilter($query, $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('start_date', '<=', $request->to_date);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();
        $filename = "leave_applications_" . date('Y-m-d_H-i-s') . ".xlsx";

        return Excel::download(new LeaveApplicationsExport($leaves), $filename);
    }

    public function updateAction(Request $request)
    {
        $request->validate([
            'leave_id' => 'required|exists:leave_applications,id',
            'status' => 'required|in:pending,approved,rejected,on_hold,unauthorised,unpaid',
        ]);

        $leave = LeaveApplication::findOrFail($request->leave_id);
        $employee = Employee::findOrFail($leave->employee_id);

        if (!canManageEmployee(auth()->user(), $employee)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $oldStatus = $leave->status;
        $newStatus = $request->status;

        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate->copy();
            $holidayDates = $this->getHolidayDatesBetween($startDate, $endDate);

            if ($startDate->equalTo($endDate)) {
                $endDate->addDay();
            }

            for ($date = $startDate->copy(); $date->lt($endDate); $date->addDay()) {
                if (
                    $this->shouldSkipNonWorkingDays($leave)
                    && ($date->isSunday() || in_array($date->toDateString(), $holidayDates, true))
                ) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'attendance_date' => $date->format('Y-m-d')
                    ],
                    [
                        'status' => $this->getAttendanceStatusFromLeave(
                            $leave->leave_category,
                            $leave->leave_type
                        ),
                        'total_hours' => $this->getAttendanceHoursFromLeave(
                            $leave->leave_category,
                            $leave->leave_type
                        ),
                        'check_in' => null,
                        'check_out' => null
                    ]
                );
            }
        } elseif ($oldStatus === 'approved' && $newStatus !== 'approved') {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate->copy();

            if ($startDate->equalTo($endDate)) {
                $endDate->addDay();
            }

            Attendance::where('employee_id', $leave->employee_id)
                ->where('attendance_date', '>=', $startDate->format('Y-m-d'))
                ->where('attendance_date', '<', $endDate->format('Y-m-d'))
                ->whereIn('status', ['leave', 'half_day', 'wfh', 'early_leave'])
                ->delete();
        }

        $leave->update(['status' => $request->status]);

        $employee = Employee::find($leave->employee_id);

        if ($employee && $employee->email) {
            Mail::to($employee->email)
                ->send(new LeaveStatusUpdatedMail($leave, $employee));
        }

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function destroy($id)
    {
        $leave = LeaveApplication::findOrFail($id);
        $employee = Employee::findOrFail($leave->employee_id);

        if (!canManageEmployee(auth()->user(), $employee)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $leave->delete();
        return response()->json(['success' => true, 'message' => 'Leave application deleted']);
    }

    public function getDetails($id)
    {
        $leave = LeaveApplication::with('employee')->findOrFail($id);
        $employee = $leave->employee;

        if ($employee && auth()->user()->employee_id != $employee->id && !canManageEmployee(auth()->user(), $employee)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        return response()->json($leave);
    }

    public function getEmployeeLeaves($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        if (auth()->user()->employee_id != $employee->id && !canManageEmployee(auth()->user(), $employee)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $leaves = LeaveApplication::where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->get();
        return response()->json($leaves);
    }
}
