<?php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Issue;
use Modules\Inventory\App\Models\JobCard;
use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\PurchaseOrder;
use Modules\Inventory\App\Models\PurchaseRequest;
use Modules\Inventory\App\Models\RequestSlip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        // ✅ Date Range: Default = Current Month (for all counts/lists)
        $from = $request->filled('from_date')
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('to_date')
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        // =========================
        // COUNTS (date filtered where needed)
        // =========================
        $data['rs'] = RequestSlip::whereBetween('created_on', [$from, $to])->count();
        $data['rejected_rs'] = RequestSlip::where('status', 'Rejected')->whereBetween('created_on', [$from, $to])->count();
        $data['hold_rs'] = RequestSlip::where('status', 'Hold')->whereBetween('created_on', [$from, $to])->count();
        $data['pending_rs'] = RequestSlip::where('status', 'Pending')->whereBetween('created_on', [$from, $to])->count();
        $data['approved_rs'] = RequestSlip::where('status', 'Approved')->whereBetween('created_on', [$from, $to])->count();
        $data['exceed_rs'] = RequestSlip::where('is_exited' , '1')->whereBetween('created_on', [$from, $to])->count();


        $data['issue'] = Issue::whereBetween('created_on', [$from, $to])->count();

        $data['completed_issued'] = Issue::where('status', 'Issued')
            ->whereBetween('created_on', [$from, $to])
            ->count();
        $data['partially_issue'] = Issue::where('status', 'Partially Issued')
            ->whereBetween('created_on', [$from, $to])
            ->count();

        $data['total_job_cart'] = JobCard::whereBetween('created_at', [$from, $to])->count();

        $data['completed_job_cart'] = JobCard::where('status', 'COMPLETED')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['Pending_job_card'] = JobCard::where('status', 'PENDING')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['total_po'] = PurchaseOrder::whereBetween('created_at', [$from, $to])->count();

        $data['completed_po'] = PurchaseOrder::where('status', 'Completed')
            ->whereBetween('created_at', [$from, $to])
            ->count();

         $data['completed_draft'] = PurchaseOrder::where('status', 'Draft')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['completed_apv'] = PurchaseOrder::where('status', 'Approved')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        
          $data['completed_submitted'] = PurchaseOrder::where('status', 'Submitted')
            ->whereBetween('created_at', [$from, $to])
            ->count();

         $data['po_pr'] = PurchaseOrder::where('status', 'Partially Received')
        ->whereBetween('created_at', [$from, $to])
        ->count();


        // =========================
        // RECENT LISTS (date filtered)
        // =========================
        $data['recent_rs_existed'] = RequestSlip::with(['project', 'creator'])
            ->where('status', 'Pending')
            ->where('is_exited', '1')
            ->whereBetween('created_on', [$from, $to])
            ->orderBy('created_on', 'desc')
            ->take(5)
            ->get();

        $data['recent_pending_po'] = PurchaseOrder::with('creator')
            ->where('status', 'Submitted')
            ->whereNotNull('created_by')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $data['recent_rs'] = RequestSlip::with(['project', 'creator'])
            ->where('is_exited', '0')
            ->whereBetween('created_on', [$from, $to])
            ->orderBy('created_on', 'desc')
            ->take(5)
            ->get();

        $data['recent_issues'] = Issue::with(['project', 'creator'])
            ->whereBetween('created_on', [$from, $to])
            ->orderBy('created_on', 'desc')
            ->take(5)
            ->get();

        $data['recent_po'] = PurchaseOrder::with('creator')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $data['recent_pr'] = PurchaseRequest::with('creator')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // =========================
        // PURCHASE ORDER SUMMARY (date filtered)
        // =========================
        $poSummaryStatuses = ['Approved', 'Partially Received', 'Completed'];

        $data['po_draft_count'] = PurchaseOrder::where('status', 'Draft')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['po_submitted_count'] = PurchaseOrder::where('status', 'Submitted')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['po_approved_count'] = PurchaseOrder::where('status', 'Approved')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $data['total_purchase_amount'] = PurchaseOrder::whereIn('status', $poSummaryStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        $data['paid_amount'] = PurchaseOrder::whereIn('status', $poSummaryStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->sum('advance_amount');

        $data['pending_amount'] = max(0, $data['total_purchase_amount'] - $data['paid_amount']);

        // =========================
        // PO PENDING CHART (selected range, balance > 0)
        // =========================
        $pendingPos = PurchaseOrder::whereBetween('created_at', [$from, $to])
            ->whereIn('status', $poSummaryStatuses)
            ->whereRaw('(total_amount - advance_amount) > 0')
            ->orderBy('created_at', 'asc')
            ->get(['po_number', 'total_amount', 'advance_amount', 'created_at']);

        $data['po_chart_labels'] = $pendingPos->pluck('po_number')->toArray();

        $data['po_chart_values'] = $pendingPos->map(function ($po) {
            return max(0, (float) $po->total_amount - (float) $po->advance_amount);
        })->toArray();

        $data['po_chart_meta'] = $pendingPos->map(function ($po) {
            $balance = max(0, (float) $po->total_amount - (float) $po->advance_amount);
            return [
                'po_number' => $po->po_number,
                'total'     => (float) $po->total_amount,
                'paid'      => (float) $po->advance_amount,
                'balance'   => (float) $balance,
                'date'      => optional($po->created_at)->format('d M Y'),
            ];
        })->toArray();

        // =========================
        // PROJECT PROGRESS (same as yours)
        // =========================
        $statusPercent = [
            'Fabrication' => 25,
            'Assembly'    => 50,
            'Machining'   => 75,
            'Completed'   => 100,
        ];

        $projects = Project::with(['user', 'projectProducts'])
            ->whereRaw("LOWER(status) != 'completed'")
            ->latest()
            ->take(4)
            ->get();

        $data['project_progress'] = $projects->map(function ($project) use ($statusPercent) {
            $machines = collect($project->projectProducts ?? []);

            $progress = $machines->count()
                ? round($machines->map(fn ($m) => $statusPercent[$m->status] ?? 0)->avg())
                : 0;

            return [
                'id'       =>$project->id,
                'name'     => $project->name,
                'user'     => ['name' => $project->user?->name ?? 'Unknown'],
                'progress' => $progress,
            ];
        })->toArray();

        // =========================
        // ✅ PAYMENT RECORD CHART (Projects Created vs Completed) - Last 4 months
        // =========================

        // ✅ if filter is applied, use that range; otherwise last 4 months (current + prev 3)
        $chartFrom = ($request->filled('from_date') || $request->filled('to_date'))
            ? $from->copy()->startOfMonth()->startOfDay()
            : Carbon::now()->subMonths(3)->startOfMonth()->startOfDay();

        $chartTo = ($request->filled('from_date') || $request->filled('to_date'))
            ? $to->copy()->endOfMonth()->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        // month buckets (labels + keys)
        $labels = [];
        $monthKeys = [];
        $c = $chartFrom->copy()->startOfMonth();
        $end = $chartTo->copy()->startOfMonth();

        while ($c <= $end) {
            $labels[] = strtoupper($c->format('M/y')); // JAN/26
            $monthKeys[] = $c->format('Y-m');         // 2026-01
            $c->addMonth();
        }

        // Created projects per month (created_at)
        $createdRaw = Project::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereBetween('created_at', [$chartFrom, $chartTo])
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Completed projects per month (status in Complete/Completed + updated_at month)
        $completedRaw = Project::selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as ym, COUNT(*) as total")
            ->whereRaw("LOWER(status) IN ('complete','completed')")
            ->whereBetween('updated_at', [$chartFrom, $chartTo])
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        // Align arrays (0 fill)
        $createdData = [];
        $completedData = [];
        foreach ($monthKeys as $k) {
            $createdData[] = (int) ($createdRaw[$k] ?? 0);
            $completedData[] = (int) ($completedRaw[$k] ?? 0);
        }

        // ✅ send to view in the SAME format your JS expects
        $data['payChartCategories'] = $labels;
        $data['payChartSeries'] = [
            [
                'name' => 'Projects Created',
                'type' => 'bar',
                'data' => $createdData,
            ],
            [
                'name' => 'Projects Completed',
                'type' => 'bar',
                'data' => $completedData,
            ],
        ];

        // ✅ send selected dates back to view (optional)
        $data['from_date'] = $from->format('Y-m-d');
        $data['to_date']   = $to->format('Y-m-d');
        $last6Start = Carbon::now()->subMonths(5)->startOfMonth()->startOfDay(); // current + prev 5 = 6 months
        $last6End   = Carbon::now()->endOfMonth()->endOfDay();

        $data['projects_created_last6'] = Project::whereBetween('created_at', [$last6Start, $last6End])->count();

        $data['projects_completed_last6'] = Project::whereRaw("LOWER(status) IN ('complete','completed')")
            ->whereBetween('updated_at', [$last6Start, $last6End])
            ->count();

        return view('inventory::dashboard', $data);
    }


  
}
