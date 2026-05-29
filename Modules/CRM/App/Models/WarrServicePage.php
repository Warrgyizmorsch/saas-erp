<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrServicePage extends Model
{
    protected $table = 'warr_service_pages';

    protected $fillable = [
        'service_id',
        'country_id',
        'city_id',

        'hero_title',
        'hero_description',

        'section1_title',
        'section1_description',
        'section1_points',

        'section2_title',
        'section2_description',
        'section2_points',

        'section3_title',
        'section3_description',
        'section3_points',

        'section4_title',
        'section4_description',
        'section4_points',

        'faq',
        'meta_title',
        'meta_description',
        'slug',
        'status',
    ];

    protected $casts = [
        'section1_points' => 'array',
        'section2_points' => 'array',
        'section3_points' => 'array',
        'section4_points' => 'array',
        'faq' => 'array',
    ];

    /* ================= Relations ================= */

    public function service()
    {
        return $this->belongsTo(WarrService::class, 'service_id');
    }

    public function country()
    {
        return $this->belongsTo(WarrCountry::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(WarrCity::class, 'city_id');
    }

    // "More services" selection
    public function moreServices()
    {
        return $this->belongsToMany(
            WarrService::class,
            'warr_service_page_more_services',
            'service_page_id',
            'service_id'
        );
    }
}
