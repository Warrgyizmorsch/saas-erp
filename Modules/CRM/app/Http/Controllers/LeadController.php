<?php

namespace Modules\CRM\App\Http\Controllers;

use App\Exports\LeadsExcelExport;
use App\Http\Controllers\Controller;
use Modules\CRM\App\Models\CallBack;
use Modules\CRM\App\Models\Leads;
use Modules\CRM\App\Models\Bucket;
use Modules\CRM\App\Models\User;
use Modules\CRM\App\Models\LeadHistory;
use Modules\CRM\App\Models\LeadAttribute;
use Modules\CRM\App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Jobs\LeadsImportJob;
use Illuminate\Http\UploadedFile;
use App\Exports\LeadsExport;
use Modules\CRM\App\Models\LeadAssignHistory;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Leads::with([
            'user',
            'owner',
            'bucket.children',
            'messages.user',
            'attributes',

        ]);

        // 👤 Restrict for role_id = 3 (counselors/agents)
        if (auth()->user()->role_id == 3) {
            $query->where('lead_owner', auth()->id());
        }

        // 🔍 Global Search (name, contact, email)
        if ($request->filled('search')) {
            $search = $request->search;
            $digitsOnly = preg_replace('/\D+/', '', $search); // keep only numbers

            $query->whereHas('user', function ($q) use ($search, $digitsOnly) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                // If user typed something that looks like a phone number
                if ($digitsOnly !== '') {
                    // Compare against contact_no with spaces removed
                    $q->orWhereRaw(
                        "REPLACE(contact_no, ' ', '') LIKE ?",
                        ['%' . $digitsOnly . '%']
                    );
                } else {
                    // Fallback: plain like on contact_no
                    $q->orWhere('contact_no', 'like', "%{$search}%");
                }
            });
        }


        // 📅 Date Filter
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('date', [$from, $to]);
        } elseif ($request->filled('from')) {
            $from = \Carbon\Carbon::parse($request->from)->toDateString();
            $query->whereDate('date', $from);
        }

        // 📌 Source filter
        if ($request->filled('source')) {
            $query->where('platform', 'like', "%{$request->source}%");
        }

        // ✅ Status filter (child bucket name, but scoped under current bucket)
        if ($request->filled('status')) {
            $query->where('lead_status', $request->status);
        }

        // 👨 Lead Owner filter
        if ($request->filled('owner_id')) {
            $query->where('lead_owner', $request->owner_id);
        }

        // 🌍 Applied Country filter
        if ($request->filled('country')) {
            $query->where('applying_country_for_a_visa', 'like', "%{$request->country}%");
        }

        // 🎓 Course filter
        if ($request->filled('course')) {
            $query->where('what_course_are_you_planning_to_study', 'like', "%{$request->course}%");
        }

        // 🪣 Bucket filter
        if ($request->filled('bucket_id')) {
            $query->where('lead_bucket_id', $request->bucket_id);
        }

        // 🎯 Campaign Name Filter
        if ($request->filled('campaign_name')) {
            $query->where('campaign_name', 'like', "%{$request->campaign_name}%");
        }

        // 🎯 Adset Name Filter
        if ($request->filled('adset_name')) {
            $query->where('adset_name', 'like', "%{$request->adset_name}%");
        }

        // 🎯 Ad Name Filter
        if ($request->filled('ad_name')) {
            $query->where('ad_name', 'like', "%{$request->ad_name}%");
        }

        if ($request->filled('old_bucket_id')) {
            $query->where('lead_bucket_id', $request->old_bucket_id);
        }

        if ($request->filled('old_sub_status')) {
            $query->where('lead_status', $request->old_sub_status);
        }



        // ✅ Count total leads (before pagination)
        $filteredLeadCount = $query->count();

        // ✅ Count total leads (without filters)
        $totalLeadsCount = Leads::count();

        // ✅ Fetch paginated leads
        $leads = $query->orderBy('id', 'desc')->paginate(20)->appends($request->query());

        // 🧠 Attach last message (for easy use in Blade)
        $leads->getCollection()->transform(function ($lead) {
            $lead->lastMessage = $lead->messages->sortByDesc('created_at')->first();
            return $lead;
        });

        // Collect all active extra fields to show
        $activeQuestions = \App\Models\LeadQuestion::where('is_active', 1)
            ->pluck('field_name')
            ->map(fn($f) => trim(preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower(str_replace(['’', "'"], '', $f))), '_'))
            ->toArray();

        $extraFieldNames = $leads->getCollection()
            ->pluck('attributes')
            ->flatten()
            ->filter(fn($attr) => in_array($attr->field_name, $activeQuestions))
            ->pluck('field_name')
            ->unique()
            ->values();

        $buckets = Bucket::whereNull('parent_id')->with('children')->get();
        $filterBucket = Bucket::whereNull('parent_id')->with('children')->get();

        $mainBucketIds = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->pluck('id')
            ->toArray();

        $otherBucketIds = Leads::whereNotIn('lead_bucket_id', $mainBucketIds)
            ->whereNotNull('lead_bucket_id')
            ->distinct()
            ->pluck('lead_bucket_id')
            ->toArray();

        $oldSubStatus = Leads::whereIn('lead_bucket_id', $otherBucketIds)
            ->whereNotNull('lead_status')
            ->distinct()
            ->pluck('lead_status');


        $oldbuckets = Bucket::whereIn('id', $otherBucketIds)
            ->where('is_deleted', 0)
            ->get();

        $owners = User::whereIn('role_id', [1, 3])->get();
        // Dynamic lead sources
        $sources = LeadSource::pluck('source_name')->toArray();


        return view('crm.lead.index', compact('leads', 'oldbuckets', 'filterBucket', 'oldSubStatus', 'extraFieldNames', 'buckets', 'owners', 'totalLeadsCount', 'filteredLeadCount', 'sources'));
    }

    public function application(Request $request)
    {
        $query = Leads::with('user', 'bucket')->whereIn('lead_bucket_id', [23, 30, 37, 48]);
        if ($request->filled('status')) {
            $query->where('lead_bucket_id', $request->status);
        }
        if ($request->filled('applicant_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->applicant_name . '%');
            });
        }
        if ($request->filled('mobile_no')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('contact_no', 'like', '%' . $request->mobile_no . '%');
            });
        }
        $applicationData = $query->paginate(10);
        return view('crm.lead.application', compact('applicationData'));
    }

    public function create()
    {
        $lead = new Leads();

        // Only top-level buckets
        $buckets = Bucket::whereNull('parent_id')->get();

        // Only users with role_id = 3 and 1
        $owners = User::whereIn('role_id', [1, 3])->get();

        // Dynamic lead sources
        $sources = LeadSource::where('is_active', 1)->pluck('source_name')->toArray();

        return view('crm.lead.create', compact('lead', 'buckets', 'sources', 'owners'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'country_code' => 'nullable|string|max:10',
            'mobile' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',

            // Lead fields
            'lead_status' => 'nullable|string',
            'lead_owner' => 'nullable|integer|exists:users,id',
            'campaign_name' => 'nullable|string',
            'adset_name' => 'nullable|string',
            'ad_name' => 'nullable|string',
            'form_name' => 'nullable|string',
            'platform' => 'nullable|string',
            'whats_your_preferred_intake' => 'nullable|string',
            'highest_completed' => 'nullable|string',
            'any_academic_gap' => 'nullable|string',
            'budget' => 'nullable|string',
            'applying_country_for_a_visa' => 'nullable|string',
            'what_course_are_you_planning_to_study' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable',
        ]);

        // 🔍 Search existing user by mobile number
        $user = User::where('contact_no', $data['mobile'])->first();

        if (!$user) {
            // ✅ Create new user if not found
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? 'user' . $data['mobile'] . '@gmail.com',
                'contact_no' => $data['mobile'],
                'country_code' => $data['country_code'] ?? null,
                'role_id' => 2,
                'password' => bcrypt('user@123'),
                'city' => $data['city'] ?? null,
            ]);
        }


        // Prepare lead data
        $leadData = $data;
        unset($leadData['name'], $leadData['email'], $leadData['mobile'], $leadData['country_code'], $leadData['city']);

        $leadData['uid'] = $user->id;
        $leadData['date'] = $data['date'] ?? now();

        $leadData['lead_bucket_id'] = 1;

        $leadData['lead_status'] = \DB::table('buckets')->where('id', 2)->value('name');

        $lead = Leads::create($leadData);

        if (!empty($lead->lead_owner)) {

            LeadAssignHistory::create([
                'lead_id' => $lead->id,
                'lead_owner_id' => $data['lead_owner'],
                'assigned_by' => auth()->id(),
                'assigned_date' => now(),
            ]);
        }

        // Log history
        LeadHistory::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'changes' => [
                'created_data' => $leadData
            ]
        ]);

        return redirect()->back()->with('success', 'Lead created successfully.');
    }


    public function edit(Leads $lead)
    {
        $buckets = Bucket::whereNull('parent_id')->get();

        $owners = User::whereIn('role_id', [1, 3])->get();


        // Dynamic lead sources
        $sources = LeadSource::where('is_active', 1)->pluck('source_name')->toArray();

        // Load existing attributes
        $leadAttributes = $lead->attributes()->get();

        // Get all active questions
        $activeQuestions = \App\Models\LeadQuestion::where('is_active', 1)
            ->pluck('field_name')
            ->map(fn($f) => trim(preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower(str_replace(['’', "'"], '', $f))), '_'))
            ->toArray();

        // Merge active questions with lead attributes
        $mergedAttributes = collect($activeQuestions)->map(function ($field) use ($leadAttributes) {
            $existing = $leadAttributes->firstWhere('field_name', $field);
            return (object) [
                'id' => $existing->id ?? null,
                'field_name' => $field,
                'field_value' => $existing->field_value ?? ''
            ];
        });

        return view('crm.lead.create', compact('lead', 'buckets', 'owners', 'sources', 'mergedAttributes'));
    }

    public function update(Request $request, Leads $lead)
    {
        $data = $request->validate([
            // User fields
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $lead->uid,
            'country_code' => 'nullable|string|max:10',
            'mobile' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',

            // Lead fields
            'lead_bucket_id' => 'nullable|integer|exists:buckets,id',
            'lead_status' => 'nullable|string',
            'lead_owner' => 'nullable|integer|exists:users,id',
            'campaign_name' => 'nullable|string',
            'adset_name' => 'nullable|string',
            'ad_name' => 'nullable|string',
            'form_name' => 'nullable|string',
            'platform' => 'nullable|string',
            'whats_your_preferred_intake' => 'nullable|string',
            'highest_completed' => 'nullable|string',
            'any_academic_gap' => 'nullable|string',
            'budget' => 'nullable|string',
            'applying_country_for_a_visa' => 'nullable|string',
            'what_course_are_you_planning_to_study' => 'nullable|string',
            'description' => 'nullable|string',
            'category_id' => 'nullable',
        ]);

        // ----------------------------
        // 1. Update related user
        // ----------------------------
        if ($lead->user) {
            $userOld = $lead->user->getOriginal();
            $lead->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'contact_no' => $data['mobile'],
                'country_code' => $data['country_code'] ?? null,
                'city' => $data['city'] ?? null,
            ]);

            $userChanges = [];
            foreach (['name', 'email', 'contact_no', 'country_code', 'city'] as $field) {
                if (($userOld[$field] ?? null) != $lead->user->$field) {
                    $userChanges[$field] = [
                        'old' => $userOld[$field] ?? null,
                        'new' => $lead->user->$field,
                    ];
                }
            }

            if (!empty($userChanges)) {
                LeadHistory::create([
                    'lead_id' => $lead->id,
                    'user_id' => auth()->id(),
                    'action' => 'user_updated',
                    'changes' => $userChanges
                ]);
            }
        }

        $oldOwnerId = $lead->lead_owner;

        // ----------------------------
        // 2. Update Lead itself
        // ----------------------------
        $oldData = $lead->getOriginal();

        $leadData = $data;
        unset($leadData['name'], $leadData['email'], $leadData['mobile'], $leadData['country_code'], $leadData['city']);

        $lead->update($leadData);

        $newOwnerId = $lead->lead_owner;

        if ($oldOwnerId != $newOwnerId) {

            LeadAssignHistory::create([
                'lead_id' => $lead->id,
                'lead_owner_id' => $newOwnerId,
                'assigned_by' => auth()->id(),
                'assigned_date' => now(),
            ]);
        }

        $lead->load(['bucket', 'owner']);

        $leadChanges = [];
        foreach ($leadData as $field => $value) {
            $oldValue = $oldData[$field] ?? null;
            $newValue = $lead->$field;

            if ($oldValue != $newValue) {
                // Handle special cases
                if ($field === 'lead_bucket_id') {
                    $oldValue = optional(Bucket::find($oldValue))->name;
                    $newValue = optional($lead->bucket)->name;
                } elseif ($field === 'lead_owner') {
                    $oldValue = optional(User::find($oldValue))->name;
                    $newValue = optional($lead->owner)->name;
                }

                $leadChanges[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        if (!empty($leadChanges)) {
            LeadHistory::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'action' => 'lead_updated',
                'changes' => $leadChanges
            ]);
        }

        // 3. Update dynamic lead attributes
        $attributes = $request->input('attributes', []);

        if (!empty($attributes) && is_array($attributes)) {
            foreach ($attributes as $attrId => $value) {
                if ($value === null)
                    continue;

                // Find existing attribute by ID and lead_id
                $attr = LeadAttribute::where('id', $attrId)
                    ->where('lead_id', $lead->id)
                    ->first();

                if (!$attr) {
                    // Skip if not found
                    continue;
                }

                $oldValue = $attr->field_value;
                $attr->field_value = $value;
                $attr->save();

                if ($oldValue != $value) {
                    LeadHistory::create([
                        'lead_id' => $lead->id,
                        'user_id' => auth()->id(),
                        'action' => 'attribute_updated',
                        'changes' => [
                            'field_name' => $attr->field_name,
                            'old' => $oldValue,
                            'new' => $value
                        ]
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Lead updated successfully.');
    }

    public function updateBucket(Request $request, $id)
    {
        $lead = Leads::findOrFail($id);
        $lead->lead_bucket_id = $request->lead_bucket_id;
        $lead->save();

        $bucket = Bucket::with('children')->find($request->lead_bucket_id);

        $children = $bucket ? $bucket->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'color' => $child->bucket_color
            ];
        }) : [];

        return response()->json([
            'message' => 'Bucket updated successfully',
            'children' => $children
        ]);
    }

    public function getSubStatus(Request $request)
    {
        $bucket = Bucket::with('children')->find($request->lead_bucket_id);

        if (!$bucket) {
            return response()->json([
                'message' => 'Bucket not found',
                'children' => []
            ]);
        }

        $children = $bucket->children->map(function ($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'color' => $child->bucket_color
            ];
        });

        return response()->json([
            'message' => 'response fetched successfully',
            'children' => $children
        ]);
    }



    public function updateStatus(Request $request, Leads $lead)
    {
        $request->validate([
            'lead_status' => 'nullable|string'
        ]);

        $oldStatus = $lead->lead_status;

        $lead->update([
            'lead_status' => $request->lead_status,
        ]);

        LeadHistory::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'action' => 'status_changed',
            'changes' => [
                'old' => $oldStatus,
                'new' => $request->lead_status
            ]
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Lead status updated successfully.'
        ]);
    }


    public function searchByMobile(Request $request)
    {
        $user = User::where('contact_no', $request->mobile)->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => $user
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function history(Leads $lead)
    {
        // Eager load user who owns history logs
        $lead->load(['histories.user', 'bucket', 'owner']);

        return view('crm.lead.history', compact('lead'));
    }


    public function destroy(Leads $lead)
    {
        $lead->delete();
        return redirect()->route('lead.index')->with('success', 'Lead deleted successfully.');
    }

    public function downloadSample()
    {
        // Generate a simple CSV header matching exactly the required columns
        $headers = [
            'created_time',
            'campaign_name',
            'adset_name',
            'ad_name',
            'form_name',
            'platform',
            'what_is_your_preferred_intake?',
            // 'highest_level_of_education_completed',
            // 'any_academic_gap_(i.e.,_time_off_between_or_during_studies)?',
            'what_is_your_budget?',
            'confirm_the_country_for_which_you_are_applying_for_visa?',
            'what_course_are_you_planning_to_study?',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'city',
        ];

        // Wrap each header in quotes to prevent Excel splitting on commas
        $headers = array_map(function ($header) {
            return '"' . $header . '"';
        }, $headers);

        $csv = implode(',', $headers) . "\n";
        $filename = 'leads_import_sample.csv';

        Storage::disk('local')->put($filename, $csv);

        return response()->download(storage_path('app/' . $filename))->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:20480',
                    function ($attribute, $value, $fail) {
                        $allowed = ['xls', 'xlsx', 'csv', 'txt'];
                        if (!in_array(strtolower($value->getClientOriginalExtension()), $allowed)) {
                            $fail('Please upload a valid Excel/CSV file (.xls, .xlsx, .csv, .txt).');
                        }
                    }
                ]
            ]);

            // dd("Validation passed!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors());
        }
        // $request->validate([
        //     'file' => 'required|mimes:xlsx,xls,csv|max:20480',
        // ]);

        $file = $request->file('file');
        $rowLimit = 5000;
        $preflight = $this->preflightValidateExcel($file, $rowLimit);

        if ($preflight['status'] === 'error') {
            return response()->json(['status' => 'error', 'message' => $preflight['message']], 422);
        }

        try {
            $storedPath = $file->store('imports');
            // LeadsImportJob::dispatch($storedPath, auth()->id());
            $importJob = \App\Models\LeadImportJob::create([
                'file_path' => $storedPath,
                'status' => 'pending',
                'total_rows' => $preflight['total_rows'] ?? 0,
                'processed_rows' => 0,
            ]);

            LeadsImportJob::dispatch($importJob->id, auth()->id());


            return response()->json(['status' => 'success', 'message' => 'File uploaded successfully. Import has been queued and will be processed in the background.', 'job_id' => $importJob->id]);
        } catch (\Exception $e) {
            \Log::error("Import dispatch failed: " . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start import. Please try again later.'
            ], 500);
        }
    }



    /**
     * Preflight validation: strict format, row limit, required fields not blank.
     */

    private function preflightValidateExcel(UploadedFile $file, int $rowLimit): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            // Header row
            $row1 = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];

            if (empty(array_filter($row1))) {
                return [
                    'status' => 'error',
                    'message' => 'Excel file has no header row.',
                ];
            }

            // Row count check
            $dataRowCount = max(0, $highestRow - 1);

            if ($dataRowCount === 0) {
                return [
                    'status' => 'error',
                    'message' => 'Excel file is empty.',
                ];
            }

            if ($dataRowCount > $rowLimit) {
                return [
                    'status' => 'error',
                    'message' => "Excel file exceeds the maximum row limit of {$rowLimit}.",
                ];
            }

            // Everything else is OPTIONAL
            return [
                'status' => 'ok',
                'message' => 'validated',
                'total_rows' => $dataRowCount,
            ];
        } catch (\Throwable $e) {
            \Log::error('Preflight Excel error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Invalid or unreadable Excel file.',
            ];
        }
    }


    // private function preflightValidateExcel(UploadedFile $file, int $rowLimit): array
    // {
    //     try {
    //         $spreadsheet = IOFactory::load($file->getRealPath());
    //         $sheet = $spreadsheet->getActiveSheet();

    //         $highestRow = $sheet->getHighestDataRow();
    //         $highestColumn = $sheet->getHighestDataColumn();

    //         // --- Read header row ---
    //         $row1 = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];
    //         $fileHeadersRaw = array_values($row1);

    //         // Normalize headers
    //         $norm = fn(string $h) =>
    //             trim(preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower(str_replace(['’', "'"], '', $h))), '_');

    //         $fileHeaders = array_map($norm, $fileHeadersRaw);

    //         /**
    //          * -------------------------------------------------
    //          * 1. STRICT REQUIRED HEADERS (NO VARIATIONS)
    //          * -------------------------------------------------
    //          */
    //         $strictRequiredRaw = [
    //             'created_time',
    //             'campaign_name',
    //             'adset_name',
    //             'ad_name',
    //             'form_name',
    //             'platform',

    //             'email',
    //             'phone_number',
    //             'city',
    //         ];

    //         $strictRequired = array_map($norm, $strictRequiredRaw);
    //         $missingStrict = array_diff($strictRequired, $fileHeaders);

    //         if (!empty($missingStrict)) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'Excel headers missing required columns: ' . implode(', ', $missingStrict),
    //             ];
    //         }

    //         $hasFirst = in_array($norm('first_name'), $fileHeaders);
    //         $hasLast  = in_array($norm('last_name'), $fileHeaders);
    //         $hasFull  = in_array($norm('full_name'), $fileHeaders) 
    //                 || in_array($norm('name'), $fileHeaders);

    //         if (!(($hasFirst && $hasLast) || $hasFull)) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'Excel must contain either First Name + Last Name OR Full Name column.',
    //             ];
    //         }

    //         /**
    //          * -------------------------------------------------
    //          * 2. SMART QUESTION HEADERS (VARIATION BASED)
    //          * -------------------------------------------------
    //          */
    //         $smartRequired = [
    //             'destination' => ['country', 'destination'],
    //             'intake'      => ['intake'],
    //             'budget'      => ['budget'],
    //             'program'     => ['study', 'course', 'program', 'pursue'],
    //         ];

    //         $foundSmart = array_fill_keys(array_keys($smartRequired), false);

    //         foreach ($fileHeaders as $header) {
    //             foreach ($smartRequired as $key => $keywords) {
    //                 foreach ($keywords as $word) {
    //                     if (str_contains($header, $word)) {
    //                         $foundSmart[$key] = true;
    //                         break 2;
    //                     }
    //                 }
    //             }
    //         }

    //         $missingSmart = array_keys(array_filter($foundSmart, fn($v) => !$v));

    //         if (!empty($missingSmart)) {
    //             return [
    //                 'status' => 'error',
    //                 'message' =>
    //                     'Excel missing required question columns (any variation accepted): ' .
    //                     implode(', ', $missingSmart),
    //             ];
    //         }

    //         /**
    //          * -------------------------------------------------
    //          * 3. ROW LIMIT CHECK
    //          * -------------------------------------------------
    //          */
    //         $dataRowCount = max(0, $highestRow - 1);
    //         if ($dataRowCount === 0) {
    //             return ['status' => 'error', 'message' => 'Excel file is empty.'];
    //         }

    //         if ($dataRowCount > $rowLimit) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => "Excel file exceeds the maximum row limit of {$rowLimit}.",
    //             ];
    //         }

    //         return [
    //             'status' => 'ok',
    //             'message' => 'validated',
    //             'total_rows' => $dataRowCount,
    //         ];

    //     } catch (\Throwable $e) {
    //         \Log::error('Preflight Excel error', [
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //         ]);
    //         return [
    //             'status' => 'error',
    //             'message' => 'Invalid or unreadable Excel file.',
    //         ];
    //     }
    // }

    // private function preflightValidateExcel(UploadedFile $file, int $rowLimit): array
    // {
    //     try {
    //         $spreadsheet = IOFactory::load($file->getRealPath());
    //         $sheet = $spreadsheet->getActiveSheet();

    //         $highestRow = $sheet->getHighestDataRow();
    //         $highestColumn = $sheet->getHighestDataColumn();

    //         // --- Read header row (1) ---
    //         $row1 = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];
    //         $fileHeadersRaw = array_values($row1);

    //         // Normalize headers (case/space/punctuation-insensitive but order must match)
    //         $norm = fn(string $h) => trim(preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower(str_replace(['’', "'"], '', $h))), '_');

    //         $fileHeaders = array_map($norm, $fileHeadersRaw);

    //         $requiredHeadersRaw = [
    //             'created_time',
    //             'campaign_name',
    //             'adset_name',
    //             'ad_name',
    //             'form_name',
    //             'platform',
    //             'what_is_your_preferred_intake?',
    //             // 'highest_level_of_education_completed',
    //             // 'any_academic_gap_(i.e.,_time_off_between_or_during_studies)?',
    //             'what_is_your_budget?',
    //             'confirm_the_country_for_which_you_are_applying_for_visa?',
    //             'what_course_are_you_planning_to_study?',
    //             'first_name',
    //             'last_name',
    //             'email',
    //             'phone_number',
    //             'city',
    //         ];
    //         $requiredHeaders = array_map($norm, $requiredHeadersRaw);

    //         $missing = array_diff($requiredHeaders, $fileHeaders);
    //         if (!empty($missing)) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => 'Excel headers missing required columns: ' . implode(', ', $missing),
    //             ];
    //         }

    //         // --- Row limit check (exclude header row) ---
    //         $dataRowCount = max(0, $highestRow - 1);
    //         if ($dataRowCount === 0) {
    //             return ['status' => 'error', 'message' => 'Excel file is empty.'];
    //         }
    //         if ($dataRowCount > $rowLimit) {
    //             return [
    //                 'status' => 'error',
    //                 'message' => "Excel file exceeds the maximum row limit of {$rowLimit}.",
    //             ];
    //         }

    //         // Build column letter map by normalized header → column letter
    //         $colLetters = array_keys($row1); // ['A','B',...]
    //         $colMap = [];
    //         foreach ($fileHeaders as $i => $h) {
    //             $colMap[$h] = $colLetters[$i] ?? null;
    //         }

    //         // --- Required fields not blank for ANY row ---
    //         // $mustHave = ['first_name', 'last_name', 'email', 'phone_number'];
    //         // for ($r = 2; $r <= $highestRow; $r++) {
    //         //     foreach ($mustHave as $key) {
    //         //         $col = $colMap[$key] ?? null;
    //         //         if (!$col)
    //         //             return ['status' => 'error', 'message' => "Template mapping error for '{$key}'."];
    //         //         $val = $sheet->getCell("{$col}{$r}")->getValue();
    //         //         if (is_null($val) || trim((string) $val) === '') {
    //         //             return [
    //         //                 'status' => 'error',
    //         //                 'message' => "Required field '{$key}' is blank at row {$r}. Fix the file and try again.",
    //         //             ];
    //         //         }
    //         //     }
    //         // }

    //         return ['status' => 'ok', 'message' => 'validated', 'total_rows' => $dataRowCount];
    //     } catch (\Throwable $e) {
    //         return ['status' => 'error', 'message' => 'Invalid or unreadable Excel file.'];
    //     }
    // }




    public function getImportJobStatus($jobId)
    {
        $importJob = \App\Models\LeadImportJob::find($jobId);

        if (!$importJob) {
            return response()->json(['status' => 'error', 'message' => 'Job not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'job_status' => $importJob->status,
                'total_rows' => $importJob->total_rows,
                'processed_rows' => $importJob->processed_rows,
            ]
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'lead_id' => ['required', 'integer', 'exists:leads,id'],
            'lead_status' => ['required', 'string'],
            'lead_bucket' => ['required', 'string'],
        ]);

        CallBack::create([
            'lead_id' => $validated['lead_id'],
            'message' => $validated['message'],
            'status' => $validated['lead_status'],
            'bucket' => $validated['lead_bucket'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Message sent successfully.');
    }


    public function export(Request $request)
    {
        $filters = $request->except('columns');
        $columns = $request->input('columns', []);

        // Prevent export when no filters are applied
        // (ignore empty strings, null, etc.)
        $activeFilters = collect($filters)->filter(function ($value, $key) {
            return !empty($value);
        });

        if ($activeFilters->isEmpty()) {
            return back()->with('error', 'Please apply at least one filter before exporting.');
        }

        if (empty($columns)) {
            return back()->with('error', 'Please select at least one column to export.');
        }

        $fileName = 'leads_export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new LeadsExport($filters, $columns), $fileName);
    }

    public function dailyReport(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        // Convert grouping
        $convertBuckets = [
            'Payment',
            'CAS',
            'Visa',
            'Enrollment'
        ];

        // Other individual statuses
        $statusColumns = [
            'Not Connected',
            'Follow Up',
            'Options Shortlisting',
            'Application',
            'Offer Letter',
            'Closed',
            'Next Intake',
            'Cold lead'
        ];

        $unassignedColumn = 'No Bucket';

        $query = Leads::leftJoin('buckets', 'buckets.id', '=', 'leads.lead_bucket_id');

        if ($from && $to) {
            // Both dates provided → filter by range
            $query->whereBetween(DB::raw('DATE(leads.date)'), [$from, $to]);
        } elseif ($from) {
            // Only from date provided → filter this exact date
            $query->whereDate('leads.date', $from);
        } elseif ($to) {
            // Only to date provided → filter this exact date
            $query->whereDate('leads.date', $to);
        }

        $data = $query->select(
            DB::raw('DATE(leads.date) as lead_date'),
            DB::raw('COALESCE(buckets.name, "No Bucket") as bucket_name'),
            DB::raw('COUNT(*) as total_count')
        )
            ->groupBy('lead_date', 'bucket_name')
            ->orderBy('lead_date', 'desc')
            ->get();

        $finalData = [];

        foreach ($data as $row) {

            $date = $row->lead_date;
            $bucket = $row->bucket_name;
            $count = $row->total_count;

            if (!isset($finalData[$date])) {

                $finalData[$date] = [
                    'total_leads' => 0,
                    'convert' => 0,
                    $unassignedColumn => 0,
                ];

                foreach ($statusColumns as $status) {
                    $finalData[$date][$status] = 0;
                }
            }


            // Total leads
            $finalData[$date]['total_leads'] += $count;

            // Convert merge logic
            if (in_array($bucket, $convertBuckets)) {
                $finalData[$date]['convert'] += $count;
            }

            // Individual status logic
            if (in_array($bucket, $statusColumns)) {
                $finalData[$date][$bucket] += $count;
            }

            // Unassigned bucket logic
            if ($bucket === $unassignedColumn) {
                $finalData[$date][$unassignedColumn] += $count;
            }
        }

        /* -------------------- PAGINATION START -------------------- */

        $perPage = 30;
        $currentPage = request()->get('page', 1);

        $collection = collect($finalData);

        $paginated = new LengthAwarePaginator(
            $collection->forPage($currentPage, $perPage),
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => request()->query()
            ]
        );

        /* -------------------- PAGINATION END -------------------- */

        return view('crm.lead.daily-report', compact(
            'paginated',
            'convertBuckets',
            'statusColumns',
            'unassignedColumn',
            'from',
            'to'
        ));
    }
    public function newdailyReport(Request $request)
    {
        $from = $request->from ?? now()->toDateString();
        $to = $request->to ?? now()->toDateString();
        $engagementFilter = $request->engagement_filter;

        $statusColumns = [
            'Untouched leads',
            'Not Connected',
            'Counselling in Progress',
            'Application Process',
            'Offer Stage',
            'Visa Process',
            'Converted',
            'Lost'
        ];

        // 👉 Users
        // $allUsers = CallBack::whereIn('created_by', function ($query) {
        //     $query->select('id')
        //         ->from('users')
        //         ->where('is_deleted', 0);
        // })
        //     ->select('created_by')
        //     ->distinct()
        //     ->pluck('created_by');

        $allUsers = Leads::whereIn('lead_owner', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('is_deleted', 0);
        })
            ->select('lead_owner')
            ->distinct()
            ->pluck('lead_owner');

        $userNames = DB::table('users')
            ->whereIn('id', $allUsers)
            ->pluck('name', 'id');

        // Get user images
        $userImages = DB::table('users')
            ->whereIn('id', $allUsers)
            ->pluck('image', 'id');

        // ✅ TOTAL (ONLY THESE BUCKETS)
        $totalData = Leads::select(
            'lead_owner',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
            ->groupBy('lead_owner')
            ->pluck('total', 'lead_owner');

        // ✅ BUCKET + SUB STATUS
        $data = CallBack::select(
            'created_by',
            'bucket',
            'status',
            DB::raw("COUNT(DISTINCT CONCAT(lead_id, '-', bucket, '-', status)) as total")
        )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('bucket', $statusColumns) // 🔥 IMPORTANT
            ->groupBy('created_by', 'bucket', 'status')
            ->get();

        $followupData = CallBack::select(
            'created_by',
            'followup_type',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('followup_type')
            ->groupBy('created_by', 'followup_type')
            ->get();

        // ✅ CALL STATUS DATA (Connected vs Not Connected)
        $callStatusData = CallBack::select(
            'created_by',
            'followup_type',
            'followup_status',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('followup_type', ['Call', 'WhatsApp Call'])
            ->whereIn('followup_status', ['Connected', 'Not Connected'])
            ->groupBy('created_by', 'followup_type', 'followup_status')
            ->get();

        // ✅ WHATSAPP STATUS DATA (Discussion Start vs No Response)
        $whatsappStatusData = CallBack::select(
            'created_by',
            'followup_status',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->where('followup_type', 'Whatsapp')
            ->whereIn('followup_status', ['Discussion Start', 'No Response'])
            ->groupBy('created_by', 'followup_status')
            ->get();

        // ✅ LEAD ENGAGEMENT STATUS DATA
        $engagementQuery = Leads::select(
            'lead_owner',
            'lead_engagement_status',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
            ->whereIn('lead_engagement_status', ['hot', 'warm', 'cold']);

        $statusQuery = Leads::select(
            'lead_owner',
            'lead_bucket_id',
            DB::raw("COUNT(*) as total")
        )
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
            ->whereIn('lead_bucket_id', [15, 23, 30, 48, 8])
            ->groupBy('lead_owner', 'lead_bucket_id')
            ->get();



        if ($engagementFilter) {
            $engagementQuery->where('lead_engagement_status', $engagementFilter);
        }

        $engagementData = $engagementQuery->groupBy('lead_owner', 'lead_engagement_status')->get();

        // ================================
        // DUPLICATE HOT LEADS COUNT
        // ================================

        // ================================
        // DUPLICATE HOT LEADS COUNT
        // ================================



        $duplicateHotCounts = $this->getDuplicateHotLeads(

            Leads::query()
                ->whereBetween('date', [$from, $to])

        )
            ->groupBy('lead_owner')
            ->map(fn($items) => $items->count());


        // ✅ HOT LEADS DATA WITH DETAILS (current hot leads)
        $hotLeads = Leads::with('user', 'bucket')
            ->select('id', 'lead_owner', 'lead_bucket_id', 'lead_status', 'campaign_name', 'lead_engagement_status', 'uid', 'applying_country_for_a_visa', 'what_course_are_you_planning_to_study', 'verified_lead', 'date')
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
            ->where('lead_engagement_status', 'hot')
            ->orderBy('id', 'desc')
            ->get();

        // ✅ STATUS TRANSITIONS DATA (WARM->HOT and HOT->WARM from callback_messages)
        $allStatusTransitions = [];
        $allStatusTransitions = CallBack::getStatusTransitions($from, $to);

        // ✅ FETCH LEAD DETAILS FOR TRANSITIONS (with user info for lead names)
        $warmToHotLeadIds = $allStatusTransitions['warm_to_hot'] ?? [];

        $hotToWarmLeadIds = $allStatusTransitions['hot_to_warm'] ?? [];

        // Get lead details with user info for display
        $leadsForTransitions = [];
        if (!empty($warmToHotLeadIds) || !empty($hotToWarmLeadIds)) {
            $leadsForTransitions = Leads::with('user')
                ->select('id', 'lead_owner', 'campaign_name', 'lead_engagement_status', 'uid', 'applying_country_for_a_visa', 'what_course_are_you_planning_to_study', 'verified_lead', 'date')
                ->whereIn('id', array_merge($warmToHotLeadIds, $hotToWarmLeadIds))
                ->get()
                ->keyBy('id');
        }

        $final = [];

        foreach ($allUsers as $userId) {

            $final[$userId] = [
                'name' => $userNames[$userId] ?? '-',
                'total' => $totalData[$userId] ?? 0,
                'statuses' => [],
                'followups' => [
                    'Call' => 0,
                    'WhatsApp Call' => 0,
                    'Whatsapp' => 0,
                ],
                'call_stats' => [
                    'Call' => ['Connected' => 0, 'Not Connected' => 0],
                    'WhatsApp Call' => ['Connected' => 0, 'Not Connected' => 0],
                ],
                'whatsapp_stats' => [
                    'Discussion Start' => 0,
                    'No Response' => 0,
                ],
                'engagement' => [
                    'hot' => 0,
                    'warm' => 0,
                    'cold' => 0,
                    'duplicate_hot' => 0,
                ],
                'status_counts' => [
                    15 => 0,
                    23 => 0,
                    30 => 0,
                    48 => 0,
                    8  => 0,
                ],
                'hot_leads' => [],
                'warm_to_hot' => [],
                'hot_to_warm' => []
            ];

            foreach ($followupData as $f) {
                if (isset($final[$f->created_by])) {

                    $type = $f->followup_type;

                    if (isset($final[$f->created_by]['followups'][$type])) {
                        $final[$f->created_by]['followups'][$type] = $f->total;
                    }
                }
            }

            // Process call status data
            foreach ($callStatusData as $c) {
                if ($c->created_by == $userId && isset($final[$c->created_by]['call_stats'][$c->followup_type])) {
                    $final[$c->created_by]['call_stats'][$c->followup_type][$c->followup_status] = $c->total;
                }
            }

            // Process whatsapp status data
            foreach ($whatsappStatusData as $w) {
                if ($w->created_by == $userId && isset($final[$w->created_by]['whatsapp_stats'][$w->followup_status])) {
                    $final[$w->created_by]['whatsapp_stats'][$w->followup_status] = $w->total;
                }
            }

            // Process engagement status data
            foreach ($engagementData as $e) {
                if ($e->lead_owner == $userId && isset($final[$userId]['engagement'][$e->lead_engagement_status])) {
                    $final[$userId]['engagement'][$e->lead_engagement_status] = $e->total;
                }
            }
            $final[$userId]['engagement']['duplicate_hot'] =
                $duplicateHotCounts[$userId] ?? 0;

            foreach ($statusQuery as $s) {

                if ($s->lead_owner == $userId) {

                    $final[$userId]['status_counts'][$s->lead_bucket_id] = $s->total;
                }
            }
            // ✅ PROCESS HOT LEADS (current hot leads)
            foreach ($hotLeads as $lead) {
                if ($lead->lead_owner == $userId) {
                    $final[$userId]['hot_leads'][] = [
                        'id' => $lead->id,
                        'campaign_name' => $lead->campaign_name ?? 'N/A',
                        'lead_name' => $lead->user->name ?? 'N/A',
                        'email' => $lead->user->email ?? 'N/A',
                        'contact_no' => $lead->user->contact_no ?? 'N/A',
                        'verified_lead' => $lead->verified_lead ?? false,
                        'country' => $lead->applying_country_for_a_visa ?? 'N/A',
                        'course' => $lead->what_course_are_you_planning_to_study ?? 'N/A',
                        'lead_bucket_name' => $lead->bucket->name ?? 'N/A',
                        'lead_status' => ucfirst($lead->lead_status ?? 'N/A'),
                        'date' => $lead->date ?? 'N/A',
                    ];
                }
            }

            // ✅ PROCESS STATUS TRANSITIONS - WARM TO HOT
            if (isset($allStatusTransitions['warm_to_hot'])) {
                foreach ($allStatusTransitions['warm_to_hot'] as $leadId) {
                    if (isset($leadsForTransitions[$leadId])) {
                        $lead = $leadsForTransitions[$leadId];
                        if ($lead->lead_owner != $userId) {
                            continue;
                        }
                        $final[$userId]['warm_to_hot'][] = [
                            'id' => $lead->id,
                            'campaign_name' => $lead->campaign_name ?? 'N/A',
                            'lead_name' => $lead->user->name ?? 'N/A',
                            'email' => $lead->user->email ?? 'N/A',
                            'contact_no' => $lead->user->contact_no ?? 'N/A',
                            'verified_lead' => $lead->verified_lead ?? false,
                            'country' => $lead->applying_country_for_a_visa ?? 'N/A',
                            'course' => $lead->what_course_are_you_planning_to_study ?? 'N/A',
                            'date' => $lead->date ?? 'N/A',
                        ];
                    }
                }
            }

            // ✅ PROCESS STATUS TRANSITIONS - HOT TO WARM
            if (isset($allStatusTransitions['hot_to_warm'])) {
                foreach ($allStatusTransitions['hot_to_warm'] as $leadId) {
                    if (isset($leadsForTransitions[$leadId])) {
                        $lead = $leadsForTransitions[$leadId];
                        if ($lead->lead_owner != $userId) {
                            continue;
                        }
                        $final[$userId]['hot_to_warm'][] = [
                            'id' => $lead->id,
                            'campaign_name' => $lead->campaign_name ?? 'N/A',
                            'lead_name' => $lead->user->name ?? 'N/A',
                            'email' => $lead->user->email ?? 'N/A',
                            'contact_no' => $lead->user->contact_no ?? 'N/A',
                            'verified_lead' => $lead->verified_lead ?? false,
                            'country' => $lead->applying_country_for_a_visa ?? 'N/A',
                            'course' => $lead->what_course_are_you_planning_to_study ?? 'N/A',
                            'date' => $lead->date ?? 'N/A',
                        ];
                    }
                }
            }

            // initialize
            foreach ($statusColumns as $bucket) {
                $final[$userId]['statuses'][$bucket] = [
                    'count' => 0,
                    'sub_status' => []
                ];
            }

            foreach ($data as $row) {

                if ($row->created_by == $userId) {

                    $bucket = $row->bucket;
                    $status = $row->status;

                    // bucket count
                    $final[$userId]['statuses'][$bucket]['count'] += $row->total;

                    // sub-status
                    if (!isset($final[$userId]['statuses'][$bucket]['sub_status'][$status])) {
                        $final[$userId]['statuses'][$bucket]['sub_status'][$status] = 0;
                    }

                    $final[$userId]['statuses'][$bucket]['sub_status'][$status] += $row->total;
                }
            }
        }
        return view('crm.lead.new-daily-report', compact('final', 'statusColumns', 'userImages'));
    }

    public function updateEngagementStatus(Request $request, Leads $lead)
    {
        $request->validate([
            'lead_engagement_status' => 'nullable|in:hot,warm,cold,dead'
        ]);

        $lead->update([
            'lead_engagement_status' => $request->lead_engagement_status
        ]);

        return response()->json([
            'message' => 'Engagement status updated successfully'
        ]);
    }

    public function followUpData(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $today = Carbon::today()->toDateString();

        $query = CallBack::with('user')->select(
            'created_by',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE 
        WHEN followup_type IS NOT NULL 
        THEN 1 ELSE 0 END
    ) as done_followups"),

            DB::raw("SUM(CASE 
        WHEN is_done = 0 
        AND DATE(next_followup_date) >= '$today'
        THEN 1 ELSE 0 END
    ) as planned_followups"),

            DB::raw("SUM(CASE 
        WHEN is_done = 0 
        AND DATE(next_followup_date) < '$today'
        THEN 1 ELSE 0 END
    ) as missed_followups"),

            DB::raw('SUM(CASE 
        WHEN  followup_type = "Call" 
        THEN 1 ELSE 0 END) as phone_call'),

            DB::raw('SUM(CASE 
        WHEN  followup_type = "Whatsapp Call" 
        THEN 1 ELSE 0 END) as whatsapp_call'),

            DB::raw('SUM(CASE 
        WHEN followup_type = "Whatsapp" 
        THEN 1 ELSE 0 END) as whatsapp'),

            DB::raw("SUM(CASE 
        WHEN followup_type = 'Call' AND followup_status = 'Connected' 
        THEN 1 ELSE 0 END
    ) as call_connected"),
            DB::raw("SUM(CASE 
        WHEN followup_type = 'Call' AND followup_status = 'Not Connected' 
        THEN 1 ELSE 0 END
    ) as call_not_connected"),

            DB::raw("SUM(CASE 
        WHEN followup_type = 'Whatsapp Call' AND followup_status = 'Connected' 
        THEN 1 ELSE 0 END
    ) as whatsapp_call_connected"),
            DB::raw("SUM(CASE 
        WHEN followup_type = 'Whatsapp Call' AND followup_status = 'Not Connected' 
        THEN 1 ELSE 0 END
    ) as whatsapp_call_not_connected"),
            DB::raw("SUM(CASE 
        WHEN followup_type = 'Whatsapp' AND followup_status = 'Discussion Start' 
        THEN 1 ELSE 0 END
    ) as discussion_start"),
            DB::raw("SUM(CASE 
        WHEN followup_type = 'Whatsapp' AND followup_status = 'No Response' 
        THEN 1 ELSE 0 END
    ) as no_response"),

        )
            ->whereNotNull('created_by');
        if ($from && $to) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);
        } elseif ($from) {
            $query->whereDate('created_at', '>=', $from);
        } elseif ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        $query->groupBy('created_by');

        // ================= SORTING =================

        $sortBy = $request->sort_by;
        $sortOrder = $request->sort_order == 'desc' ? 'desc' : 'asc';


        $allowedSorts = [
            'done_followups',
            'call_connected',
            'call_not_connected',
            'whatsapp_call_connected',
            'whatsapp_call_not_connected',
            'discussion_start',
            'no_response'
        ];

        if ($sortBy == 'name') {

            $query->join('users', 'users.id', '=', 'callback_messages.created_by')
                ->orderBy('users.name', $sortOrder);
        } elseif (in_array($sortBy, $allowedSorts)) {

            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->per_page ?? 20;

        $leads = $query->paginate($perPage)
            ->appends($request->query());

        return view('crm.lead.lead-data', compact('leads'));
    }

    public function callbackUpdate(Request $request, $id)
    {
        $callback = CallBack::findOrFail($id);

        $newcallback = $callback->replicate();

        $newcallback->followup_type = $request->followup_type;
        $newcallback->next_followup_date = $request->next_followup_date;
        $newcallback->message = $request->message;

        $newcallback->created_at = now();
        $newcallback->updated_at = now();

        $newcallback->save();

        $callback->is_done = 1;
        $callback->save();

        return redirect()->back()->with('success', 'updated successfully');
    }

    public function callbackDone(Request $request)
    {
        $id = $request->lead_id ?? null;
        if (!$id) {
            return back()->with('error', 'Lead ID is required');
        }

        $callback = CallBack::findOrFail($id);

        $callback->update([
            'is_done' => 1,
            'followup_status' => $request->followup_status ?? $callback->followup_status,
            'message' => $request->feedback ?? $callback->message,
        ]);
        return back()->with('success', 'Callback status updated');
    }

    public function campaignPerformance(Request $request)
    {
        $buckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->get();

        //  default grouping
        $groupBy = request('group_by', 'campaign_name');

        // 👉 allowed columns (security)
        $allowed = [
            'campaign' => 'campaign_name',
            'ad-name' => 'ad_name',
            'adset-name' => 'adset_name',
            'form-name' => 'form_name',
        ];

        // 👉 map dropdown value → column
        $column = $allowed[$groupBy] ?? 'campaign_name';

        $sortBy = $request->sort_by ?? '';
        $sortOrder = $request->sort_order ?? 'asc';

        $query = Leads::select(
            $column . ' as name',
            DB::raw('COUNT(*) as total_leads')
        );

        // ================= DYNAMIC BUCKET COUNTS =================
        foreach ($buckets as $bucket) {

            $slug = 'bucket_' . $bucket->id;

            $query->selectRaw("
    SUM(
        CASE
            WHEN leads.lead_bucket_id = {$bucket->id}
            THEN 1
            ELSE 0
        END
    ) as bucket_{$bucket->id}
");
        }

        // ================= JOIN + GROUP =================
        // $query->leftJoin('buckets', 'buckets.id', '=', 'leads.lead_bucket_id')
        //     ->whereNotNull($column)
        //     ->where($column, '!=', '')
        //     ->groupBy($column)
        //     ->orderByRaw('MAX(leads.created_at) DESC');

        $query->leftJoin('buckets', 'buckets.id', '=', 'leads.lead_bucket_id')
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column);

        // SORTING
        if ($sortBy == 'name') {

            $query->orderBy($column, $sortOrder);
        } elseif ($sortBy == 'total_leads') {

            $query->orderBy('total_leads', $sortOrder);
        } elseif (str_starts_with($sortBy, 'bucket_')) {

            $query->orderBy($sortBy, $sortOrder);
        } else {

            $query->orderByRaw('MAX(leads.created_at) DESC');
        }

        // 👉 optional campaign filter
        if (request()->filled('campaign_name')) {
            $query->where('campaign_name', request()->campaign_name);
        }
        if (request()->filled('adset_name')) {
            $query->where('adset_name', request()->adset_name);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();

            $query->whereBetween('leads.created_at', [$from, $to]);
        }
        if ($request->filled('from')) {
            $from = \Carbon\Carbon::parse($request->from)->toDateString();
            $query->where('leads.created_at', '>=', $from);
        }

        $perPage = $request->per_page ?? 20;
        $totals = (clone $query)->get();
        $data = $query
            ->paginate($perPage)
            ->appends($request->query());
        $campaigns = Leads::whereNotNull('campaign_name')
            ->distinct()
            ->pluck('campaign_name', 'campaign_name');

        $adsets = Leads::whereNotNull('adset_name')
            ->distinct()
            ->pluck('adset_name', 'adset_name');


        return view('crm.lead.campaign-performance', compact('data', 'totals', 'groupBy', 'campaigns', 'adsets', 'buckets'));
    }

    public function sourcePerformance(Request $request)
    {
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

        $buckets = Bucket::whereNull('parent_id')
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->get();


        $baseQuery = Leads::select(DB::raw("
    CASE
        WHEN platform IS NULL OR TRIM(platform) = '' THEN 'other'

        WHEN LOWER(TRIM(platform)) IN (
            'website',
            'referral',
            'social media',
            'facebook',
            'instagram',
            'whatsapp',
            'advertisement',
            'landing page',
            'manual import'
        )
        THEN LOWER(TRIM(platform))

        ELSE 'other'
    END as source_group,

    leads.lead_bucket_id
"));
        if ($request->filled('source')) {
            $source = strtolower(trim($request->source));

            if ($source != 'other') {
                $baseQuery->whereRaw("
            LOWER(TRIM(platform)) = ?
        ", [$source]);
            } else {
                $baseQuery->whereRaw("
            platform IS NULL
            OR TRIM(platform) = ''
            OR LOWER(TRIM(platform)) = 'other'
            OR LOWER(TRIM(platform)) NOT IN (
                'website',
                'referral',
                'social media',
                'facebook',
                'instagram',
                'whatsapp',
                'advertisement',
                'landing page',
                'manual import'
            )
        ");
            }
        }
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->from)->startOfDay();
            $to = \Carbon\Carbon::parse($request->to)->endOfDay();

            $baseQuery->whereBetween('leads.created_at', [$from, $to]);
        }
        if ($request->filled('from')) {
            $from = \Carbon\Carbon::parse($request->from)->toDateString();
            $baseQuery->where('leads.created_at', '>=', $from);
        }

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as mapped"))
            ->mergeBindings($baseQuery->getQuery())
            ->select(
                'source_group',
                DB::raw('COUNT(*) as total_leads')
            );

        foreach ($buckets as $bucket) {

            $slug = \Str::slug($bucket->name, '_');

            $query->selectRaw("
        SUM(
            CASE
                WHEN mapped.lead_bucket_id = {$bucket->id}
                THEN 1
                ELSE 0
            END
        ) as `$slug`
    ");
        }

        $query->leftJoin('buckets', 'buckets.id', '=', 'mapped.lead_bucket_id')
            ->groupBy('source_group');

        $sortBy = $request->get('sort_by', 'total_leads');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy == 'source_group') {

            $query->orderBy('source_group', $sortOrder);
        } elseif ($sortBy == 'total_leads') {

            $query->orderBy('total_leads', $sortOrder);
        } else {

            // bucket sorting
            foreach ($buckets as $bucket) {

                $slug = \Str::slug($bucket->name, '_');

                if ($sortBy == $slug) {

                    $query->orderBy($slug, $sortOrder);
                }
            }
        }

        $perPage = $request->per_page ?? 20;
        $totals = (clone $query)->get();
        $sourcesData = $query->paginate($perPage)
            ->appends($request->query());

        $sources = collect([
            'Website',
            'Referral',
            'Social Media',
            'Facebook',
            'Instagram',
            'Whatsapp',
            'Advertisement',
            'Other',
            'Landing Page',
            'Manual Import'
        ]);

        return view(
            'crm.lead.source-performance',
            compact('sourcesData', 'sources', 'totals', 'buckets')
        );
    }
    // public function councillorReport(Request $request)
    // {
    //     $query = Leads::with('owner')->select(
    //         'lead_owner',
    //         DB::raw('COUNT(*) as total_leads'),
    //         DB::raw("SUM(CASE WHEN buckets.name = 'Untouched leads' THEN 1 ELSE 0 END) as untouched"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Not Connected' THEN 1 ELSE 0 END) as not_connected"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Counselling in Progress' THEN 1 ELSE 0 END) as counselling"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Application Process' THEN 1 ELSE 0 END) as application"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Offer Stage' THEN 1 ELSE 0 END) as offer_stage"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Visa Process' THEN 1 ELSE 0 END) as visa_process"),

    //         DB::raw("SUM(CASE WHEN buckets.name = 'Converted' AND buckets.parent_id IS NULL  THEN 1 ELSE 0 END) as converted"),
    //         DB::raw("SUM(CASE WHEN buckets.name = 'Lost' THEN 1 ELSE 0 END) as lost"),
    //     )
    //         ->leftJoin('buckets', 'buckets.id', '=', 'leads.lead_bucket_id')
    //         ->groupBy('lead_owner');

    //     if (request()->has('councillor_name') && !empty(request()->councillor_name)) {
    //         $query->where('lead_owner', request()->councillor_name);
    //     }

    //     if ($request->filled('from') && $request->filled('to')) {
    //         $from = \Carbon\Carbon::parse($request->from)->startOfDay();
    //         $to = \Carbon\Carbon::parse($request->to)->endOfDay();

    //         $query->whereBetween('leads.created_at', [$from, $to]);
    //     }
    //     if ($request->filled('from')) {
    //         $from = \Carbon\Carbon::parse($request->from)->toDateString();
    //         $query->where('leads.created_at', '>=', $from);
    //     }

    //     $totals = (clone $query)->get();
    //     $data = $query->get();

    //     $councillors = User::whereIn('id', Leads::pluck('lead_owner')->unique())->pluck('name', 'id');
    //     return view('crm.lead.councillor-report', compact('data', 'councillors', 'totals'));
    // }

    public function councillorReport(Request $request)
    {
        // parent null buckets
        $buckets = Bucket::whereNull('parent_id')->where('is_deleted', 0)->get();

        $query = Leads::with('owner')
            ->select('lead_owner')
            ->selectRaw('COUNT(*) as total_leads');

        // dynamic bucket counts
        foreach ($buckets as $bucket) {

            $slug = \Str::slug($bucket->name, '_');

            $query->selectRaw("
            SUM(
                CASE 
                    WHEN leads.lead_bucket_id = {$bucket->id}
                    THEN 1 
                    ELSE 0 
                END
            ) as `$slug`
        ");
        }

        $query->leftJoin('buckets', 'buckets.id', '=', 'leads.lead_bucket_id')
            ->groupBy('lead_owner');

        $sortBy = $request->get('sort_by', 'total_leads');
        $sortOrder = $request->get('sort_order', 'desc');

        $query->orderBy($sortBy, $sortOrder);

        // councillor filter
        if ($request->filled('councillor_name')) {

            $query->where('lead_owner', $request->councillor_name);
        }

        // date filter
        if ($request->filled('from') && $request->filled('to')) {

            $from = \Carbon\Carbon::parse($request->from)->startOfDay();

            $to = \Carbon\Carbon::parse($request->to)->endOfDay();

            $query->whereBetween('leads.created_at', [$from, $to]);
        } elseif ($request->filled('from')) {

            $from = \Carbon\Carbon::parse($request->from)->startOfDay();

            $query->where('leads.created_at', '>=', $from);
        }
        $perPage = $request->per_page ?? 20;

        $totals = (clone $query)->get();

        $data = $query->paginate($perPage)
            ->appends($request->query());

        $councillors = User::whereIn(
            'id',
            Leads::pluck('lead_owner')->unique()
        )->pluck('name', 'id');

        return view(
            'crm.lead.councillor-report',
            compact('data', 'councillors', 'totals', 'buckets')
        );
    }
    public function bulkDelete(Request $request)
    {
        if (!$request->filled('ids')) {
            return back()->with('error', 'No leads selected');
        }

        $ids = explode(',', $request->ids);

        DB::beginTransaction();

        try {

            CallBack::whereIn('lead_id', $ids)->delete();

            Leads::whereIn('id', $ids)->delete();

            DB::commit();

            return back()->with('success', 'Selected leads deleted successfully!');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function fetchTemplates()
    {
        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'X-AiSensy-Project-API-Pwd' => '222488aa8678e32a9069d'
            ])->get('https://apis.aisensy.com/project-apis/v1/project/67e109077c4b230bed2fb1ff/wa_template/');


            if ($response->successful()) {

                $templates = collect($response->json()['template'] ?? [])
                    ->where('status', 'APPROVED')
                    ->map(function ($t) {
                        return [
                            'id' => $t['id'],
                            'name' => $t['name'],
                            'message' => $t['text'] ?? ''
                        ];
                    })
                    ->values();

                return response()->json([
                    'status' => true,
                    'templates' => $templates
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'API failed'
            ], 500);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }



    public function sendSMS(Request $request)
    {
        $numbers = array_filter($request->numbers);
        $message = $request->message;

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');

        $client = new Client($sid, $token);

        try {
            foreach ($numbers as $number) {
                $client->messages->create(
                    $number,
                    [
                        'from' => env('TWILIO_FROM'),
                        'body' => $message
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'SMS sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function leadActivity(Request $request)
    {
        $from = $request->from;
        $to = $request->to;


        $query = CallBack::with(['lead.user', 'lead.owner'])
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereDate('created_at', '>=', $from)
                    ->whereDate('created_at', '<=', $to);
            }, function ($q) use ($from) {
                if ($from) {
                    $q->whereDate('created_at', '>=', $from)
                        ->whereDate('created_at', '<=', now()->toDateString());
                } else {
                    // No Date => Today only
                    $q->whereDate('created_at', now()->toDateString());
                }
            })

            ->latest();

        if ($request->filled('name')) {
            $query->whereHas('lead.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('owner_id')) {

            if ($request->owner_id == 'null') {
                $query->whereHas('lead', function ($q) {
                    $q->whereNull('lead_owner');
                });
            } else {
                $query->whereHas('lead', function ($q) use ($request) {
                    $q->where('lead_owner', $request->owner_id);
                });
            }
        }
        $perPage = $request->per_page ?? 20;

        $callbacks = $query->paginate($perPage)
            ->appends($request->query());
        $owners = User::whereIn('role_id', [1, 3])->where('is_deleted', 0)->get();



        return view('crm.lead.activity-lead-report', compact('callbacks', 'owners'));
    }
    public function exportLeads(Request $request)
    {
        return Excel::download(new LeadsExcelExport($request->all()), 'leads.xlsx');
    }

    public function getLeadsByType(Request $request)
    {
        try {

            $bucketId = $request->bucket_id;
            $userId = $request->ower_id;

            $from = $request->from;
            $to = $request->to;

            $range = $request->range;

            $today = now()->toDateString();

            $bucketNames = [
                15 => 'Counselling in Progress',
                23 => 'Application Process',
                30 => 'Offer Stage',
                48 => 'Converted',
                8  => 'Not Connceted',
            ];

            $title = $bucketNames[$bucketId] ?? 'Leads';

            $query = Leads::with('user', 'bucket');

            // HOT LEADS
            if ($bucketId == 'hot') {

                $title = 'Hot Leads';

                $query->where('lead_engagement_status', 'hot');
            } elseif ($bucketId == 'duplicate_hot') {

                $title = 'Duplicate Hot Leads';

                $duplicateLeadIds = $this->getDuplicateHotLeads(
                    clone $query,
                    $range
                )->pluck('id');

                $query->whereIn('id', $duplicateLeadIds);
            } else {

                $query->where('lead_bucket_id', $bucketId);
            }

            // USER FILTER
            if (!empty($userId)) {
                $query->where('lead_owner', $userId);
            }

            // DATE FILTER
            // if (!empty($from) && !empty($to)) {

            //     if ($range == 'today') {

            //         $query->whereBetween('date', [$from, $to]);
            //     } else {

            //         $query->whereDate('date', '<', $from);
            //     }
            // } else {

            //     if ($range == 'today') {

            //         $query->whereDate('date', $today);
            //     } else {

            //         $query->whereDate('date', '!=', now()->toDateString());
            //     }
            // }

            $isDuplicateHot = ($bucketId == 'duplicate_hot');


            if (!empty($from) && !empty($to)) {

                if ($range == 'today') {

                    $query->whereBetween('date', [$from, $to]);
                } elseif ($range == 'previous') {

                    // 🔥 NEW ADDITION (previous)
                    if ($isDuplicateHot) {
                        $query->whereDate('date', '!=', $today);
                    } else {
                        $query->whereDate('date', '!=', $today)
                            ->whereHas('messages', function ($q) use ($today) {
                                $q->whereDate('created_at', $today);
                            });
                    }
                } else {

                    $query->whereDate('date', '<', $from);
                }
            } else {

                if ($range == 'today') {
                    $query->whereDate('date', $today);
                } elseif ($range == 'previous') {

                    // 🔥 NEW ADDITION (previous)
                    if ($isDuplicateHot) {
                        $query->whereDate('date', '!=', $today);
                    } else {
                        $query->whereDate('date', '!=', $today)
                            ->whereHas('messages', function ($q) use ($today) {
                                $q->whereDate('created_at', $today);
                            });
                    }
                } else {

                    $query->whereDate('date', '!=', now()->toDateString());
                }
            }

            $dublicate_hots = '';
            if ($isDuplicateHot) {

                $dublicate_hots = CallBack::with('lead.user', 'user')
                    ->whereIn('lead_id', function ($q) use ($userId, $today, $range) {

                        $q->select('callback_messages.lead_id')
                            ->from('callback_messages')
                            ->join('leads', 'leads.id', '=', 'callback_messages.lead_id')
                            ->where('leads.lead_owner', $userId)
                            ->when($range == 'today', function ($q) use ($today) {
                                $q->whereDate('leads.date', $today);
                            })
                            ->groupBy('callback_messages.lead_id')
                            ->havingRaw("SUM(callback_messages.lead_engagement_status = 'hot') >= 2")
                            ->havingRaw("MIN(callback_messages.lead_engagement_status) <> MAX(callback_messages.lead_engagement_status)");
                    })
                    ->when($range == 'today', function ($q) use ($today) {
                        $q->whereDate('created_at', $today);
                    })
                    ->orderBy('lead_id')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }


            $leads = $query->latest()
                ->get()
                ->map(function ($lead) {

                    return [

                        'id' => $lead->id,

                        'lead_name' => $lead->user->name ?? 'N/A',
                        'email' => $lead->user->email ?? 'N/A',
                        'contact_no' => $lead->user->contact_no ?? 'N/A',

                        'country' => $lead->applying_country_for_a_visa ?? 'N/A',
                        'course' => $lead->what_course_are_you_planning_to_study ?? 'N/A',
                        'campaign_name' => $lead->campaign_name ?? 'N/A',

                        'date' => $lead->date ?? null,
                        'verified_lead' => $lead->verified_lead ?? 0,
                        'lead_bucket_name' => $lead->bucket->name ?? 'N/A',
                        'lead_status' => $lead->lead_status ?? 'N/A',
                    ];
                });

            return response()->json([
                'success' => true,
                'title' => $title,
                'leads' => $leads,
                'dublicate_hots' => $dublicate_hots,
                'is_history' => $isDuplicateHot ? true : false,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserReportData(Request $request)
    {
        $userId = $request->user_id;

        $range = $request->range;

        $from = $request->from;
        $to = $request->to;

        $today = now()->toDateString();

        $query = Leads::where('lead_owner', $userId);

        // DATE FILTER
        if ($range == 'previous') {

            $query->whereDate('date', '!=', $today)
                ->whereHas('messages', function ($q) use ($today) {
                    $q->whereDate('created_at', $today);
                });
        } else {
            if (!empty($from) && !empty($to)) {

                if ($range == 'today') {

                    $query->whereBetween('date', [$from, $to]);
                } else {

                    $query->whereDate('date', '<', $from);
                }
            } else {
                if ($range == 'today') {

                    $query->whereDate('date', $today);
                } else {

                    $query->whereDate('date', '!=', now()->toDateString());
                }
            }
        }

        $leads = $query->get();
        $duplicateQuery = Leads::where('lead_owner', $userId);

        // previous me sirf itna check hoga ki lead aaj ki nahi ho
        if ($range == 'previous') {

            $duplicateQuery->whereDate('date', '!=', $today);
        } elseif ($range == 'today') {

            $duplicateQuery->whereDate('date', $today);
        } else {

            $duplicateQuery->whereDate('date', '!=', $today);
        }

        $duplicateHotCount = $this->getDuplicateHotLeads(
            $duplicateQuery,
            $range
        )->count();
        return response()->json([

            'total' => $leads->count(),

            'status_counts' => [
                15 => $leads->where('lead_bucket_id', 15)->count(),
                23 => $leads->where('lead_bucket_id', 23)->count(),
                30 => $leads->where('lead_bucket_id', 30)->count(),
                48 => $leads->where('lead_bucket_id', 48)->count(),
                8  => $leads->where('lead_bucket_id', 8)->count(),
            ],

            'engagement' => [
                'hot' => $leads->where('lead_engagement_status', 'hot')->count(),
                'warm' => $leads->where('lead_engagement_status', 'warm')->count(),
                'cold' => $leads->where('lead_engagement_status', 'cold')->count(),
                'duplicate_hot' => $duplicateHotCount,
            ]

        ]);
    }

    private function getDuplicateHotLeads($query, $range = 'today')
    {
        $today = now()->toDateString();

        return $query->with(['messages' => function ($q) use ($range, $today) {

            // PREVIOUS


            // TODAY
            if ($range == 'today') {

                $q->whereDate('created_at', $today);
            }
        }])->get()

            ->filter(function ($lead) {

                $statuses = $lead->messages
                    ->sortBy('created_at')
                    ->pluck('lead_engagement_status')
                    ->map(fn($s) => strtolower(trim($s)))
                    ->toArray();

                $firstHot = array_search('hot', $statuses);

                if ($firstHot === false) {
                    return false;
                }

                $nonHotAfter = false;

                for ($i = $firstHot + 1; $i < count($statuses); $i++) {

                    if ($statuses[$i] !== 'hot') {
                        $nonHotAfter = true;
                    }

                    if ($nonHotAfter && $statuses[$i] === 'hot') {
                        return true;
                    }
                }

                return false;
            });
    }

    public function bulkOwnerUpdate(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required',
            'lead_owner' => 'required'
        ]);

        $leadIds = explode(',', $request->lead_ids);

        Leads::whereIn('id', $leadIds)
            ->update([
                'lead_owner' => $request->lead_owner
            ]);

        return redirect()->back()->with(
            'success',
            'Lead owner updated successfully'
        );
    }
}

