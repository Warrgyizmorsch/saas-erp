<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{

    protected $fillable = [
        'lead_bucket_id',
        'lead_status',
        'lead_owner',
        'date',
        'campaign_name',
        'adset_name',
        'ad_name',
        'form_name',
        'platform',
        'whats_your_preferred_intake',
        'highest_completed',
        'any_academic_gap',
        'budget',
        'applying_country_for_a_visa',
        'what_course_are_you_planning_to_study',
        'uid',
        'description',
        'visa_type',
        'city',
        'lead_engagement_status',
        'page_url',
        'imported_by',
        'followup_type',
        'english_test_status',
        'category_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid', 'id');
    }

    protected $casts = [
        'date' => 'date',
        'verified_lead' => 'boolean',
    ];

    public function bucket()
    {
        return $this->belongsTo(Bucket::class, 'lead_bucket_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'lead_owner', 'id');
    }

    public function histories()
    {
        return $this->hasMany(LeadHistory::class, 'lead_id');
    }

    public function messages()
    {
        return $this->hasMany(CallBack::class, 'lead_id');
    }

    public function attributes()
    {
        return $this->hasMany(LeadAttribute::class, 'lead_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(CallBack::class, 'lead_id')
            ->latestOfMany();
    }

    public function todoTasks()
    {
        return $this->hasMany(TodoTask::class, 'lead_id');
    }

    public function assignHistories()
    {
        return $this->hasMany(LeadAssignHistory::class, 'lead_id');
    }

    public function latestAssignHistory()
    {
        return $this->hasOne(LeadAssignHistory::class, 'lead_id')->latestOfMany();
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
