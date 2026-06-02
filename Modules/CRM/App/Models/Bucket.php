<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\App\Models\Leads;

use App\Traits\BelongsToTenant;
class Bucket extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'buckets';

    protected $fillable = [
        'name',
        'parent_id',
        'is_deleted',
        'bucket_color'
    ];

    // Override delete() to mark is_deleted = 1
    public function delete()
    {
        $this->is_deleted = 1;
        $this->save();
    }

    // Scope to always exclude soft-deleted buckets
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    /**
     * Parent bucket relationship (self-referencing).
     */
    public function parent()
    {
        return $this->belongsTo(Bucket::class, 'parent_id')->where('is_deleted', 0);
    }

    /**
     * Child buckets relationship (self-referencing).
     */
    public function children()
    {
        return $this->hasMany(Bucket::class, 'parent_id')->where('is_deleted', 0);
    }

    public function leads()
    {
        return $this->hasMany(Leads::class, 'lead_bucket_id', 'id');
    }
}
