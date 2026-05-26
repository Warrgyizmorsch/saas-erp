<?php

namespace Modules\Inventory\App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductSampleExport implements FromArray, WithEvents
{
    public function array(): array
    {
        return [

            // Heading Row
            [
                'machine_name',
                'estimation_duration',
                'name',
                'quantity',
                'unit',
                'model',
                'height',
                'width',
                'thikness',
                'length',
                'opening_stock',
                'classification',
                'placement',
                'composition',
                'outer_diameter',
                'inner_diameter',
                'no_of_coil',
            ],

            // Dummy Row 1
            [
                'DTO 48X08',
                '30',
                'BODY BLOCK',
                '3',
                'Nos',
                'DTO 48X08',
                '1',
                '1',
                '1',
                '1',
                '0',
                'FINISH',
                'Rack A1',
                'MS CASTING',
                '0',
                '0',
                '1',
            ],  
        ];
    }
    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            $units = '"Nos,Kg,Mtr,Ltr,Feet,Set,Ton,Inch"';

            for ($i = 2; $i <= 100; $i++) {

                $validation = $event->sheet
                    ->getCell("E{$i}")
                    ->getDataValidation();

                $validation->setType(
                    \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST
                );

                $validation->setErrorStyle(
                    \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP
                );

                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);

                $validation->setFormula1($units);
            }
        },
    ];
}
}