<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Unit;
use App\utils\Helper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SalePdfGenerationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $details = array();
        $sale_data = Sale::with('details.product.unitSale')->findOrFail($id);
        $helper = new Helper();


        $sale['client_name'] = $sale_data['client']->name;
        $sale['client_phone'] = $sale_data['client']->phone;
        $sale['client_adr'] = $sale_data['client']->Address;
        $sale['client_email'] = $sale_data['client']->email;
        $sale['client_tax'] = $sale_data['client']->tax_number;
        $sale['tax_net'] = number_format($sale_data->tax_net, 2, '.', '');
        $sale['discount'] = number_format($sale_data->discount, 2, '.', '');
        $sale['shipping'] = number_format($sale_data->shipping, 2, '.', '');
        $sale['status'] = $sale_data->status;
        $sale['ref'] = $sale_data->ref;
        $sale['date'] = $sale_data->date;
        $sale['grand_total'] = number_format($sale_data->grand_total, 2, '.', '');
        $sale['paid_amount'] = number_format($sale_data->paid_amount, 2, '.', '');
        $sale['due'] = number_format($sale_data->due, 2, '.', '');
        $sale['payment_status'] = $sale_data->payment_status;


        foreach ($sale_data['details'] as $index => $detail) {

            //check if detail has sale_unit_id Or Null
            if ($detail->sale_unit_id !== null) {
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            } else {
                $product_unit_sale_id = Product::with('unitSale')
                    ->where('id', $detail->product_id)
                    ->first();

                if ($product_unit_sale_id['unitSale']) {
                    $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
                }
                {
                    $unit = NULL;
                }

            }

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->code;
                $data['name'] = '[' . $productsVariants->name . ']' . $detail['product']['name'];
            } else {
                $data['code'] = $detail['product']['code'];
                $data['name'] = $detail['product']['name'];
            }

            $data['detail_id'] = $index += 1;
            $data['quantity'] = number_format($detail->quantity, 2, '.', '');
//            $data['total'] = number_format($detail->total, 2, '.', '');
            $data['total'] = 0;
            $data['unit_sale'] = $unit ? $unit->short_name : '';
            $data['price'] = number_format($detail->price, 2, '.', '');

            if ($detail->discount_method == '2') {
                $data['discount_net'] = number_format($detail->discount, 2, '.', '');
            } else {
                $data['discount_net'] = number_format($detail->price * $detail->discount / 100, 2, '.', '');
            }
            $tax_price = $detail->tax_net * (($detail->price - $data['discount_net']) / 100);
            $data['unit_price'] = number_format($detail->price, 2, '.', '');
            $data['discount'] = number_format($detail->discount, 2, '.', '');

            if ($detail->tax_method == '1') {
                $data['net_price'] = $detail->price - $data['discount_net'];
                $data['tax'] = number_format($tax_price, 2, '.', '');
            } else {
                $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['tax'] = number_format($detail->price - $data['net_price'] - $data['discount_net'], 2, '.', '');
            }

            $data['is_imei'] = $detail->product->is_imei;
            $data['imei_number'] = $detail->product->is_imei ? $detail->imei_number : null;

            $details[] = $data;
        }

        $settings = Setting::first();

        $pdf = Pdf::loadView('pdf.sale_pdf', [
            'symbol' => $helper->getCurrencyCode(),
            'setting' => $settings,
            'sale' => $sale,
            'details' => $details,
        ]);

        return $pdf->download('invoice.pdf');
    }
}
