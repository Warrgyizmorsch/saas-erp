<?php

namespace Modules\Inventory\App\Exports;

use Modules\Inventory\App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PurchaseOrderExport implements FromCollection, WithHeadings, WithEvents
{
    protected $request;

    //  store merge row ranges
    protected $mergeRows = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = PurchaseOrder::with([
            'supplier',
            'creator',
            'approver',
            'firmData',
            'items.inventory'
        ]);

        //  Filters
        if ($this->request->from_date) {
            $query->whereDate('po_date', '>=', $this->request->from_date);
        }

        if ($this->request->to_date) {
            $query->whereDate('po_date', '<=', $this->request->to_date);
        }

        if ($this->request->po_no) {
            $query->where('po_number', 'like', '%' . $this->request->po_no . '%');
        }

        if ($this->request->supplier_id) {
            $query->where('supplier_id', $this->request->supplier_id);
        }

        if ($this->request->firm_id) {
            $query->where('firm', $this->request->firm_id);
        }

        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }

        $rows = collect();
        $count = 0;

        $query->get()->each(function ($po) use (&$rows, &$count) {

            $startRow = $rows->count() + 2; // +2 because heading row

            $firstRow = true;
            $poTotal = 0;

            $itemCount = count($po->items);

            foreach ($po->items as $item) {

                if ($firstRow) {
                    $count++;
                }

                $poTotal += $item->line_total;

                $rows->push([
                    'S.No' => $firstRow ? $count : '',
                    'Date' => $firstRow ? $po->po_date : '',
                    'P.O No' => $firstRow ? $po->po_number : '',
                    'Supplier' => $firstRow ? ($po->supplier->supplier_name ?? '') : '',
                    'Firm' => $firstRow ? ($po->firmData->name ?? '') : '',

                    'Description' => $item->inventory->name ?? '',
                    'Unit' => $item->inventory->unit ?? '',
                    'HSN' => $item->hsn,
                    'Qty' => $item->ordered_qty,
                    'Rate' => $item->unit_price,
                    'Dis %' => $item->discount,
                    'Amount' => $item->discount_amount,
                    'GST %' => $item->tax_percent,
                    'GST Amt' => $item->tax_amount,
                    'Total Amount' => $item->line_total,

                    'Remark' => '',
                ]);

                $firstRow = false;
            }

            // merge rows tracking
            $endRow = $startRow + $itemCount;

            $this->mergeRows[] = [
                'start' => $startRow,
                'end'   => $endRow,
            ];

            //  SUBTOTAL ROW (YELLOW)
            $rows->push([
                'S.No' => '',
                'Date' => '',
                'P.O No' => '',
                'Supplier' => '',
                'Firm' => '',
                'Description' => '',
                'Unit' => '',
                'HSN' => '',
                'Qty' => '',
                'Rate' => '',
                'Dis %' => '',
                'Amount' => '',
                'GST %' => '',
                'GST Amt' => '',
                'Total Amount' => $poTotal,
                'Remark' => '',
            ]);

            //  GAP ROW
            $rows->push([
                'S.No' => '',
                'Date' => '',
                'P.O No' => '',
                'Supplier' => '',
                'Firm' => '',
                'Description' => '',
                'Unit' => '',
                'HSN' => '',
                'Qty' => '',
                'Rate' => '',
                'Dis %' => '',
                'Amount' => '',
                'GST %' => '',
                'GST Amt' => '',
                'Total Amount' => '',
                'Remark' => '',
            ]);
        });

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Date',
            'P.O No',
            'Supplier',
            'Firm',
            'Description',
            'Unit',
            'HSN',
            'Qty',
            'Rate',
            'Dis %',
            'Amount',
            'GST %',
            'GST Amt',
            'Total Amount',
            'Remark',
        ];
    }

    //  STYLING + MERGE
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                //  dynamic last column
                $lastCol = $sheet->getHighestColumn();

                //  Header Style
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'D9D9D9']
                    ],
                ]);

                //  Merge Rows
                foreach ($this->mergeRows as $merge) {

                    $start = $merge['start'];
                    $end   = $merge['end'];

                    // merge only if more than 1 row
                    if ($start != $end) {

                        $sheet->mergeCells("A{$start}:A{$end}");
                        $sheet->mergeCells("B{$start}:B{$end}");
                        $sheet->mergeCells("C{$start}:C{$end}");
                        $sheet->mergeCells("D{$start}:D{$end}");
                        $sheet->mergeCells("E{$start}:E{$end}");
                    }

                    // 🔽 Center Alignment
                    $sheet->getStyle("A{$start}:E{$end}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle("A{$start}:E{$end}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $highestRow = $sheet->getHighestRow();

                //  Yellow subtotal rows detect
                for ($row = 2; $row <= $highestRow; $row++) {

                    $total = $sheet->getCell('O' . $row)->getValue();
                    $desc  = $sheet->getCell('F' . $row)->getValue();

                    // subtotal row
                    if (!empty($total) && empty($desc)) {

                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => 'solid',
                                    'startColor' => ['rgb' => 'FFFF00']
                                ],
                                'font' => [
                                    'bold' => true
                                ]
                            ]);
                    }
                }

                //  Auto Width
                foreach (range('A', $lastCol) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}