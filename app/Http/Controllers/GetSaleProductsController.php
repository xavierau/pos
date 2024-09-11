<?php

namespace App\Http\Controllers;

use App\Models\ProductWarehouse;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetSaleProductsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'create', SaleReturn::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $sale = Sale::with('details.product.unitSale', 'details.productVariant')
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === SaleReturn->id
            $this->authorizeForUser($request->user('api'), 'check_record', $sale);
        }

        $Return_detail['client_id'] = $sale->client_id;
        $Return_detail['warehouse_id'] = $sale->warehouse_id;
        $Return_detail['sale_id'] = $sale->id;
        $Return_detail['tax_rate'] = 0;
        $Return_detail['tax_net'] = 0;
        $Return_detail['discount'] = 0;
        $Return_detail['shipping'] = 0;
        $Return_detail['status'] = "received";
        $Return_detail['notes'] = "";

        $detail_id = 0;
        foreach ($sale->details as $detail) {
            //check if detail has sale_unit_id Or Null
            $data['no_unit'] = ($detail->unitSale || $detail->product->unitSale) ? 0 : 1;
            $unit = $detail->unitSale ?? $detail->product->unitSale;

            if ($detail->productVariant) {
                $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->first();

                $data['del'] = $item_product ? 0 : 1;
                $data['product_variant_id'] = $detail->product_variant_id;
                $data['code'] = $detail->productVariant->code;
                $data['name'] = '[' . $detail->productVariant->name . ']' . $detail['product']['name'];

            } else {
                $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->whereNull('product_variant_id')
                    ->first();

                $data['del'] = $item_product ? 0 : 1;
                $data['product_variant_id'] = null;
                $data['code'] = $detail->product->code;
                $data['name'] = $detail->product->name;
            }

            if ($unit && $unit->operator === '/') {
                $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
            } else if ($unit && $unit->operator === '*') {
                $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
            } else {
                $stock = 0;
            }

            $data['id'] = $detail->id;
            $data['stock'] = $detail->product->type !== 'is_service' ? $stock : '-- - ';
            $data['detail_id'] = $detail_id += 1;
            $data['quantity'] = $detail->quantity;
            $data['sale_quantity'] = $detail->quantity;
            $data['product_id'] = $detail->product_id;
            $data['unitSale'] = $unit->short_name;
            $data['sale_unit_id'] = $unit->id;
            $data['is_imei'] = $detail->product->is_imei;
            $data['imei_number'] = $detail->imei_number;
            $data['discount_net'] = $detail->discount_net;
            $data['unit_price'] = $detail->price;
            $data['tax_percent'] = $detail->tax_net;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_Method'] = $detail->discount_method;
            $data['net_price'] = $detail->net_price;
            $data['tax'] = $detail->tax_price;
            $data['subtotal'] = $detail->sub_total;


            $details[] = $data;
        }


        return response()->json([
            'details' => $details,
            'sale_return' => $Return_detail,
        ]);

    }
}
