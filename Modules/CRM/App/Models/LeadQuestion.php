<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Traits\BelongsToTenant;
class LeadQuestion extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $fillable = ['field_name', 'label', 'is_active'];

    public function attributes()
    {
        return $this->hasMany(LeadAttribute::class, 'lead_question_id');
    }
}
