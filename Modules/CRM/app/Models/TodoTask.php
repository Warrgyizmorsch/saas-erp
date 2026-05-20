<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoTask extends Model
{
    protected $fillable = [
        'lead_id',
        'assigned_to',
        'created_by', 
        'summary', 
        'due_date', 
        'status',
        'remark'
        ];

    // Jis user ko task assign kiya gaya hai
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
