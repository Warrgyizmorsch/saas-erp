<?php

namespace Modules\HRMS\App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveBalancesExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnWidths
{
    protected $balances;

    public function __construct($balances)
    {
        $this->balances = $balances;
    }

    public function array(): array
    {
        $rows = [];
        $counter = 0;
        foreach ($this->balances as $b) {
            $counter++;
            $rows[] = [
                $counter,
                $b->name,
                $b->total_allotted,
                $b->total_taken,
                $b->balance,
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'SR. NO',
            'EMPLOYEE NAME',
            'TOTAL ALLOTTED',
            'USED',
            'AVAILABLE',
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
            'A' => 10,
            'B' => 30,
            'C' => 18,
            'D' => 12,
            'E' => 15,
        ];
    }
}
