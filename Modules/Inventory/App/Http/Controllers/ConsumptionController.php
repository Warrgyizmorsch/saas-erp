<?php
// app/Http/Controllers/ConsumptionController.php

namespace Modules\Inventory\App\Http\Controllers;

use Modules\Inventory\App\Models\Consumption;
use Modules\Inventory\App\Models\RequestSlip;
use Modules\Inventory\App\Models\RequisitionSlipRow;
use Modules\Inventory\App\Models\RequisitionSlipRowPiece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConsumptionController extends Controller
{



    public function create($id)
    {
        $rs = RequestSlip::with(['issue.rows.inventory'])->findOrFail($id);

        if (!$rs->issue) {
            return back()->with('error', 'Issue entry not found for this Request Slip.');
        }

        // ✅ Consumption sums for this RS
        $consumedQtyMap = Consumption::where('request_slips_id', $rs->id)
            ->select('rs_row_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('rs_row_id')
            ->pluck('total_qty', 'rs_row_id')
            ->toArray();

        $consumedHMap = Consumption::where('request_slips_id', $rs->id)
            ->select('rs_row_id', DB::raw('SUM(height) as total_h'))
            ->groupBy('rs_row_id')
            ->pluck('total_h', 'rs_row_id')
            ->toArray();

        $consumedWMap = Consumption::where('request_slips_id', $rs->id)
            ->select('rs_row_id', DB::raw('SUM(width) as total_w'))
            ->groupBy('rs_row_id')
            ->pluck('total_w', 'rs_row_id')
            ->toArray();

        return view('inventory::consumption.create', compact('rs', 'consumedQtyMap', 'consumedHMap', 'consumedWMap'));
    }

    public function store(Request $request, $id)
    {
        $rs = RequestSlip::with(['rows'])->findOrFail($id);


        $items = $request->input('items', []);

         // 🔥 If Send To HOD clicked
    if ($request->has('send_to_hod_item')) {

        $rowId = $request->send_to_hod_item;

        if (isset($items[$rowId])) {

            // ✅ Also update piece table if needed
            RequisitionSlipRowPiece::where('id', $rowId)->update([
                'is_completed' => 1,
                'send_hod' =>6
            ]);
        }

        return back()->with('success', 'Item sent to HOD successfully.');
    }

        foreach ($items as $rsRowId => $item) {



            $consumeQty = $item['consume_qty'] ?? null;
            $invId      = $item['inv_id'] ?? null;
            $issuedHeight = $item['issued_height'] ?? null;
            $issuedWidth  = $item['issued_width'] ?? null;
            $issuedQty    = $item['issued_qty'] ?? null;
             $shape       = $item['shape'] ?? null;
            $height     = $item['height'] ?? null;
            $unit       = $item['unit'] ?? null;
            $width      = $item['width'] ?? null;
            $remark     = $item['remark'] ?? null;
            $machineId  = $item['machine_id'] ?? null;
            $projectId  = $item['project_id'] ?? null;

            $row = RequisitionSlipRowPiece::find($rsRowId);

            // ✅ Pehle remaining nikaalo (without adding current input)
            $remainingQty = $issuedQty  - ($row->consumed_qty ?? 0);
            $remainingH   = $issuedHeight - ($row->consumed_height ?? 0);
            $remainingW   = $issuedWidth - ($row->consumed_width ?? 0);

            // ================= VALIDATION =================

            if (!empty($unit) && strtolower(trim($unit)) !== 'kg' && $consumeQty > $remainingQty) {
                return back()
                    ->withErrors(["items.$rsRowId.consume_qty" => "Consume qty cannot be greater than remaining ($remainingQty)."])
                    ->withInput();
            }

            if (!empty($unit) && strtolower(trim($unit)) === 'kg') {

                if (($height && !$width) || (!$height && $width)) {
                    return back()
                        ->withErrors(["items.$rsRowId.height" => "Both Height and Width are required."])
                        ->withInput();
                }

                if ($height > $remainingH) {
                    return back()
                        ->withErrors(["items.$rsRowId.height" => "Height cannot be greater than remaining ($remainingH)."])
                        ->withInput();
                }

                if ($width > $remainingW) {
                    return back()
                        ->withErrors(["items.$rsRowId.width" => "Width cannot be greater than remaining ($remainingW)."])
                        ->withInput();
                }
            }
   

            if ($row) {
                if (!empty($unit) && strtolower(trim($unit ?? '')) === 'kg') {
                    $row->issued_qty  = 1;
                } else {
                    $row->issued_qty   = $issuedQty;
                }

                $row->issued_height =   $issuedHeight;
                $row->issued_width  =   $issuedWidth;
                 $row->shape        =   $shape;
                $row->consumed_height = ($row->consumed_height ?? 0) + $height;
                $row->consumed_width  = ($row->consumed_width ?? 0) + $width;

                $row->consumed_qty   = ($row->consumed_qty ?? 0) + $consumeQty;

                if ($row->consumed_height >= $row->issued_height && $row->consumed_width >= $row->issued_width && $row->consumed_height > 0 && $row->consumed_width > 0) {
                    $row->is_completed = 1;
                }

                if ((int)$row->issued_qty > 0 && (int)$row->consumed_qty >= (int)$row->issued_qty) {
                    $row->is_completed = 1;
                }



                $row->save();
            }
            if ($consumeQty == 0 && $height == null && $width == null) {
                continue;
            }

            Consumption::create([
                'request_slips_id' => $rs->id,
                'transaction_date' => $request->transaction_date,
                'created_by'       => Auth::id(),

                'rs_row_id'        => $rsRowId,
                'inventory_id'     => $invId,

                'machine_id'       => $machineId,
                'project_id'       => $projectId,
                'remark'           => $remark,

                'unit'             => $unit,

                'height'           => $height,
                'width'            => $width,
                'issued_qty'       => $issuedQty,
                'quantity'         => $consumeQty,

            ]);
        }
        return redirect()->back()
            ->with('success', 'Consumption saved successfully.');
    }


    // helper (aapka existing logic)
    private function nextConsumptionNo()
    {
        $last = Consumption::orderBy('id', 'desc')->first();
        $n = $last ? (int)preg_replace('/\D/', '', (string)$last->consumption_no) : 0;
        $n++;
        return 'CONS-' . str_pad($n, 5, '0', STR_PAD_LEFT);
    }

    public function list(Request $request)
    {
        
    $rs = RequestSlip::with([
        'rows.pieces' => function ($q) {
            $q->where('send_hod', 6)->with('inventory');
        },
    ])
    ->orderBy('id', 'desc')->get();
        return view('inventory::consumption.list', compact('rs'));
    }

   
}
