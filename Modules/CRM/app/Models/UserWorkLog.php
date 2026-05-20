<?php
namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkLog extends Model
{
    protected $fillable = ['user_id', 'date', 'active_seconds'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
