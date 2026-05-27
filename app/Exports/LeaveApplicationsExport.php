<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveApplicationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $leaves;

    public function __construct($leaves)
    {
        $this->leaves = $leaves;
    }

    public function collection()
    {
        return $this->leaves;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Employee Email',
            'Leave Category',
            'Leave Type',
            'Start Date',
            'End Date',
            'Duration (Days)',
            'Reason',
            'Status',
            'Applied Date'
        ];
    }

    public function map($leave): array
    {
        return [
            $leave->id,
            optional($leave->employee)->name ?? 'N/A',
            optional($leave->employee)->email ?? 'N/A',
            $leave->leave_category ?? 'N/A',
            $leave->leave_type ?? 'N/A',
            $leave->start_date ? (\Carbon\Carbon::parse($leave->start_date)->format('Y-m-d')) : 'N/A',
            $leave->end_date ? (\Carbon\Carbon::parse($leave->end_date)->format('Y-m-d')) : 'N/A',
            $leave->duration ?? 'N/A',
            $leave->reason ?? 'N/A',
            ucfirst($leave->status),
            $leave->created_at ? $leave->created_at->format('Y-m-d H:i:s') : 'N/A'
        ];
    }
}
