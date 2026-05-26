<?php

namespace Modules\Inventory\App\Imports;

use Modules\Inventory\App\Models\Product;
use Modules\Inventory\App\Models\Inventory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;

class ProductImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {

            // 1. Machine (Product) create (last row se)
            $firstRow = $rows->first();

            $machineName = trim($firstRow['machine_name'] ?? '');

            $exists = Product::whereRaw('LOWER(name) = ?', [strtolower($machineName)])->exists();

            if ($exists) {
                throw new \Exception("Machine already exists with name: " . $machineName);
            }

            $product = Product::create([
                'name' =>  $firstRow['machine_name'],
                'estimation_duration' => $firstRow['estimation_duration'] ?? 0,
            ]);

            // 2. Loop all rows (inventory items)
            foreach ($rows as $row) {

                if (empty($row['name'])) continue;

                // Inventory find or create
                $inventory = Inventory::firstOrCreate([
                    'name'  => $row['name'],
                    'model' => $row['model'],
                ],
                [
                    'min_quantity' => $row['min_quantity'] ?? 0,
                    'unit' => $row['unit'] ?? 'Nos',
                    'category_id' => 1,
                    'classification' => $row['classification'] ?? 'FINISH',
                    'height' => $row['height'] ?? 0,
                    'width' => $row['width'] ?? 0,
                    'thikness' => $row['thikness'] ?? 0,
                    'length' => $row['length'] ?? 0,
                    'opening_stock' => $row['opening_stock'] ?? 0,
                    'placement' => $row['placement'] ?? null,
                    'composition' => $row['composition'] ?? null,
                    'outer_diameter' => $row['outer_diameter'] ?? 0,
                    'inner_diameter' => $row['inner_diameter'] ?? 0,
                    'no_of_coil'  => $row['no_of_coil'] ?? 0,
                ]);

                // product_items insert
                DB::table('product_items')->insert([
                    'product_id'   => $product->id,
                    'inventory_id' => $inventory->id,
                    'quantity'     => $row['quantity'] ?? 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
