<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UniversityDetail extends Model
{
    use SoftDeletes;

    protected $table = 'university_details';

    protected $fillable = [
        'university_id',
        'overview',
        'ranking_info',
        'global_ranking',
        'country_ranking',
        'batch_strength',
        'global_diversity',
        'cost_of_living',
        'tuition_fee_from',
        'currency_code',
        'admission_requirements',
        'entry_requirements_url',
        'scholarship_info',
        'scholarship_url',
        'finances_info',
        'finances_url',
        'accommodation_info',
        'accommodation_url',
        'faq_content',
        'banner_image',
        'thumbnail_image',
        'status',
    ];

    protected $casts = [
        'cost_of_living' => 'decimal:2',
        'tuition_fee_from' => 'decimal:2',
        'batch_strength' => 'integer',
    ];

    /**
     * Relationship to University
     */
    public function university()
    {
        return $this->belongsTo(University::class, 'university_id', 'id');
    }
}
