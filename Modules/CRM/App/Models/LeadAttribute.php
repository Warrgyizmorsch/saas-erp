<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class LeadAttribute extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = [
        'lead_id',
        'field_name',
        'field_value',
        'lead_question_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }

    public function question()
    {
        return $this->belongsTo(LeadQuestion::class, 'lead_question_id');
    }
}
