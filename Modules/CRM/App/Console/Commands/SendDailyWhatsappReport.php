<?php

namespace Modules\CRM\App\Console\Commands;

use Illuminate\Console\Command;
use Modules\CRM\App\Models\CallBack;
use Modules\CRM\App\Models\Leads;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

class SendDailyWhatsappReport extends Command
{
    protected $signature = 'report:send-whatsapp';

    protected $description = 'Send daily whatsapp report to fixed number';

    public function handle()
    {
        try {

            // ONLY TODAY DATA
            $today = now()->toDateString();

            // FIXED WHATSAPP NUMBER
            $fixedNumber = '916265455843';

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

            // SAME USERS AS UI
            $allUsers = CallBack::whereIn('created_by', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('is_deleted', 0);
            })
                ->select('created_by')
                ->distinct()
                ->pluck('created_by');

            $users = DB::table('users')
                ->whereIn('id', $allUsers)
                ->get();

            // TWILIO
            $twilio = new Client(
                env('TWILIO_SID'),
                env('TWILIO_AUTH_TOKEN')
            );

            foreach ($users as $user) {

                // TOTAL LEADS
                $total = CallBack::where('created_by', $user->id)
                    ->whereDate('created_at', $today)
                    ->whereIn('bucket', $statusColumns)
                    ->select(DB::raw("COUNT(DISTINCT CONCAT(lead_id, '-', status)) as total"))
                    ->value('total') ?? 0;

                // ENGAGEMENT DATA
                $engagementData = Leads::select(
                    'lead_engagement_status',
                    DB::raw("COUNT(*) as total")
                )
                    ->where('lead_owner', $user->id)
                    ->whereDate('date', $today)
                    ->whereIn('lead_engagement_status', ['hot', 'warm', 'cold'])
                    ->groupBy('lead_engagement_status')
                    ->pluck('total', 'lead_engagement_status');

                $hot = $engagementData['hot'] ?? 0;
                $warm = $engagementData['warm'] ?? 0;
                $cold = $engagementData['cold'] ?? 0;

                // STATUS DATA
                $statusData = CallBack::select(
                    'bucket',
                    'status',
                    DB::raw("COUNT(DISTINCT CONCAT(lead_id, '-', bucket, '-', status)) as total")
                )
                    ->where('created_by', $user->id)
                    ->whereDate('created_at', $today)
                    ->whereIn('bucket', $statusColumns)
                    ->groupBy('bucket', 'status')
                    ->get();

                // FOLLOWUPS
                $followups = CallBack::select(
                    'followup_type',
                    DB::raw("COUNT(*) as total")
                )
                    ->where('created_by', $user->id)
                    ->whereDate('created_at', $today)
                    ->whereNotNull('followup_type')
                    ->groupBy('followup_type')
                    ->pluck('total', 'followup_type');

                // CALL + WHATSAPP CALL STATS
                $callStats = CallBack::select(
                    'followup_type',
                    'followup_status',
                    DB::raw("COUNT(*) as total")
                )
                    ->where('created_by', $user->id)
                    ->whereDate('created_at', $today)
                    ->whereIn('followup_type', ['Call', 'WhatsApp Call'])
                    ->whereIn('followup_status', ['Connected', 'Not Connected'])
                    ->groupBy('followup_type', 'followup_status')
                    ->get();

                // WHATSAPP MESSAGE STATS
                $whatsappStats = CallBack::select(
                    'followup_status',
                    DB::raw("COUNT(*) as total")
                )
                    ->where('created_by', $user->id)
                    ->where('followup_type', 'Whatsapp')
                    ->whereDate('created_at', $today)
                    ->whereIn('followup_status', ['Discussion Start', 'No Response'])
                    ->groupBy('followup_status')
                    ->pluck('total', 'followup_status');

                // ================= MESSAGE =================

                $message = "📊 DAILY LEAD REPORT\n\n";

                $message .= "👤 User: {$user->name}\n";
                $message .= "📅 Date: {$today}\n\n";

                $message .= "📈 Total Leads: {$total}\n";
                $message .= "🔥 Hot Leads: {$hot}\n";
                $message .= "🌤 Warm Leads: {$warm}\n";
                $message .= "❄ Cold Leads: {$cold}\n\n";

                // STATUS BREAKDOWN
                $message .= "📋 Lead Status Breakdown\n";

                foreach ($statusColumns as $bucket) {

                    $bucketRows = $statusData->where('bucket', $bucket);

                    $bucketTotal = $bucketRows->sum('total');

                    if ($bucketTotal > 0) {

                        $message .= "\n➡ {$bucket}: {$bucketTotal}\n";

                        foreach ($bucketRows as $row) {

                            $message .= "• {$row->status}: {$row->total}\n";
                        }
                    }
                }

                // FOLLOWUP ACTIVITIES
                $message .= "\n📞 Follow-up Activities\n\n";

                // CALL
                $message .= "📞 Call: " . ($followups['Call'] ?? 0) . "\n";

                $callConnected = $callStats
                    ->where('followup_type', 'Call')
                    ->where('followup_status', 'Connected')
                    ->sum('total');

                $callNotConnected = $callStats
                    ->where('followup_type', 'Call')
                    ->where('followup_status', 'Not Connected')
                    ->sum('total');

                $message .= "✅ Connected: {$callConnected}\n";
                $message .= "❌ Not Connected: {$callNotConnected}\n\n";

                // WHATSAPP CALL
                $message .= "📱 WhatsApp Call: " . ($followups['WhatsApp Call'] ?? 0) . "\n";

                $wpCallConnected = $callStats
                    ->where('followup_type', 'WhatsApp Call')
                    ->where('followup_status', 'Connected')
                    ->sum('total');

                $wpCallNotConnected = $callStats
                    ->where('followup_type', 'WhatsApp Call')
                    ->where('followup_status', 'Not Connected')
                    ->sum('total');

                $message .= "✅ Connected: {$wpCallConnected}\n";
                $message .= "❌ Not Connected: {$wpCallNotConnected}\n\n";

                // WHATSAPP MESSAGE
                $message .= "💬 WhatsApp Message: " . ($followups['Whatsapp'] ?? 0) . "\n";

                $message .= "🟢 Discussion Start: " . ($whatsappStats['Discussion Start'] ?? 0) . "\n";

                $message .= "🔴 No Response: " . ($whatsappStats['No Response'] ?? 0) . "\n";

                // SEND MESSAGE
                $response = $twilio->messages->create(
                    "whatsapp:+{$fixedNumber}",
                    [
                        "from" => env('TWILIO_WHATSAPP_FROM'),
                        "body" => $message
                    ]
                );

                $this->info("Sent report for {$user->name}");

                print_r([
                    'user' => $user->name,
                    'sid' => $response->sid,
                    'status' => $response->status
                ]);
            }

            $this->info('All reports sent successfully.');

        } catch (\Exception $e) {

            print_r([
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
}
