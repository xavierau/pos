<?php

namespace App\Http\Controllers;

use App\Models\PosSetting;
use App\Models\Sale;
use App\Models\Setting;
use App\utils\Helper;
use Illuminate\Http\Request;

class PrintPosInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $helpers = new Helper();
        $details = array();

        $sale = Sale::with('client', 'payments', 'details.unitSale', 'details.productVariant', 'details.product.unitSale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $item['id'] = $sale->id;
        $item['ref'] = $sale->ref;
        $item['date'] = $sale->date;
        $item['discount'] = $sale->discount;
        $item['shipping'] = $sale->shipping;
        $item['tax_net'] = $sale->tax_net;
        $item['tax_rate'] = $sale->tax_rate;
        $item['client_name'] = $sale->client?->name;
        $item['warehouse_name'] = $sale->warehouse->name;
        $item['grand_total'] = $sale->grand_total;
        $item['paid_amount'] = $sale->paid_amount;

        foreach ($sale->details as $detail) {

            //check if detail has sale_unit_id Or Null
            $unit = $detail->unitSale ?? $detail->product->unitSsale;

            if ($productsVariant = $detail->productVariant) {
                $data['code'] = $productsVariant->code;
                $data['name'] = '[' . $productsVariant->name . ']' . $detail->product->name;

            } else {
                $data['code'] = $detail->product->code;
                $data['name'] = $detail->product->name;
            }


            $data['quantity'] = $detail->quantity;
            $data['total'] = $detail->total;
            $data['unit_sale'] = $unit ? $unit->short_name : '';

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $details[] = $data;
        }


        $settings = Setting::first();
        $pos_settings = PosSetting::first();
        $symbol = $helpers->getCurrencyCode();

        return response()->json([
            'symbol' => $symbol,
            'payments' => $sale->payments,
            'setting' => $settings,
            'pos_settings' => $pos_settings,
            'sale' => $item,
            'details' => $details,
        ]);

    }
}
