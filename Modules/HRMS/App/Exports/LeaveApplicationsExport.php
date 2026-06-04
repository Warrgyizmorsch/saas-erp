<?php

namespace Modules\HRMS\App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class LeaveApplicationsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithColumnWidths
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
            'SR. NO',
            'EMPLOYEE NAME',
            'STATUS',
            'LEAVE TYPE',
            'CATEGORY',
            'START DATE',
            'END DATE',
            'START TIME',
            'END TIME',
            'TOTAL DAYS',
            'REASON',
            'MESSAGE'
        ];
    }

    public function map($leave): array
    {
        static $counter = 0;
        $counter++;

        $categoryRaw = strtolower($leave->leave_category);
        $isGatepass = str_contains($categoryRaw, 'gatepass');

        $category = strtoupper($leave->leave_category);
        if (str_contains($categoryRaw, 'half')) {
            // Already handled by strtoupper above, but ensuring consistency
            $category = str_replace('HALF', 'HALF DAY', strtoupper($leave->leave_category));
            // Avoid double "HALF DAY DAY"
            $category = str_replace('HALF DAY DAY', 'HALF DAY', $category);
        } elseif ($categoryRaw === 'full' || $categoryRaw === 'full day') {
            $category = 'FULL DAY';
        }

        return [
            $counter,
            $leave->employee->name ?? 'N/A',
            strtoupper($leave->status),
            ucwords($leave->leave_type),
            $category,
            $leave->start_date ? $leave->start_date->format('d-M-Y') : '-',
            $leave->end_date ? $leave->end_date->format('d-M-Y') : '-',
            ($isGatepass && $leave->start_time) ? Carbon::parse($leave->start_time)->format('h:i A') : '',
            ($isGatepass && $leave->end_time) ? Carbon::parse($leave->end_time)->format('h:i A') : '',
            $leave->total_days,
            $leave->reason,
            $leave->message
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 25,
            'F' => 15,
            'G' => 15,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 30,
            'L' => 45,
        ];
    }
}
