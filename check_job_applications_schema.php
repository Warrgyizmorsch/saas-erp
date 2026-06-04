<?php

include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== INITIALIZING TENANCY ===\n";
$tenant = \App\Models\Tenant::where('id', 'mewar-1')->first();
if ($tenant) {
    tenancy()->initialize($tenant);
}

try {
    echo "Attempting to create candidate...\n";
    $candidate = \Modules\HRMS\App\Models\JobVacancy::create([
        'name' => 'Test Candidate',
        'email' => 'test@candidate.com',
        'phone' => '1234567890',
        'department_id' => 1,
        'designation' => 'Frontend Developer',
        'qualification' => 'B.Tech',
        'experience' => '1 year',
        'interview_date' => date('Y-m-d'),
        'interview_time' => '10:00',
        'interviewer_id' => 7,
        'interview_details' => 'Meeting details or link',
        'status' => 'Pending',
        'resume' => 'resumes/test.pdf'
    ]);
    echo "Success! Created candidate ID: {$candidate->id}\n";
    // Delete it so we don't pollute database
    $candidate->delete();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
