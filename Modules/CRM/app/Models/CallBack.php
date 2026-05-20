<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CallBack extends Model
{
    use HasFactory;

    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'callback_messages';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'created_by',
        'message',
        'status',
        'bucket',
        'next_followup_date',
        'is_done',
        'call_recording',
        'lead_engagement_status',
        'followup_type',
        'followup_status',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')
            ->select(['id', 'name']); // only fetch id + name
    }

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id')
            ->select(['id', 'uid', 'lead_owner', 'lead_engagement_status', 'campaign_name', 'applying_country_for_a_visa', 'what_course_are_you_planning_to_study', 'verified_lead', 'date']);
    }

    /**
     * Get status transitions for a user within a date range
     * Identifies warm->hot and hot->warm transitions efficiently
     */
    public static function getStatusTransitions($fromDate, $toDate)
    {
        $callbacks = self::whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate])
            ->whereNotNull('lead_engagement_status')
            ->orderBy('lead_id')
            ->orderBy('created_at')
            ->get([
                'lead_id',
                'lead_engagement_status',
                'created_at'
            ]);


        $warmToHot = [];
        $hotToWarm = [];

        $previousStatuses = [];

        foreach ($callbacks as $callback) {

            $leadId = $callback->lead_id;

            $currentStatus = strtolower(trim($callback->lead_engagement_status));

            // skip invalid statuses
            if (!in_array($currentStatus, ['hot', 'warm', 'cold'])) {
                continue;
            }

            // if previous status exists
            if (isset($previousStatuses[$leadId])) {

                $previousStatus = $previousStatuses[$leadId];

                // warm -> hot
                if ($previousStatus === 'warm' && $currentStatus === 'hot') {
                    $warmToHot[] = $leadId;
                }

                // hot -> warm
                if ($previousStatus === 'hot' && $currentStatus === 'warm') {
                    $hotToWarm[] = $leadId;
                }
            }

            // update latest status
            $previousStatuses[$leadId] = $currentStatus;
        }

        return [
            'warm_to_hot' => array_values(array_unique($warmToHot)),
            'hot_to_warm' => array_values(array_unique($hotToWarm)),
        ];
    }


}
