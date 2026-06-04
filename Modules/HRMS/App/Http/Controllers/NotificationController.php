<?php

namespace Modules\HRMS\App\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\HRMS\App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Modules\HRMS\App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        // $role = strtoupper(auth()->user()->hrm_role);
        $notifications = [];

        $roleSlug = strtolower(auth()->user()->hrm_role); // e.g. "manager"
        $isAdmin = in_array($roleSlug, ['super_admin', 'admin', 'manager', 'hr_executive', 'hr_intern', 'business_operation_head']);

        // if ($role == 'ADMIN' || $role == 'SUPER ADMIN') {
        if ($isAdmin) {
            // $notifications = LeaveApplication::with('employee')
            //     ->whereIn('status', ['pending', 'Pending'])
            //     ->latest()
            //     ->paginate(20);

            $leaveNotifications = LeaveApplication::with('employee')
                ->latest()
                ->get();

            $payrollNotifications = Payroll::with('employee')
                ->whereNotNull('remarks')
                ->where('remarks', '!=', '')
                ->latest()
                ->get();

            $notifications = $leaveNotifications
                ->concat($payrollNotifications)
                ->sortByDesc(function ($item) {
                    return isset($item->remarks) 
                        ? $item->updated_at 
                        : $item->created_at;
                });
                
        } else {
            $notifications = LeaveApplication::where('employee_id', auth()->user()->employee_id)
                ->whereIn('status', ['approved', 'rejected', 'Approved', 'Rejected'])
                ->where('updated_at', '>=', now()->subDays(30)) // Show more history on the full page
                ->latest()
                ->paginate(20);
        }

        return view('hrms::notifications.index', compact('notifications', 'isAdmin'));
    }
}
