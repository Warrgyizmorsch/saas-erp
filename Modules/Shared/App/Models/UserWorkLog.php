<?php

namespace Modules\Shared\App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
class UserWorkLog extends Model
{
    use BelongsToTenant;

    protected $fillable = ['user_id', 'date', 'active_seconds'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
