<?php

namespace Modules\HRMS\App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Payroll extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'employee_id',
        'month',
        'payable_days',
        'gross_salary',
        'basic_salary',
        'hra',
        'conveyance_allowance',
        'medical_allowance',
        'other_allowance',
        'deductions',
        'pf_deduction',
        'esi_deduction',
        'other_deduction',
        'net_salary',
        'status',
        'payment_date',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
