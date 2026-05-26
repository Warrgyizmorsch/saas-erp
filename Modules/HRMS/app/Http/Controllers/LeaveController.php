<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\Employee;
use Modules\HRMS\App\Models\LeaveAllotment;
use Modules\HRMS\App\Models\Attendance;
use App\Exports\LeaveBalancesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index()
    {
        return redirect()->route('leave.allotment');
    }

    public function allotment(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->format('m'));
        $year = Carbon::now()->format('Y');
        $month = $selectedMonth;

        $user = auth()->user();
        $isAdmin = in_array($user->role_id, [1, 3]);
        $isTeamLeader = false;

        if ($isAdmin) {
            $employees = Employee::orderBy('name', 'asc')->get();
            $allotments = LeaveAllotment::where('month', $month)
                ->where('year', $year)
                ->get()
                ->keyBy('employee_id');

            $history = LeaveAllotment::with('employee')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($isTeamLeader) {
            $department = $user->employee->department ?? null;
            if ($department) {
                $employees = Employee::where('department', $department)->orderBy('name', 'asc')->get();
                $employeeIds = $employees->pluck('id');
                
                $allotments = LeaveAllotment::where('month', $month)
                    ->where('year', $year)
                    ->whereIn('employee_id', $employeeIds)
                    ->get()
                    ->keyBy('employee_id');

                $history = LeaveAllotment::with('employee')
                    ->whereIn('employee_id', $employeeIds)
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $employees = collect();
                $allotments = collect();
                $history = collect();
            }
        } else {
            $employee_id = $user->employee_id;
            $employees = Employee::where('id', $employee_id)->get();
            $allotments = LeaveAllotment::where('month', $month)
                ->where('year', $year)
                ->where('employee_id', $employee_id)
                ->get()
                ->keyBy('employee_id');

            $history = LeaveAllotment::with('employee')
                ->where('employee_id', $employee_id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($isTeamLeader) {
            $balances = $this->calculateBalances($employees);
            return view('hrms::leave.team_balance', compact('balances', 'selectedMonth'));
        }

        return view('hrms::leave.allotment', compact('employees', 'allotments', 'selectedMonth', 'history', 'isAdmin'));
    }

    public function storeAllotment(Request $request)
    {

        // echo "<pre>";print_r($request);exit;

        $isAdmin = in_array(auth()->user()->role_id, [1, 3]);
        if (!$isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $month = $request->input('month');
        $year = Carbon::now()->format('Y');

        $allotments = $request->input('allotments', []);
        $employeeIds = array_keys($allotments);

        // Remove allotments for employees who were removed from the list in UI
        LeaveAllotment::where('month', $month)
            ->where('year', $year)
            ->whereNotIn('employee_id', $employeeIds)
            ->delete();

        foreach ($allotments as $employeeId => $count) {
            LeaveAllotment::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'leave_count' => $count ?? 0,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Leaves allotted successfully']);
    }

    public function balanceList()
    {
        $balances = $this->calculateBalances();
        return view('hrms::leave.balance', compact('balances'));
    }

    public function apiBalanceList()
    {
        $balances = $this->calculateBalances();
        return response()->json($balances);
    }

    public function exportBalances()
    {
        $balances = $this->calculateBalances();
        $filename = "leave_balances_" . date('Y-m-d_H-i-s') . ".xlsx";
        return Excel::download(new LeaveBalancesExport($balances), $filename);
    }

    private function calculateBalances($employees = null)
    {
        $employees = $employees ?? Employee::orderBy('name', 'asc')->get();
        $balances = [];

        foreach ($employees as $employee) {
            $totalAllotted = LeaveAllotment::where('employee_id', $employee->id)->sum('leave_count');

            // Count ONLY from approved leave applications (NOT from Attendance table)
            $approvedLeaves = \Modules\HRMS\App\Models\LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->get();

            $totalTaken = 0;
            foreach ($approvedLeaves as $leave) {
                $cat = strtolower($leave->leave_category);

                // Gatepass does NOT count in balance
                if (str_contains($cat, 'gatepass')) {
                    continue;
                }

                // Half Day = 0.5
                if (str_contains($cat, 'half')) {
                    $totalTaken += 0.5;
                    continue;
                }

                // Full Day - calculate number of days (end date exclusive)
                $startDate = Carbon::parse($leave->start_date);
                $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : $startDate->copy();

                if ($startDate->equalTo($endDate)) {
                    $totalTaken += 1;
                } else {
                    $totalTaken += $startDate->diffInDays($endDate);
                }
            }

            $balances[] = (object) [
                'id' => $employee->id,
                'name' => $employee->name,
                'total_allotted' => $totalAllotted,
                'total_taken' => $totalTaken,
                'balance' => $totalAllotted - $totalTaken
            ];
        }
        return $balances;
    }
}
