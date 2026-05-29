<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Http\Controllers\Controller;
use Modules\CRM\App\Models\CallBack;
use Modules\CRM\App\Models\Leads;
use Modules\CRM\App\Models\Bucket;
use Modules\CRM\App\Models\Category;
use Modules\Shared\App\Models\User;
use Modules\CRM\App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;



class NewleadController extends Controller
{
    public function index(Request $request)
    {
        // 1. Eager Load Relations (Same as your old code)
        $query = Leads::with([
            'user',
            'owner',
            'bucket.children',
            'messages.user',
            'messages.lead',
            'attributes',
            'todoTasks.assignee',
            'latestAssignHistory',
            'category',
        ])->withCount([
                    'messages as call_followup_count' => function ($q) {
                        $q->where('followup_type', 'Call');
                    }
                ]);

        // 2. Role-based restrictions
        if (auth()->check() && auth()->user()->role_id == 3) {
            $query->where('lead_owner', auth()->id());
        }

        // 3. APPLY ALL YOUR FILTERS
        // Global Search

        if ($request->filled('search')) {
            $search = $request->search;
            $digitsOnly = preg_replace('/\D+/', '', $search);

            $query->whereHas('user', function ($q) use ($search, $digitsOnly) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if ($digitsOnly !== '') {
                    $q->orWhereRaw("REPLACE(contact_no, ' ', '') LIKE ?", ['%' . $digitsOnly . '%']);
                } else {
                    $q->orWhere('contact_no', 'like', "%{$search}%");
                }
            });
        }
        // dd($query->get()->toArray());
        // Date Filter
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('date', [$from, $to]);
        } elseif ($request->filled('from')) {
            $from = \Carbon\Carbon::parse($request->from)->toDateString();
            $query->whereDate('date', $from);
        }

        // Other Filters
        if ($request->filled('source'))
            $query->where('platform', 'like', "%{$request->source}%");
        if ($request->filled('status'))
            $query->where('lead_status', $request->status);
        if ($request->filled('owner_id')) {

            if ($request->owner_id === 'null') {
                $query->whereNull('lead_owner');
            } else {
                $query->where('lead_owner', $request->owner_id);
            }
        }

        if ($request->filled('duplicate_of')) {

            $lead = Leads::find($request->duplicate_of);

            if ($lead) {
                $query->where('uid', $lead->uid);
            }
        }

        if ($request->filled('deleted_leads')) {

            $mainBucketIds = Bucket::whereNull('parent_id')
                ->where('is_deleted', 0)
                ->pluck('id')
                ->toArray();

            $query->whereNotNull('lead_bucket_id')
                ->where('lead_bucket_id', '!=', '')
                ->whereNotIn('lead_bucket_id', $mainBucketIds);
        }

        if ($request->filled('country'))
            $query->where('applying_country_for_a_visa', 'like', "%{$request->country}%");
        if ($request->filled('course'))
            $query->where('what_course_are_you_planning_to_study', 'like', "%{$request->course}%");
        if ($request->filled('bucket_id'))
            $query->where('lead_bucket_id', $request->bucket_id);
        if ($request->filled('bucket_id') && $request->filled('lead_status')) {

            $query->where('lead_bucket_id', $request->bucket_id)
                ->where('lead_status', $request->lead_status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('campaign_name'))
            $query->where('campaign_name', 'like', "%{$request->campaign_name}%");
        if ($request->filled('adset_name'))
            $query->where('adset_name', 'like', "%{$request->adset_name}%");
        if ($request->filled('ad_name'))
            $query->where('ad_name', 'like', "%{$request->ad_name}%");
        if ($request->filled('has_followups')) {

            $today = \Carbon\Carbon::today();
            $type = $request->followup_type_filter ?? 'upcoming';

            $query->whereHas('messages', function ($q) use ($today, $type) {

                $q->whereNotNull('next_followup_date');

                if ($type == 'missed') {
                    $q->whereDate('next_followup_date', '<', $today)
                        ->where('is_done', 0);
                } else {
                    $q->whereDate('next_followup_date', '>=', $today);
                }
            });
        }
        if ($request->filled('lead_engagement_status')) {
            $query->where('lead_engagement_status', strtolower($request->lead_engagement_status));
        }

        if ($request->filled('date_filter')) {

            if ($request->date_filter == 'today') {

                $query->whereHas('messages', function ($q) {
                    $q->whereDate('created_at', Carbon::today());
                });
            } elseif ($request->date_filter == 'yesterday') {

                $query->whereHas('messages', function ($q) {
                    $q->whereDate('created_at', Carbon::yesterday());
                });
            }
        }


        // 4. Counts
        $user = auth()->user();
        $filteredLeadCount = $query->count();
        if ($user->role_id == 1 || $user->role_id == 2) {
            $totalLeadsCount = Leads::count();
        } else {
            $totalLeadsCount = Leads::where('lead_owner', $user->id)->count();
        }
        // 5. Pagination (Appends query preserves filters on next pages)
        $perPage = request('per_page', 20);
        $leads = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->query());


        // ================= DUPLICATE OPTIMIZATION =================

        $uids = $leads->pluck('uid')->filter()->unique();

        $duplicateData = Leads::whereIn('uid', $uids)
            ->select('uid', 'id')
            ->get()
            ->groupBy('uid');

        $leads->getCollection()->transform(function ($lead) use ($duplicateData) {

            $duplicates = collect($duplicateData[$lead->uid] ?? [])
                ->where('id', '!=', $lead->id);

            $lead->duplicate_count = $duplicates->count();

            $lead->duplicate_ids = $duplicates->pluck('id');

            return $lead;
        });
        // echo "<pre>";print_r($leads->toArray());exit;

        // Attach last message
        $leads->getCollection()->transform(function ($lead) {
            $lead->lastMessage = $lead->messages->sortByDesc('created_at')->first();
            return $lead;
        });

        // 6. Dynamic Buckets (From your old logic)
        $mainStatuses = [
            'Untouched leads',
            'Not Connected',
            'Counselling in Progress',
            'Application Process',
            'Offer Stage',
            'Visa Process',
            'Converted',
            'Lost'
        ];
        $buckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->withCount([
                'leads' => function ($q) {
                    if (auth()->check() && auth()->user()->role_id == 3) {
                        $q->where('lead_owner', auth()->id());
                    }
                }
            ])
            ->orderByRaw("FIELD(name, '" . implode("','", $mainStatuses) . "')")
            ->get();

        $childBuckets = collect();
        $childtotalLeadsCount = 0;
        if ($request->filled('bucket_id')) {

            // current bucket ke children lao
            $childBuckets = Bucket::where('parent_id', $request->bucket_id)
                ->where('is_deleted', 0)
                ->select('buckets.*')
                ->selectSub(function ($q) use ($request) {

                    $q->from('leads')
                        ->selectRaw('COUNT(*)')
                        ->where('leads.lead_bucket_id', $request->bucket_id)
                        ->whereColumn('leads.lead_status', 'buckets.name')
                        ->when(auth()->check() && auth()->user()->role_id == 3, function ($qq) {
                            $qq->where('leads.lead_owner', auth()->id());
                        });
                }, 'leads_count')
                ->get();

            $childtotalLeadsCount = Leads::where('lead_bucket_id', $request->bucket_id)->count();

            $filterBucket = Bucket::where('id', $request->bucket_id)
                ->whereNull('parent_id')
                ->where('is_deleted', 0)
                ->withCount([
                    'leads' => function ($q) {
                        if (auth()->check() && auth()->user()->role_id == 3) {
                            $q->where('lead_owner', auth()->id());
                        }
                    }
                ])
                ->orderByRaw("FIELD(name, '" . implode("','", $mainStatuses) . "')")
                ->get();
        } else {

            // ✅ default parent buckets

            $filterBucket = Bucket::whereNull('parent_id')
                ->where('is_deleted', 0)
                ->withCount([
                    'leads' => function ($q) {
                        if (auth()->check() && auth()->user()->role_id == 3) {
                            $q->where('lead_owner', auth()->id());
                        }
                    }
                ])
                ->orderByRaw("FIELD(name, '" . implode("','", $mainStatuses) . "')")
                ->get();
        }

        $mainbuckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->withCount([
                'leads' => function ($q) {
                    if (auth()->check() && auth()->user()->role_id == 3) {
                        $q->where('lead_owner', auth()->id());
                    }
                }
            ])
            ->orderByRaw("FIELD(name, '" . implode("','", $mainStatuses) . "')")
            ->get();

        $mainBucketIds = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->pluck('id')
            ->toArray();

        $deletedLeadsCount = Leads::whereNotNull('lead_bucket_id')
            ->where('lead_bucket_id', '!=', '')
            ->whereNotIn('lead_bucket_id', $mainBucketIds)
            ->when(auth()->check() && auth()->user()->role_id == 3, function ($q) {
                $q->where('lead_owner', auth()->id());
            })
            ->count();

        $categorys = Category::where('is_active', 1)->get();

        $owners = User::whereIn('role_id', [1, 3])->where('is_deleted', 0)->get();
        $sources = LeadSource::pluck('source_name')->toArray();
        $today = Carbon::today();
        $followupsQuery = Leads::query();
        // Role restriction same rakho
        if (auth()->check() && auth()->user()->role_id == 3) {
            $followupsQuery->where('lead_owner', auth()->id());
        }

        $type = $request->followup_type_filter ?? 'upcoming';

        $followupsQuery->whereHas('messages', function ($q) use ($today, $type) {

            $q->whereNotNull('next_followup_date');

            if ($type == 'missed') {
                $q->whereDate('next_followup_date', '<', $today)
                    ->where('is_done', 0);
            } else {
                $q->whereDate('next_followup_date', '>=', $today);
            }
        });
        $followupsCount = $followupsQuery->count();



        // Return to your new view
        return view('crm::crm.lead.newindex', compact('leads', 'childBuckets', 'filterBucket', 'mainbuckets', 'childtotalLeadsCount', 'categorys', 'buckets', 'deletedLeadsCount', 'owners', 'totalLeadsCount', 'filteredLeadCount', 'sources', 'followupsCount'));
    }

    public function updateQuick(Request $request, Leads $lead)
    {
        $request->validate([
            'lead_engagement_status' => 'nullable|string',
            'lead_bucket_id' => 'required|integer|exists:buckets,id',
            'lead_status' => 'required|string',
            'followup_type' => 'nullable|string',
            'next_followup_date' => 'nullable|date',
            'message' => 'nullable|string|max:1000',
            'call_recording' => 'nullable|file|mimes:mp3,wav,m4a,ogg,aac,amr,3gp,mp4|max:51200 '
        ]);
        $lead->update([
            'lead_engagement_status' => $request->lead_engagement_status,
            'lead_bucket_id' => $request->lead_bucket_id,
            'lead_status' => $request->lead_status,
            'followup_type' => $request->followup_type,
        ]);
        $audioPath = null;

        if ($request->hasFile('call_recording')) {
            $audioPath = $request->file('call_recording')->store('call_recordings', 'public');
        }
        $bucketName = Bucket::find($request->lead_bucket_id)->name ?? '';
        CallBack::create([
            'lead_id' => $lead->id,
            'message' => $request->message,
            'status' => $request->lead_status,
            'bucket' => $bucketName,
            'lead_engagement_status' => $request->lead_engagement_status,
            'followup_type' => $request->followup_type,
            'followup_status' => $request->followup_status ?? null,
            'created_by' => auth()->user()->id,
            'next_followup_date' => $request->next_followup_date
                ? Carbon::parse($request->next_followup_date)
                : null,
            'is_done' => 0,
            'call_recording' => $audioPath,
        ]);

        return redirect()->back()->with('success', 'Details updated successfully!');
    }

    public function storeTodo(Request $request, $leadId)
    {
        $isAdmin = auth()->check() && auth()->user()->role_id == 1;

        $rules = [
            'summary' => 'required|string',
            'due_date' => 'required|date',
        ];

        if ($isAdmin) {
            $rules['assign_to'] = 'required|integer|exists:users,id';
        }

        $request->validate($rules);

        \Modules\CRM\App\Models\TodoTask::create([
            'lead_id' => $leadId,
            'assigned_to' => $isAdmin ? $request->assign_to : auth()->id(),
            'created_by' => auth()->id(),
            'summary' => $request->summary,
            'due_date' => $request->due_date,
            'status' => 'Pending'
        ]);

        return back()->with('success', 'To-Do Task assigned successfully!');
    }

    // To-Do Task Update Karne Ke Liye (Optional/Future use)
    public function updateTaskStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $task = \Modules\CRM\App\Models\TodoTask::findOrFail($id);
        $task->status = $request->status;
        $task->save();
        return back()->with('success', 'Task status updated!');
    }

    public function campaignDetails($id)
    {
        $data = DB::table('leads')
            ->select(
                'adset_name',
                'ad_name',
                'form_name',
                DB::raw('COUNT(*) as total')
            )
            ->where('campaign_id', $id)
            ->groupBy('adset_name', 'ad_name', 'form_name')
            ->get();

        return view('crm::crm.lead.campaign-details', compact('data'));
    }
}


