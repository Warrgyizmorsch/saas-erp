<?php

namespace Modules\CRM\App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CRM\App\Models\Bucket;
use Modules\CRM\App\Models\Leads;
use Modules\Shared\App\Models\User;
use Modules\CRM\App\Models\CallBack;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $loggedInRole = $user->role ?? \Modules\Shared\App\Models\Role::find($user->role_id);
        $loggedInLevel = $loggedInRole ? $loggedInRole->authority_level : 0;

        $allowedUserIdsQuery = \App\Models\User::where('is_deleted', 0);
        if ($user->role_id !== 1) {
            $allowedUserIdsQuery->where(function ($q) use ($user, $loggedInLevel) {
                $q->where('id', $user->id)
                  ->orWhereHas('role', function ($qr) use ($loggedInLevel) {
                      $qr->where('authority_level', '<', $loggedInLevel);
                  });
            });
        }
        $allowedUserIds = $allowedUserIdsQuery->pluck('id');

        $leadQuery = Leads::query();
        $totalLeads = Leads::count();

        if ($user->role_id !== 1) {
            $leadQuery->whereIn('lead_owner', $allowedUserIds);
            $totalLeads = Leads::whereIn('lead_owner', $allowedUserIds)->count();
        }

        // Handle date filtering
        $startInput = $request->input('start');
        $endInput = $request->input('end');
        $filterStart = null;
        $filterEnd = null;

        if ($startInput || $endInput) {
            if ($startInput && $endInput) {
                $filterStart = Carbon::parse($startInput)->startOfDay();
                $filterEnd = Carbon::parse($endInput)->endOfDay();
            } elseif ($startInput) {
                $filterStart = Carbon::parse($startInput)->startOfDay();
                $filterEnd = $filterStart->copy()->endOfDay();
            } elseif ($endInput) {
                $filterStart = Carbon::parse($endInput)->startOfDay();
                $filterEnd = $filterStart->copy()->endOfDay();
            }

            $leadQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
            $totalLeads = (clone $leadQuery)->count();
        }

        $buckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->get();

        foreach ($buckets as $bucket) {
            $bucket->total_leads = (clone $leadQuery)
                ->where('lead_bucket_id', $bucket->id)
                ->count();
        }

        $firstBucket = Bucket::where('name', 'Application')
            ->whereNull('parent_id')
            ->where('is_deleted', 0)
            ->with([
                'children' => function ($q) {
                    $q->where('is_deleted', 0);
                }
            ])
            ->first();

        $statusCounts = [];

        if ($firstBucket) {
            $baseQuery = (clone $leadQuery)->where('lead_bucket_id', $firstBucket->id);

            // Count each child status (match by name)
            foreach ($firstBucket->children as $child) {
                $count = (clone $baseQuery)->where('lead_status', $child->name)->count();
                $statusCounts[$child->id] = $count;
            }

            // Optional: count leads with status not in any child bucket
            $knownStatuses = $firstBucket->children->pluck('name')->all();

            $otherCount = (clone $baseQuery)
                ->whereNotIn('lead_status', $knownStatuses)
                ->whereNotNull('lead_status')
                ->where('lead_status', '!=', '')
                ->count();

            if ($otherCount > 0) {
                $statusCounts['other'] = $otherCount;
            }

            // Optional: count leads with no status at all
            $noStatusCount = (clone $baseQuery)
                ->where(function ($q) {
                    $q->whereNull('lead_status')->orWhere('lead_status', '');
                })->count();

            if ($noStatusCount > 0) {
                $statusCounts['no_status'] = $noStatusCount;
            }
        }

        $engagementCounts = [
            'hot' => 0,
            'warm' => 0,
            'cold' => 0,
            'dead' => 0,
        ];

        $engagementBaseQuery = clone $leadQuery;

        foreach (array_keys($engagementCounts) as $status) {
            $engagementCounts[$status] = (clone $engagementBaseQuery)
                ->where('lead_engagement_status', $status)
                ->count();
        }

        $totalEngagement = $totalLeads ?: 1; // prevent division by zero

        $engagementPercentages = [
            'hot' => round(($engagementCounts['hot'] / $totalEngagement) * 100, 1),
            'warm' => round(($engagementCounts['warm'] / $totalEngagement) * 100, 1),
            'cold' => round(($engagementCounts['cold'] / $totalEngagement) * 100, 1),
            'dead' => round(($engagementCounts['dead'] / $totalEngagement) * 100, 1),
        ];

        // recent leads based on the comment        
        $currentUser = Auth::user();

        $recentLeadsQuery = Leads::with([
            'user',                 // lead user
            'owner',                // lead owner
            'bucket',               // lead bucket
            'latestMessage.user'    // last message + sender
        ])
            ->whereHas('latestMessage'); // only leads having messages
        // ->whereNotNull('lead_owner');

        // Role Based Filtering
        if ($currentUser->role_id == 1) {
            // Admin → see all
        } else {
            $recentLeadsQuery->whereIn('lead_owner', $allowedUserIds);
        }

        // Apply date filter if present
        if ($filterStart && $filterEnd) {
            $recentLeadsQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        $recentLeads = $recentLeadsQuery
            ->orderByDesc(
                CallBack::select('created_at')
                    ->whereColumn('callback_messages.lead_id', 'leads.id')
                    ->latest()
                    ->take(1)
            )
            ->limit(10)
            ->get();


        // ─────────────────────────────────────────────
        // SALES PERFORMANCE (Updated Conversion Logic)
        // ─────────────────────────────────────────────


        // 1️⃣ Get all parent buckets
        $salesParentBuckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->get();

        // 2️⃣ Get conversion parent bucket IDs
        $conversionParentBuckets = Bucket::whereIn('name', [
            'Payment',
            'CAS',
            'Visa',
            'Enrollment'
        ])
            ->whereNull('parent_id')
            ->pluck('id')
            ->toArray();


        // 3️⃣ Role Based User Filtering
        $currentUser = Auth::user();

        if ($currentUser->role_id == 1) {
            // Admin → all sales users
            $salesUsersList = User::whereNotIn('role_id', [1, 2])->get();
        } else {
            $salesUsersList = User::whereIn('id', $allowedUserIds)
                ->whereNotIn('role_id', [1, 2])
                ->get();
        }


        // 4️⃣ Grouped Lead Data (Bucket Distribution)
        $salesGroupedLeadsQuery = Leads::select(
            'lead_owner',
            'lead_bucket_id',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('lead_owner');

        if ($filterStart && $filterEnd) {
            $salesGroupedLeadsQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        $salesGroupedLeads = $salesGroupedLeadsQuery
            ->groupBy('lead_owner', 'lead_bucket_id')
            ->get()
            ->groupBy('lead_owner');


        // 5️⃣ Grouped Lead Data (Conversion Based On 3 Parent Buckets)
        $conversionIdsString = empty($conversionParentBuckets) ? '0' : implode(',', $conversionParentBuckets);

        $salesConversionGroupedQuery = Leads::select(
            'lead_owner',
            DB::raw('COUNT(*) as total_leads'),
            DB::raw("
                    SUM(
                        CASE 
                            WHEN lead_bucket_id IN (" . $conversionIdsString . ")
                            THEN 1 
                            ELSE 0 
                        END
                    ) as converted
                ")
        )
            ->whereNotNull('lead_owner');

        if ($filterStart && $filterEnd) {
            $salesConversionGroupedQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        $salesConversionGrouped = $salesConversionGroupedQuery
            ->groupBy('lead_owner')
            ->get()
            ->keyBy('lead_owner');


        // 6️⃣ Blue Gradient Palette
        $blueShades = [
            '#0b3d91',
            '#0d47a1',
            '#1565c0',
            '#1976d2',
            '#1e88e5',
            '#2196f3',
            '#42a5f5',
            '#64b5f6',
            '#90caf9'
        ];

        $salesUserPerformance = [];

        foreach ($salesUsersList as $salesUser) {

            $userBucketData = $salesGroupedLeads->get($salesUser->id, collect());
            $conversionData = $salesConversionGrouped->get($salesUser->id);

            $userTotalLeads = $conversionData->total_leads ?? 0;
            $convertedLeads = $conversionData->converted ?? 0;

            // 🚫 Skip user if no leads
            if ($userTotalLeads == 0) {
                continue;
            }

            $conversionRate = round(($convertedLeads / $userTotalLeads) * 100, 1);

            $donutSeries = [];
            $donutLabels = [];
            $donutColors = [];

            foreach ($salesParentBuckets as $index => $parentBucket) {

                $bucketCount = $userBucketData
                    ->where('lead_bucket_id', $parentBucket->id)
                    ->sum('total');

                $donutSeries[] = $bucketCount;
                $donutLabels[] = $parentBucket->name;
                $donutColors[] = $blueShades[$index % count($blueShades)];
            }

            $salesUserPerformance[] = [
                'user' => $salesUser,
                'series' => $donutSeries,
                'labels' => $donutLabels,
                'colors' => $donutColors,
                'conversion_rate' => $conversionRate,
                'converted' => $convertedLeads,
                'total_leads' => $userTotalLeads,
            ];
        }

        // ─────────────────────────────────────────────
        // RECENT LEADS PROGRESS SECTION
        // ─────────────────────────────────────────────

        // 🎯 Fixed Main Stage Order
        // $mainBucketOrder = [
        //     'Follow Up',
        //     'Options Shortlisting',
        //     'Application',
        //     'Offer Letter',
        //     'Payment',
        //     'CAS',
        //     'Visa',
        //     'Enrollment'
        // ];

        // Get only required parent buckets
        $parentBuckets = Bucket::whereNull('parent_id')
            ->where('name', '!=', 'Lost')
            ->where('is_deleted', 0)
            // ->whereIn('name', $mainBucketOrder)
            ->get();


        // Total stages
        $totalParentBuckets = $parentBuckets->count();
        $progressPerBucket = $totalParentBuckets > 0
            ? 100 / $totalParentBuckets
            : 0;

        // Map bucket id → position based on FIXED ORDER
        $bucketPositionMap = [];

        foreach ($parentBuckets as $index => $bucket) {
            $bucketPositionMap[$bucket->id] = $index + 1;
        }

        // 3️⃣ Build Lead Query Based On Role
        $leadQuery = Leads::with(['bucket', 'user'])
            ->orderBy('id', 'desc');

        if ($currentUser->role_id == 1) {
            // Admin → see all
        } else {
            $leadQuery->whereIn('lead_owner', $allowedUserIds);
        }

        // Apply date filter if present
        if ($filterStart && $filterEnd) {
            $leadQuery->whereBetween('created_at', [$filterStart, $filterEnd]);
        }

        // Get recent 5 leads
        $recentLeadsProgress = $leadQuery
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($lead) use ($bucketPositionMap, $progressPerBucket, $totalParentBuckets) {

                if (!$lead->bucket) {
                    return null;
                }

                $parentBucketId = $lead->bucket->parent_id
                    ? $lead->bucket->parent_id
                    : $lead->bucket->id;

                $position = $bucketPositionMap[$parentBucketId] ?? 0;
                $progress = round($position * $progressPerBucket);

                return [
                    'user' => $lead->user,
                    'lead_name' => $lead->id,
                    'bucket_name' => $lead->bucket->name,
                    'stage_position' => $position,        // 👈 important
                    'total_stages' => $totalParentBuckets, // 👈 important
                    'progress' => $progress
                ];
            })
            ->filter();



        // ── Monthly New Leads Chart – Prepare data for frontend switching ────────────

        // Date range (full current year by default)
        $now = Carbon::now();
        $chartStart = $now->copy()->startOfYear();
        $chartEnd = $now->copy()->endOfYear();

        if ($filterStart && $filterEnd) {
            $chartStart = $filterStart->copy()->startOfMonth();
            $chartEnd = $filterEnd->copy()->endOfMonth();
        }

        // Generate month categories (always full range)
        $chartCategories = [];
        $current = $chartStart->copy();
        while ($current->lte($chartEnd)) {
            $chartCategories[] = $current->format('M y');
            $current->addMonthNoOverflow();
        }

        // ── 1. Get list of users + their monthly data
        $monthlyChartData = [];

        $currentUser = auth()->user();

        if ($currentUser->role_id == 1) {
            // Admin → get all sales users
            $usersForChart = User::whereNotIn('role_id', [1, 2])
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $usersForChart = User::whereIn('id', $allowedUserIds)
                ->whereNotIn('role_id', [1, 2])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        // Build data for each user
        foreach ($usersForChart as $user) {
            $query = Leads::where('lead_owner', $user->id)
                ->whereBetween('created_at', [$chartStart, $chartEnd])
                ->selectRaw("DATE_FORMAT(created_at, '%b %y') as month, COUNT(*) as total")
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $userData = collect($chartCategories)
                ->map(fn($m) => (int) ($query[$m] ?? 0))
                ->toArray();

            $monthlyChartData[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'series' => $userData,
                'total' => array_sum($userData),
            ];
        }

        // ── 2. Also prepare "All Users" combined data (for admin default view)
        $allTotalsQuery = Leads::whereBetween('created_at', [$chartStart, $chartEnd]);
        if ($currentUser->role_id !== 1) {
            $allTotalsQuery->whereIn('lead_owner', $allowedUserIds);
        }
        $allTotals = $allTotalsQuery
            ->selectRaw("DATE_FORMAT(created_at, '%b %y') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $allSeries = collect($chartCategories)
            ->map(fn($m) => (int) ($allTotals[$m] ?? 0))
            ->toArray();

        $monthlyChartData = array_merge(
            [['user_id' => 'all', 'user_name' => 'All Users', 'series' => $allSeries, 'total' => array_sum($allSeries)]],
            $monthlyChartData
        );

        // source wise data 
        $fixedSources = [
            'website',
            'referral',
            'social media',
            'facebook',
            'instagram',
            'whatsapp',
            'advertisement',
            'other',
            'landing page',
            'manual import'
        ];

        $sourceChartData = [];

        foreach ($fixedSources as $source) {

            $normalizedSource = strtolower(trim($source));

            $leadsQuery = Leads::whereRaw("LOWER(TRIM(platform)) = ?", [$normalizedSource])
                ->whereBetween('created_at', [$chartStart, $chartEnd]);
            if ($currentUser->role_id !== 1) {
                $leadsQuery->whereIn('lead_owner', $allowedUserIds);
            }
            $query = $leadsQuery->selectRaw("DATE_FORMAT(created_at, '%b %y') as month, COUNT(*) as total")
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $series = collect($chartCategories)
                ->map(fn($m) => (int) ($query[$m] ?? 0))
                ->toArray();

            $sourceChartData[] = [
                'source_name' => ucfirst($source),
                'series' => $series,
                'total' => array_sum($series),
            ];
        }

        $allTotalsQuery = Leads::whereIn(
            DB::raw("LOWER(TRIM(platform))"),
            array_map('strtolower', $fixedSources)
        )
            ->whereBetween('created_at', [$chartStart, $chartEnd]);
        if ($currentUser->role_id !== 1) {
            $allTotalsQuery->whereIn('lead_owner', $allowedUserIds);
        }
        $allTotals = $allTotalsQuery
            ->selectRaw("DATE_FORMAT(created_at, '%b %y') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $allSeries = collect($chartCategories)
            ->map(fn($m) => (int) ($allTotals[$m] ?? 0))
            ->toArray();

        $sourceChartData = array_merge(
            [
                [
                    'source_name' => 'All Sources',
                    'series' => $allSeries,
                    'total' => array_sum($allSeries),
                ]
            ],
            $sourceChartData
        );

        return view('crm::index', compact(
            'buckets',
            'firstBucket',
            'statusCounts',
            'totalLeads',
            'engagementCounts',
            'engagementPercentages',
            'totalEngagement',
            'recentLeads',
            'salesUserPerformance',
            'recentLeadsProgress',
            'monthlyChartData',
            'chartCategories',
            'sourceChartData',
        ));
    }
}

