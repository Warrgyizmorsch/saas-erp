<?php

namespace Modules\CRM\App\Http\Controllers;

use Modules\CRM\App\Models\CallBack;
use Modules\CRM\App\Models\Leads;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
{
    public function send($userId)
    {
        $from = request('from') ?? now()->toDateString();
        $to = request('to') ?? now()->toDateString();

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

        // USER
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return back()->with('error', 'User not found');
        }

        // TOTAL LEADS
        $total = CallBack::where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('bucket', $statusColumns)
            ->select(DB::raw("COUNT(DISTINCT CONCAT(lead_id, '-', status)) as total"))
            ->value('total') ?? 0;

        // ENGAGEMENT DATA
        $engagementData = Leads::select(
            'lead_engagement_status',
            DB::raw("COUNT(*) as total")
        )
            ->where('lead_owner', $userId)
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
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
            ->where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('bucket', $statusColumns)
            ->groupBy('bucket', 'status')
            ->get();

        // FOLLOWUPS
        $followups = CallBack::select(
            'followup_type',
            DB::raw("COUNT(*) as total")
        )
            ->where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('followup_type')
            ->groupBy('followup_type')
            ->pluck('total', 'followup_type');

        // CALL + WHATSAPP CALL STATS
        $callStats = CallBack::select(
            'followup_type',
            'followup_status',
            DB::raw("COUNT(*) as total")
        )
            ->where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('followup_type', ['Call', 'WhatsApp Call'])
            ->whereIn('followup_status', ['Connected', 'Not Connected'])
            ->groupBy('followup_type', 'followup_status')
            ->get();

        // WHATSAPP MESSAGE STATS
        $whatsappStats = CallBack::select(
            'followup_status',
            DB::raw("COUNT(*) as total")
        )
            ->where('created_by', $userId)
            ->where('followup_type', 'Whatsapp')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('followup_status', ['Discussion Start', 'No Response'])
            ->groupBy('followup_status')
            ->pluck('total', 'followup_status');

        // ================= MESSAGE =================

        $message = "📊 Daily Lead Report\n\n";

        $message .= "👤 User: {$user->name}\n\n";

        $message .= "📅 From: {$from}\n";
        $message .= "📅 To: {$to}\n\n";

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

                    $message .= "   • {$row->status}: {$row->total}\n";
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

        $message .= "   ✅ Connected: {$callConnected}\n";
        $message .= "   ❌ Not Connected: {$callNotConnected}\n\n";

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

        $message .= "   ✅ Connected: {$wpCallConnected}\n";
        $message .= "   ❌ Not Connected: {$wpCallNotConnected}\n\n";

        // WHATSAPP MESSAGE
        $message .= "💬 WhatsApp Message: " . ($followups['Whatsapp'] ?? 0) . "\n";

        $message .= "   🟢 Discussion Start: " . ($whatsappStats['Discussion Start'] ?? 0) . "\n";

        $message .= "   🔴 No Response: " . ($whatsappStats['No Response'] ?? 0) . "\n";

        // MOBILE FORMAT
        $mobile = preg_replace('/[^0-9]/', '', $user->contact_no);

        if (substr($mobile, 0, 2) != '91') {
            $mobile = '91' . $mobile;
        }

        // TWILIO CLIENT
        $twilio = new Client(
            env('TWILIO_SID'),
            env('TWILIO_AUTH_TOKEN')
        );

        // SEND MESSAGE
        $twilio->messages->create(
            "whatsapp:+{$mobile}",
            [
                "from" => env('TWILIO_WHATSAPP_FROM'),
                "body" => $message
            ]
        );

        return back()->with('success', 'WhatsApp Report Sent Successfully');
    }

    public function sendAll()
    {
        $from = request('from') ?? now()->toDateString();
        $to   = request('to') ?? now()->toDateString();

        $allUsers = Leads::whereIn('lead_owner', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('is_deleted', 0);
        })
            ->select('lead_owner')
            ->distinct()
            ->pluck('lead_owner');

        $sent = 0;


        foreach ($allUsers as $userId) {

            try {
                $this->sendUserReport($userId, $from, $to);
                $sent++;
            } catch (\Exception $e) {
                dd($e->getMessage());
                \Log::error("Lead Owner WhatsApp Error", [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return back()->with('success', "WhatsApp sent to {$sent} users successfully");
    }

    private function sendUserReport($userId, $from, $to)
    {
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

        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) return;

        // TOTAL
        $total = CallBack::where('created_by', $userId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereIn('bucket', $statusColumns)
            ->select(DB::raw("COUNT(DISTINCT CONCAT(lead_id, '-', status)) as total"))
            ->value('total') ?? 0;

        // ENGAGEMENT
        $engagementData = Leads::select(
            'lead_engagement_status',
            DB::raw("COUNT(*) as total")
        )
            ->where('lead_owner', $userId)
            ->whereBetween(DB::raw('DATE(date)'), [$from, $to])
            ->groupBy('lead_engagement_status')
            ->pluck('total', 'lead_engagement_status');

        $hot  = $engagementData['hot'] ?? 0;
        $warm = $engagementData['warm'] ?? 0;
        $cold = $engagementData['cold'] ?? 0;

        // MESSAGE
        $message = "📊 Daily Lead Report\n\n";
        $message .= "👤 User: {$user->name}\n";
        $message .= "📅 {$from} to {$to}\n\n";
        $message .= "📈 Total: {$total}\n";
        $message .= "🔥 Hot: {$hot}\n";
        $message .= "🌤 Warm: {$warm}\n";
        $message .= "❄ Cold: {$cold}\n";

        // MOBILE
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $response = $twilio->messages->create(
            "whatsapp:+916265455843",
            [
                "from" => 'whatsapp:+14155238886',
                "body" => $message
            ]
        );
        
    }
}

