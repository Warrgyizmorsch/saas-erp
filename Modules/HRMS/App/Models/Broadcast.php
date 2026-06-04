<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Broadcast extends Model
{
    use HasFactory;

    protected $table = 'broadcasts';

    protected $fillable = [
        'department',
        'message',
    ];

    public function readByUsers()
    {
        return $this->belongsToMany(User::class)->withPivot('read_at');
    }
}
