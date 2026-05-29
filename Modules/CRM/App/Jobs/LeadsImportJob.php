<?php

namespace Modules\CRM\App\Jobs;

use Modules\Shared\App\Models\User;
use Modules\CRM\App\Models\Leads;
use Modules\CRM\App\Models\LeadImportJob;
use Modules\CRM\App\Models\LeadAttribute;
use Modules\CRM\App\Models\LeadQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class LeadsImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $importJobId;
    public ?int $uploadedBy;

    public function __construct(int $importJobId, ?int $uploadedBy = null)
    {
        $this->importJobId = $importJobId;
        $this->uploadedBy = $uploadedBy;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $importJob = LeadImportJob::find($this->importJobId);
        if (!$importJob) {
            Log::error("Import job not found: ID {$this->importJobId}");
            return;
        }

        $importJob->update(['status' => 'processing', 'processed_rows' => 0]);

        $fullPath = Storage::path($importJob->file_path);
        if (!file_exists($fullPath)) {
            Log::error("File not found at: {$fullPath}");
            $importJob->update(['status' => 'failed']);
            return;
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();

        $row1 = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1] ?? [];
        // dd($row1);
        $norm = fn(string $h) => trim(preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower(str_replace(['’', "'"], '', $h))), '_');

        $fieldRules = [

            // 1. DESTINATION
            'destination' => [
                'primary' => ['country', 'destination'],
                'secondary' => ['study', 'apply', 'planning', 'interested', 'overseas'],
                'db' => 'applying_country_for_a_visa',
            ],

            // 2. INTAKE
            'intake' => [
                'primary' => ['intake'],
                'secondary' => ['academic', 'admission', 'target'],
                'db' => 'whats_your_preferred_intake',
            ],

            // 3. BUDGET
            'budget' => [
                'primary' => ['budget'],
                'secondary' => ['range', 'estimate', 'expected', 'approximate'],
                'db' => 'budget',
            ],

            // 4. PROGRAM (NO COMMON WORD → INTENT BASED)
            'program' => [
                'primary' => ['study', 'course', 'program', 'pursue'],
                'secondary' => ['level', 'intend', 'planning', 'apply'],
                'db' => 'what_course_are_you_planning_to_study',
            ],

            // 5. EDUCATION LEVEL (NEW)
            'education_level' => [
                'primary' => ['education', 'qualification'],
                'secondary' => ['current', 'highest', 'academic', 'present'],
                'db' => 'highest_completed',
            ],

            // 6. ENGLISH TEST STATUS (NEW)
            'english_test' => [
                'primary' => ['ielts', 'english'],
                'secondary' => ['test', 'score', 'proficiency'],
                'db' => 'english_test_status', // <-- confirm column name
            ],
        ];

        $headersRaw = array_values($row1);
        $letters = array_keys($row1);

        $detectedFields = [];

        foreach ($headersRaw as $i => $header) {
            $normalized = $norm($header);

            foreach ($fieldRules as $key => $rule) {
                $score = 0;

                foreach ($rule['primary'] as $word) {
                    if (str_contains($normalized, $word)) $score += 3;
                }

                foreach ($rule['secondary'] as $word) {
                    if (str_contains($normalized, $word)) $score += 1;
                }

                // if ($score > 0) {
                //     if (!isset($detectedFields[$key]) || $score > $detectedFields[$key]['score']) {
                //         $detectedFields[$key] = [
                //             'column' => $letters[$i],
                //             'score'  => $score,
                //             'header' => $header,
                //         ];
                //     }
                // }
                $minScore = [
                    'english_test'    => 4, // HARD match only
                    'destination'     => 3,
                    'intake'          => 3,
                    'budget'          => 3,
                    'program'         => 3,
                    'education_level' => 3,
                ];
                if ($score < ($minScore[$key] ?? 1)) {
                    continue; 
                }

                if (!isset($detectedFields[$key]) || $score > $detectedFields[$key]['score']) {
                    $detectedFields[$key] = [
                        'column' => $letters[$i],
                        'score'  => $score,
                        'header' => $header,
                    ];
                }


            }
        }

        $map = [];
        foreach ($headersRaw as $i => $hdr) {
            $map[$norm((string)$hdr)] = $letters[$i] ?? null;
        }

        $cell = function (int $rowIndex, string $header) use ($map, $sheet) {
            $col = $map[$header] ?? null;
            if (!$col) return null;
            $v = $sheet->getCell("{$col}{$rowIndex}")->getValue();
            return is_null($v) ? null : trim((string)$v);
        };

        $getAuto = function (int $row, string $field) use ($detectedFields, $sheet) {
            if (!isset($detectedFields[$field])) return null;
            return trim((string)$sheet
                ->getCell($detectedFields[$field]['column'] . $row)
                ->getValue());
        };
        
        $getName = function (int $row) use ($cell, $map) {

            $first = $cell($row, 'first_name');
            $last  = $cell($row, 'last_name');

            if ($first || $last) {
                return [
                    trim((string)$first),
                    trim((string)$last),
                ];
            }

            $full = $cell($row, 'full_name') ?? $cell($row, 'name');

            if ($full) {
                $parts = preg_split('/\s+/', trim($full), 2);
                return [
                    $parts[0] ?? '',
                    $parts[1] ?? '',
                ];
            }

            return ['', ''];
        };

        Log::info('Detected import field mapping', $detectedFields);

        DB::beginTransaction();
        try {
            $processed = [];

            for ($r = 2; $r <= $highestRow; $r++) {
                [$firstName, $lastName] = $getName($r);
                if (!$firstName && !$lastName) {
                    Log::warning("Name missing at row {$r}");
                }

                $email = strtolower(trim($cell($r, 'email') ?? ''));

                // --- Normalize phone ---
                $phoneRaw = trim((string)($cell($r, 'phone_number') ?? ''));
                $phoneRaw = str_replace([' ', '-', '(', ')'], '', $phoneRaw);

                [$countryCode, $phone] = $this->extractPhoneAndCountry($phoneRaw);

                if (!$phone) {
                    Log::warning("Invalid or missing phone skipped: {$phoneRaw}");
                    continue;
                }

                // Skip duplicates within same import
                $uniqueKey = $phone . '|' . $email;
                if (isset($processed[$uniqueKey])) {
                    Log::info("Duplicate in same file skipped: {$phone}, {$email}");
                    continue;
                }
                $processed[$uniqueKey] = true;

                // Dummy email if missing
                if (empty($email)) {
                    $lastUserId = User::max('id') ?? 0;
                    $email = 'user' . ($lastUserId + $r) . '@demo.com';
                }

                // Find or create user
                $user = User::where(function ($q) use ($phone, $email) {
                    if ($phone && $email) $q->where('contact_no', $phone)->orWhere('email', $email);
                    elseif ($phone) $q->where('contact_no', $phone);
                    elseif ($email) $q->where('email', $email);
                })->first();

                if (!$user) {
                    $fullName = trim($firstName . ' ' . $lastName) ?: 'user' . ((User::max('id') ?? 0) + 1);
                    $user = User::create([
                        'name' => $fullName,
                        'email' => $email,
                        'contact_no' => $phone,
                        'country_code' => $countryCode,
                        'city' => $cell($r, 'city'),
                        'role_id' => 2,
                        'password' => Hash::make('user@123'),
                    ]);
                } elseif (empty($user->country_code) && $countryCode) {
                    $user->update(['country_code' => $countryCode]);
                }

                // --- Created date ---
                $dateCell = $cell($r, 'created_time');
                if ($dateCell !== null && $dateCell !== '') {
                    if (is_numeric($dateCell)) {
                        $date = Carbon::instance(ExcelDate::excelToDateTimeObject((float)$dateCell))->toDateString();
                    } else {
                        try { $date = Carbon::parse($dateCell)->toDateString(); } 
                        catch (\Throwable) { $date = now()->toDateString(); }
                    }
                } else {
                    $date = now()->toDateString();
                }

                // $visaCountry = $cell($r, 'confirm_the_country_for_which_you_are_applying_for_visa');
                $visaCountry = $getAuto($r, 'destination');

                // Skip duplicate lead
                // $existingLead = Leads::where('uid', $user->id)
                //     ->where('applying_country_for_a_visa', $visaCountry)
                //     ->where('date', $date)
                //     ->first();
            //    $existingLeadQuery = Leads::where('uid', $user->id)->where('date', $date);

            //     if ($visaCountry) {
            //         $existingLeadQuery->where('applying_country_for_a_visa', $visaCountry);
            //     }

            //     $existingLead = $existingLeadQuery->first();

            //     $existingLead = Leads::whereHas('user', function ($q) use ($phone, $email) {
            //     $q->where('contact_no', $phone);

            //     if (!empty($email) && !str_contains($email, '@demo.com')) {
            //         $q->orWhere('email', $email);
            //     }
            //     })->first();

            //     if ($existingLead) {
            //         Log::info("Duplicate lead skipped by phone/email", [
            //             'phone' => $phone,
            //             'email' => $email,
            //             'existing_lead_id' => $existingLead->id,
            //         ]);
            //         continue;
            //     }

                // --- Create lead ---
                $lead = Leads::create([
                    'uid' => $user->id,
                    'date' => $date,
                    'campaign_name' => $cell($r, 'campaign_name'),
                    'adset_name' => $cell($r, 'adset_name'),
                    'ad_name' => $cell($r, 'ad_name'),
                    'form_name' => $cell($r, 'form_name'),
                    'platform' => $cell($r, 'platform'),
                    // 'whats_your_preferred_intake' => $cell($r, 'what_is_your_preferred_intake'),
                    // 'budget' => $cell($r, 'what_is_your_budget'),
                    // 'applying_country_for_a_visa' => $visaCountry,
                    // 'what_course_are_you_planning_to_study' => $cell($r, 'what_course_are_you_planning_to_study'),
                    'whats_your_preferred_intake' => $getAuto($r, 'intake'),
                    'budget' => $getAuto($r, 'budget'),
                    'applying_country_for_a_visa' => $getAuto($r, 'destination'),
                    'what_course_are_you_planning_to_study' => $getAuto($r, 'program'),
                    'highest_completed' => $getAuto($r, 'education_level'),
                    'english_test_status' => $getAuto($r, 'english_test'),
                    'lead_status' => 'Yet to Call',
                    'lead_bucket_id' => 1,
                    'lead_owner' => $this->uploadedBy,
                    'imported_by' => $this->uploadedBy,
                ]);

                // --- Dynamic fields ---
                $activeQuestions = LeadQuestion::where('is_active', 1)
                    ->pluck('field_name')
                    ->map(fn($f) => $norm($f))
                    ->toArray();

                $standardFields = [
                    'first_name','last_name','email','phone_number','city','created_time',
                    'campaign_name','adset_name','ad_name','form_name','platform',
                    'what_is_your_preferred_intake','what_is_your_budget',
                    'confirm_the_country_for_which_you_are_applying_for_visa',
                    'what_course_are_you_planning_to_study'
                ];

                foreach ($row1 as $colLetter => $rawHeader) {
                    $normalized = $norm($rawHeader);
                    if (in_array($normalized, $standardFields)) continue;
                    if (!in_array($normalized, $activeQuestions)) continue;

                    $value = $sheet->getCell("{$colLetter}{$r}")->getValue();
                    if ($value !== null && $value !== '') {
                        LeadAttribute::create([
                            'lead_id' => $lead->id,
                            'field_name' => $normalized,
                            'field_value' => $value,
                        ]);
                    }
                }

                $importJob->increment('processed_rows');
            }

            DB::commit();
            $importJob->update(['status' => 'completed']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Lead import failed: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            $importJob->update(['status' => 'failed']);
            throw $e;
        }
    }

    /**
     * Extract country code and 10-digit phone
     */
    private function extractPhoneAndCountry(string $phoneRaw): array
    {
        // Remove any non-digit except +
        $phoneRaw = preg_replace('/[^\d\+]/', '', $phoneRaw);

        // +CCXXXXXXXXXX
        if (preg_match('/^\+?(\d{1,3})(\d{10})$/', $phoneRaw, $matches)) {
            return ['+' . $matches[1], $matches[2]];
        }

        // 0XXXXXXXXXX -> default India
        if (preg_match('/^0?(\d{10})$/', $phoneRaw, $matches)) {
            return ['+91', $matches[1]];
        }

        // 11+ digits starting with country code
        if (preg_match('/^(\d{11,14})$/', $phoneRaw, $matches)) {
            $number = substr($matches[1], -10);
            $code = '+' . substr($matches[1], 0, strlen($matches[1]) - 10);
            return [$code, $number];
        }

        return [null, null]; // invalid
    }
}

