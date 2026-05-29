<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadImportJob extends Model
{
    protected $fillable = ['file_path', 'total_rows', 'processed_rows', 'status', 'error_message'];
}
