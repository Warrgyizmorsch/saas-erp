<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class University extends Model
{
    protected $table = 'university'; // update table name

    protected $guarded = [];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($university) {
            if (empty($university->slug)) {
                $university->slug = Str::slug($university->name);
            }
        });

        static::updating(function ($university) {
            if (empty($university->slug)) {
                $university->slug = Str::slug($university->name);
            }
        });
    }

    // Relation to courses
    public function courses()
    {
        return $this->hasMany(Course::class, 'university_id', 'id');
    }

    // Relation to university details
    public function details()
    {
        return $this->hasOne(UniversityDetail::class, 'university_id', 'id');
    }

    public function getBannerImageUrlAttribute()
    {
        // 1. From university_details (storage)
        if ($this->details && $this->details->banner_image) {
            return asset('storage/' . $this->details->banner_image);
        }

        // 2. From university table (storage)
        if ($this->banner_image) {
            return asset('storage/' . $this->banner_image);
        }

        // 3. fallback
        return asset('images/default-banner.jpg');
    }

    public function getLogoImageUrlAttribute()
    {
        // 1. From university_details (thumbnail in storage)
        if ($this->details && $this->details->thumbnail_image) {
            return asset('storage/' . $this->details->thumbnail_image);
        }

        // 2. From university table (custom folder)
        if ($this->logo_image && $this->logo_image !== '0') {
            $file = basename($this->logo_image);
            return asset('university-logos/' . $file);
        }

        // 3. fallback
        return asset('images/default-logo.png');
    }

    public function getCountrySlugAttribute()
    {
        $map = [
            'usa' => ['usa', 'us', 'united states', 'united states of america'],
            'uk' => ['uk', 'united kingdom', 'england', 'britain'],
            'australia' => ['australia', 'au'],
            'canada' => ['canada', 'ca'],
        ];

        $country = strtolower(trim($this->country));

        foreach ($map as $standard => $variants) {
            if (in_array($country, $variants)) {
                return $standard;
            }
        }

        return \Str::slug($this->country); // fallback (safe URL)
    }
}

