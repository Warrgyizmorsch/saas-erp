<?php

namespace Modules\Inventory\App\Exports;

use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\StockTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CurrentStockExport implements 
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Inventory::with('category');

        if ($this->request->filled('name')) {
            $query->where('id', $this->request->name);
        }

        if ($this->request->filled('category_id')) {
            $query->where('category_id', $this->request->category_id);
        }

        if ($this->request->filled('classification')) {
            $query->where('classification', $this->request->classification);
        }

        $sort = $this->request->get('sort', 'desc');

        $inventories = $query->get();

        $inventoryIds = $inventories->pluck('id')->values();

        $transactions = StockTransaction::select(
            'inventory_id',
            'txn_type',
            'ref_type',
            'quantity'
        )
        ->whereIn('inventory_id', $inventoryIds)
        ->get()
        ->groupBy('inventory_id');

        $stockData = [];

        foreach ($inventories as $item) {

            $rows = $transactions->get($item->id, collect());

            $in = $rows->where('txn_type', 'In')
                ->where('ref_type', '!=', 'Finish')
                ->sum('quantity');

            $out = $rows->where('txn_type', 'Out')
                ->where('ref_type', '!=', 'Machining')
                ->sum('quantity');

            $finish = $rows->where('txn_type', 'In')
                ->where('ref_type', 'Finish')
                ->sum('quantity');

            $mc = $rows->where('txn_type', 'Out')
                ->where('ref_type', 'Machining')
                ->sum('quantity');

            $cls = strtoupper(trim((string)($item->classification ?? '')));

            if ($cls === 'FINISH' || $cls === '' || $cls === 'NULL') {

                $machining = 0;
                $semiFinish = 0;
                $finishStock = $in - $out;
                $total = $in - $out;

            } elseif ($cls === 'SEMI_FINISH') {

                $machining = $mc - $finish;
                $finishStock = $finish - $out;
                $semiFinish = $in - $out - $machining - $finishStock;
                $total = $in - $out;

            } else {

                $machining = 0;
                $semiFinish = 0;
                $finishStock = $in - $finish;
                $total = $in - $out;
            }

            $stockData[] = [
                'item' => $item,
                'machining' => $machining,
                'semiFinish' => $semiFinish,
                'finishStock' => $finishStock,
                'total' => $total,
            ];
        }

        if ($sort == 'asc') {
            $stockData = collect($stockData)->sortBy('total');
        } else {
            $stockData = collect($stockData)->sortByDesc('total');
        }

        $data = [];

        foreach ($stockData as $row) {

            $item = $row['item'];

            $data[] = [
                'Inventory Name' => $item->name,
                'Model' => $item->model,
                'Placement' => $item->placement,
                'Category' => $item->category->name ?? '',
                'Classification' => $item->classification,
                'Machining Stock' => number_format($row['machining'], 2),
                'Semi Finish Stock' => number_format($row['semiFinish'], 2),
                'Finish Stock' => number_format($row['finishStock'], 2),
                'Total Stock' => number_format($row['total'], 2),
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Inventory Name',
            'Model',
            'Placement',
            'Category',
            'Classification',
            'Machining Stock',
            'Semi Finish Stock',
            'Finish Stock',
            'Total Stock',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Heading Row Style
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'],
            ],

            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '1F4E78', // Blue
                ],
            ],

            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // All Data Border
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:I{$highestRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

        return [];
    }
}