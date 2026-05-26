<?php

namespace Modules\Inventory\App\Exports;

use Modules\Inventory\App\Models\Project;
use Modules\Inventory\App\Models\Inventory;
use Modules\Inventory\App\Models\StockTransaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RequiredVsAvailableExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {

        $projects = Project::with([
            'projectProducts.product.productItems',
            'projectItems'
        ])
            ->whereNotIn('status', ['completed', 'hold'])
            ->get();

        $runningProjectIds = $projects->pluck('id')->filter()->values();
        $runningProjectIdSet = $runningProjectIds->flip();

        $requirements = [];

        foreach ($projects as $project) {

            foreach ($project->projectProducts as $projectProduct) {

                $productQty = (int) ($projectProduct->quantity ?? 0);

                if ($productQty <= 0) continue;

                foreach (($projectProduct->product->productItems ?? []) as $item) {

                    $inventoryId = (int) $item->inventory_id;

                    if (!$inventoryId) continue;

                    $requiredQty = $productQty * (int) ($item->quantity ?? 0);

                    if (!isset($requirements[$inventoryId])) {
                        $requirements[$inventoryId] = 0;
                    }

                    $requirements[$inventoryId] += $requiredQty;
                }
            }

            foreach (($project->projectItems ?? []) as $pi) {

                $inventoryId = (int) $pi->inventory_id;

                if (!$inventoryId) continue;

                $requiredQty = (int) ($pi->quantity ?? 0);

                if (!isset($requirements[$inventoryId])) {
                    $requirements[$inventoryId] = 0;
                }

                $requirements[$inventoryId] += $requiredQty;
            }
        }

        if (empty($requirements)) {
            return collect([]);
        }

        $inventories = Inventory::whereIn('id', array_keys($requirements))->get();

        $inventoryIds = $inventories->pluck('id')->values();

        $allowedMachineIds = StockTransaction::whereIn('project_id', $runningProjectIds)
            ->whereNotNull('machine_id')
            ->pluck('machine_id')
            ->unique()
            ->values();

        $allowedMachineIdSet = $allowedMachineIds->flip();

        $transactions = StockTransaction::select(
            'inventory_id',
            'txn_type',
            'ref_type',
            'quantity',
            'project_id',
            'machine_id'
        )
            ->whereIn('inventory_id', $inventoryIds)
            ->get()
            ->groupBy('inventory_id');

        $data = [];

        foreach ($inventories as $inventory) {

            $rows = $transactions->get($inventory->id, collect());

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

            $consumption = $rows
                ->where('txn_type', 'Out')
                ->filter(function ($r) use ($runningProjectIdSet, $allowedMachineIdSet) {

                    $hasRunningProject = !empty($r->project_id) && $runningProjectIdSet->has($r->project_id);

                    $hasAllowedMachine = !empty($r->machine_id) && $allowedMachineIdSet->has($r->machine_id);

                    return $hasRunningProject || $hasAllowedMachine;
                })
                ->sum('quantity');

            $classification = $inventory->classification;

            if ($classification === 'FINISH' || $classification === "" || $classification === "null") {

                $finalFnsh = $in - $out;
                $finalMc = 0;
                $semifinish = 0;
            } else {

                $finalMc = $mc - $finish;
                $finalFnsh = $finish - $out;
                $semifinish = $in - $out - $finalMc - $finalFnsh;
            }

            $total = $in - $out;

            $required = (float) ($requirements[$inventory->id] ?? 0);

            $diff = $required - $total - $consumption;

            $status = 'OK';

            if ($diff > 0) {
                $status = 'Short: ' . number_format($diff, 2);
            } elseif ($diff < 0) {
                $status = 'Extra: ' . number_format(abs($diff), 2);
            }

            $data[] = [
                'Inventory Name' => $inventory->name,
                'Model' => $inventory->model,
                 'Required Qty' => number_format($required - $consumption, 2),
                'Machining' => number_format($finalMc, 2),
                'Finish' => number_format($finalFnsh, 2),
                'Semi Finish' => number_format($semifinish, 2),
                'Available Total' => number_format($total, 2),
                'Short / Extra' => $status,
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Inventory Name',
            'Model',
            'Required Qty',
            'Machining',
            'Finish',
            'Semi Finish',
            'Available Total',
            'Short / Extra',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [

            // Heading Row
            1 => [

                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ],
                ],

                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'rgb' => '1F4E78'
                    ],
                ],

                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }
}
