<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use App\Mail\QuotationMail;
use App\Models\Client;
use App\Models\EmailMessage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Role;
use App\Models\Setting;
use App\Models\sms_gateway;
use App\Models\SMSMessage;
use App\Models\Unit;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\utils\Helper;
use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use GuzzleHttp\Client as Client_guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Str;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Twilio\Rest\Client as Client_Twilio;

class QuotationsController extends BaseController
{

    //---------------- GET ALL QUOTATIONS ---------------\\
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = $request->get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new Helper();
        // Filter fields With Params to retrieve
        $param = array(
            0 => 'like',
            1 => 'like',
            2 => '=',
            3 => '=',
            4 => '=',
        );
        $columns = array(
            0 => 'ref',
            1 => 'status',
            2 => 'client_id',
            3 => 'date',
            4 => 'warehouse_id',
        );
        $data = array();

        // Check If User Has Permission View  All Records
        $Quotations = Quotation::with('client', 'warehouse')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });

        //Multiple Filter
        $Filtred = $helpers->filter($Quotations, $columns, $param, $request)
            //Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('status', 'LIKE', "%{$request->search}%")
                        ->orWhere('grand_total', $request->search)
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('client', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('warehouse', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

        $totalRows = $Filtred->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $Quotations = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Quotations as $Quotation) {

            $item['id'] = $Quotation->id;
            $item['date'] = $Quotation->date;
            $item['ref'] = $Quotation->ref;
            $item['status'] = $Quotation->status;
            $item['warehouse_name'] = $Quotation['warehouse']->name;
            $item['client_name'] = $Quotation['client']->name;
            $item['client_email'] = $Quotation['client']->email;
            $item['grand_total'] = $Quotation->grand_total;

            $data[] = $item;
        }

        $customers = client::whereNull('deleted_at')->get();

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        return response()->json([
            'totalRows' => $totalRows,
            'quotations' => $data,
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    //------------ Store new Quotation -------------\\

    public function store(Request $request)
    {
        $authUser = $request->user('api');

        $this->authorizeForUser($authUser, 'create', Quotation::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        DB::transaction(function () use ($request, $authUser) {

            $order = new Quotation;

            $order->date = $request->date;
            $order->ref = $this->getNumberOrder();
            $order->status = $request->status;
            $order->client_id = $request->client_id;
            $order->grand_total = $request->grand_total;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->tax_net = $request->tax_net;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->notes = $request->notes;
            $order->user_id = $authUser->id;

            $order->save();

            $data = $request['details'];

            foreach ($data as $key => $value) {

                $orderDetails[] = [
                    'quotation_id' => $order->id,
                    'qty' => $value['qty'],
                    'sale_unit_id' => $value['sale_unit_id'] ? $value['sale_unit_id'] : NULL,
                    'price' => $value['unit_price'],
                    'tax_net' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_method'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'] ? $value['product_variant_id'] : NULL,
                    'total' => $value['subtotal'],
                    'imei_number' => $value['imei_number'],
                ];
            }
            QuotationDetail::insert($orderDetails);
        }, 10);
        return response()->json(['success' => true]);
    }

    //------------ Update Quotation -------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Quotation::class);

        request()->validate([
            'warehouse_id' => 'required',
            'client_id' => 'required',
        ]);

        DB::transaction(function () use ($request, $id) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_Quotation = Quotation::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === Quotation->id
                $this->authorizeForUser($request->user('api'), 'check_record', $current_Quotation);
            }

            $old_quotation_details = QuotationDetail::where('quotation_id', $id)->get();
            $new_quotation_details = $request['details'];
            $length = sizeof($new_quotation_details);

            // Get Ids details
            $new_details_id = [];
            foreach ($new_quotation_details as $new_detail) {
                $new_details_id[] = $new_detail['id'];
            }

            // Init quotation with old Parametre
            $old_detail_id = [];
            foreach ($old_quotation_details as $key => $value) {
                $old_detail_id[] = $value->id;

                // Delete Detail
                if (!in_array($old_detail_id[$key], $new_details_id)) {
                    $QuotationDetail = QuotationDetail::findOrFail($value->id);
                    $QuotationDetail->delete();
                }

            }

            // Update quotation with New request
            foreach ($new_quotation_details as $key => $product_detail) {

                $QuoteDetail['quotation_id'] = $id;
                $QuoteDetail['qty'] = $product_detail['qty'];
                $QuoteDetail['sale_unit_id'] = $product_detail['sale_unit_id'];
                $QuoteDetail['product_id'] = $product_detail['product_id'];
                $QuoteDetail['product_variant_id'] = $product_detail['product_variant_id'];
                $QuoteDetail['price'] = $product_detail['unit_price'];
                $QuoteDetail['tax_net'] = $product_detail['tax_percent'];
                $QuoteDetail['tax_method'] = $product_detail['tax_method'];
                $QuoteDetail['discount'] = $product_detail['discount'];
                $QuoteDetail['discount_method'] = $product_detail['discount_Method'];
                $QuoteDetail['total'] = $product_detail['subtotal'];
                $QuoteDetail['imei_number'] = $product_detail['imei_number'];

                if (!in_array($product_detail['id'], $old_detail_id)) {
                    QuotationDetail::Create($QuoteDetail);
                } else {
                    QuotationDetail::where('id', $product_detail['id'])->update($QuoteDetail);
                }
            }

            $current_Quotation->update([
                'client_id' => $request['client_id'],
                'warehouse_id' => $request['warehouse_id'],
                'status' => $request['status'],
                'notes' => $request['notes'],
                'tax_rate' => $request['tax_rate'],
                'tax_net' => $request['tax_net'],
                'date' => $request['date'],
                'discount' => $request['discount'],
                'shipping' => $request['shipping'],
                'grand_total' => $request['grand_total'],
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ Delete Quotation -------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Quotation::class);

        DB::transaction(function () use ($id, $request) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $Quotation = Quotation::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === Quotation->id
                $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
            }
            $Quotation->details()->delete();
            $Quotation->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Quotation::class);

        DB::transaction(function () use ($request) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $selectedIds = $request->selectedIds;
            foreach ($selectedIds as $Quotation_id) {
                $Quotation = Quotation::findOrFail($Quotation_id);

                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === Quotation->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
                }
                $Quotation->details()->delete();
                $Quotation->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }


    //---------------- Get Details Quotation-----------------\\

    public function show(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $quotation_data = Quotation::with('details.product.unitSale')
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Quotation->id
            $this->authorizeForUser($request->user('api'), 'check_record', $quotation_data);
        }

        $quote['ref'] = $quotation_data->ref;
        $quote['date'] = $quotation_data->date;
        $quote['note'] = $quotation_data->notes;
        $quote['status'] = $quotation_data->status;
        $quote['discount'] = $quotation_data->discount;
        $quote['shipping'] = $quotation_data->shipping;
        $quote['tax_rate'] = $quotation_data->tax_rate;
        $quote['tax_net'] = $quotation_data->tax_net;
        $quote['client_name'] = $quotation_data['client']->name;
        $quote['client_phone'] = $quotation_data['client']->phone;
        $quote['client_adr'] = $quotation_data['client']->Address;
        $quote['client_email'] = $quotation_data['client']->email;
        $quote['client_tax'] = $quotation_data['client']->tax_number;
        $quote['warehouse'] = $quotation_data['warehouse']->name;
        $quote['grand_total'] = number_format($quotation_data['grand_total'], 2, '.', '');

        foreach ($quotation_data['details'] as $detail) {

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

            $data['qty'] = $detail->qty;
            $data['total'] = $detail->total;
            $data['price'] = $detail->price;
            $data['unit_sale'] = $unit ? $unit->short_name : '';

            if ($detail->discount_method == '2') {
                $data['discount_net'] = $detail->discount;
            } else {
                $data['discount_net'] = $detail->price * $detail->discount / 100;
            }

            $tax_price = $detail->tax_net * (($detail->price - $data['discount_net']) / 100);
            $data['unit_price'] = $detail->price;
            $data['discount'] = $detail->discount;

            if ($detail->tax_method == '1') {
                $data['net_price'] = $detail->price - $data['discount_net'];
                $data['taxe'] = $tax_price;
            } else {
                $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['taxe'] = $detail->price - $data['net_price'] - $data['discount_net'];
            }

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $details[] = $data;
        }

        $company = Setting::whereNull('deleted_at')->first();

        return response()->json([
            'quote' => $quote,
            'details' => $details,
            'company' => $company,
        ]);

    }

    //---------------- reference Number Of Quotation  ---------------\\

    public function getNumberOrder()
    {
        $last = DB::table('quotations')->latest('id')->first();

        if ($last) {
            $item = $last->ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'QT_1111';
        }
        return $code;

    }

    //---------------- Quotation PDF ---------------\\

    public function Quotation_pdf(Request $request, $id)
    {

        $details = array();
        $helpers = new Helper();
        $Quotation = Quotation::with('details.product.unitSale')
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $quote['client_name'] = $Quotation['client']->name;
        $quote['client_phone'] = $Quotation['client']->phone;
        $quote['client_adr'] = $Quotation['client']->Address;
        $quote['client_email'] = $Quotation['client']->email;
        $quote['client_tax'] = $Quotation['client']->tax_number;
        $quote['tax_net'] = number_format($Quotation->tax_net, 2, '.', '');
        $quote['discount'] = number_format($Quotation->discount, 2, '.', '');
        $quote['shipping'] = number_format($Quotation->shipping, 2, '.', '');
        $quote['status'] = $Quotation->status;
        $quote['ref'] = $Quotation->ref;
        $quote['date'] = $Quotation->date;
        $quote['grand_total'] = number_format($Quotation->grand_total, 2, '.', '');

        $detail_id = 0;
        foreach ($Quotation['details'] as $detail) {

            //check if detail has sale_unit_id Or Null
            $unit = $detail->sale_unit_id ?
                Unit::where('id', $detail->sale_unit_id)->first() :
                Unit::where('id', function ($q) use ($detail) {
                    $q->select('unit_sale_id')
                        ->from('products')
                        ->where('id', $detail->product_id);
                });

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->code;
                $data['name'] = '[' . $productsVariants->name . ']' . $detail['product']['name'];

            } else {
                $data['code'] = $detail['product']['code'];
                $data['name'] = $detail['product']['name'];
            }

            $data['detail_id'] = $detail_id += 1;
            $data['qty'] = number_format($detail->qty, 2, '.', '');
            $data['total'] = number_format($detail->total, 2, '.', '');
            $data['unitSale'] = $unit ? $unit->short_name : '';
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
                $data['taxe'] = number_format($tax_price, 2, '.', '');
            } else {
                $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['taxe'] = number_format($detail->price - $data['net_price'] - $data['discount_net'], 2, '.', '');
            }

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $details[] = $data;
        }

        $settings = Setting::whereNull('deleted_at')->first();
        $symbol = $helpers->getCurrencyCode();

        $Html = view('pdf.quotation_pdf', [
            'symbol' => $symbol,
            'setting' => $settings,
            'quote' => $quote,
            'details' => $details,
        ])->render();

        $arabic = new Arabic();
        $p = $arabic->arIdentify($Html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $Html = substr_replace($Html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = PDF::loadHTML($Html);
        return $pdf->download('quotation.pdf');

    }

    //---------------- Show Form Create Quotation ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Quotation::class);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $clients = Client::whereNull('deleted_at')->get(['id', 'name']);
        $quotation_with_stock = Setting::whereNull('deleted_at')->first()->quotation_with_stock;

        return response()->json([
            'clients' => $clients,
            'warehouses' => $warehouses,
            'quotation_with_stock' => $quotation_with_stock,
        ]);
    }

    //------------- Show Form Edit Quotation -----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Quotation = Quotation::with('details.product.unitSale')
            ->whereNull('deleted_at')
            ->findOrFail($id);
        $details = array();
        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Quotation->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
        }

        if ($Quotation->client_id) {
            if (Client::where('id', $Quotation->client_id)
                ->whereNull('deleted_at')
                ->first()) {
                $quote['client_id'] = $Quotation->client_id;
            } else {
                $quote['client_id'] = '';
            }
        } else {
            $quote['client_id'] = '';
        }

        if ($Quotation->warehouse_id) {
            if (Warehouse::where('id', $Quotation->warehouse_id)
                ->whereNull('deleted_at')
                ->first()) {
                $quote['warehouse_id'] = $Quotation->warehouse_id;
            } else {
                $quote['warehouse_id'] = '';
            }
        } else {
            $quote['warehouse_id'] = '';
        }

        $quote['date'] = $Quotation->date;
        $quote['tax_rate'] = $Quotation->tax_rate;
        $quote['discount'] = $Quotation->discount;
        $quote['shipping'] = $Quotation->shipping;
        $quote['status'] = $Quotation->status;
        $quote['notes'] = $Quotation->notes;

        $detail_id = 0;
        foreach ($Quotation['details'] as $detail) {

            //check if detail has sale_unit_id Or Null
            if ($detail->sale_unit_id !== null) {
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
                $data['no_unit'] = 1;
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

                $data['no_unit'] = 0;
            }

            if ($detail->product_variant_id) {
                $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $Quotation->warehouse_id)
                    ->whereNull('deleted_at')
                    ->first();

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = $detail->product_variant_id;

                $data['code'] = $productsVariants->code;
                $data['name'] = '[' . $productsVariants->name . ']' . $detail['product']['name'];

                if ($unit && $unit->operator == '/') {
                    $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $stock = 0;
                }

            } else {
                $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                    ->whereNull('deleted_at')
                    ->where('warehouse_id', $Quotation->warehouse_id)
                    ->where('product_variant_id', '=', null)
                    ->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = null;
                $data['code'] = $detail['product']['code'];
                $data['name'] = $detail['product']['name'];

                if ($unit && $unit->operator == '/') {
                    $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $stock = 0;
                }

            }

            $data['id'] = $detail->id;
            $data['stock'] = $detail['product']['type'] != 'is_service' ? $stock : '---';
            $data['product_type'] = $detail['product']['type'];
            $data['detail_id'] = $detail_id += 1;
            $data['product_id'] = $detail->product_id;
            $data['qty'] = $detail->qty;
            $data['etat'] = 'current';
            $data['qte_copy'] = $detail->qty;
            $data['total'] = $detail->total;
            $data['unitSale'] = $unit ? $unit->short_name : '';
            $data['sale_unit_id'] = $unit ? $unit->id : '';
            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            if ($detail->discount_method == '2') {
                $data['discount_net'] = $detail->discount;
            } else {
                $data['discount_net'] = $detail->price * $detail->discount / 100;
            }

            $tax_price = $detail->tax_net * (($detail->price - $data['discount_net']) / 100);
            $data['unit_price'] = $detail->price;
            $data['tax_percent'] = $detail->tax_net;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['net_price'] = $detail->price - $data['discount_net'];
                $data['taxe'] = $tax_price;
                $data['subtotal'] = ($data['net_price'] * $data['qty']) + ($tax_price * $data['qty']);
            } else {
                $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['taxe'] = $detail->price - $data['net_price'] - $data['discount_net'];
                $data['subtotal'] = ($data['net_price'] * $data['qty']) + ($tax_price * $data['qty']);
            }

            $details[] = $data;
        }

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $clients = Client::whereNull('deleted_at')->get(['id', 'name']);

        return response()->json([
            'details' => $details,
            'quote' => $quote,
            'clients' => $clients,
            'warehouses' => $warehouses,
        ]);
    }


    //------------- Send Quotation on Email -----------\\

    public function SendEmail(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);

        //Quotation
        $quotation = Quotation::with('client')->whereNull('deleted_at')->findOrFail($request->id);

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //settings
        $settings = Setting::whereNull('deleted_at')->first();

        //the custom msg of quotation
        $emailMessage = EmailMessage::where('name', 'quotation')->first();

        if ($emailMessage) {
            $message_body = $emailMessage->body;
            $message_subject = $emailMessage->subject;
        } else {
            $message_body = '';
            $message_subject = '';
        }

        //Tags
        $random_number = Str::random(10);
        $quotation_url = url('/api/quote_pdf/' . $request->id . '?' . $random_number);
        $quotation_number = $quotation->ref;

        $total_amount = $currency . ' ' . number_format($quotation->grand_total, 2, '.', ',');

        $contact_name = $quotation['client']->name;
        $business_name = $settings->CompanyName;

        //receiver email
        $receiver_email = $quotation['client']->email;

        //replace the text with tags
        $message_body = str_replace('{contact_name}', $contact_name, $message_body);
        $message_body = str_replace('{business_name}', $business_name, $message_body);
        $message_body = str_replace('{quotation_url}', $quotation_url, $message_body);
        $message_body = str_replace('{quotation_number}', $quotation_number, $message_body);
        $message_body = str_replace('{total_amount}', $total_amount, $message_body);

        $email['subject'] = $message_subject;
        $email['body'] = $message_body;
        $email['company_name'] = $business_name;

        $this->set_config_mail();

        $mail = Mail::to($receiver_email)->send(new CustomEmail($email));

        return $mail;
    }

    //-------------------Sms Notifications -----------------\\

    public function Send_SMS(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Quotation::class);

        //Quotation
        $quotation = Quotation::with('client')->whereNull('deleted_at')->findOrFail($request->id);

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //settings
        $settings = Setting::whereNull('deleted_at')->first();

        $default_sms_gateway = sms_gateway::where('id', $settings->sms_gateway)
            ->whereNull('deleted_at')->first();

        //the custom msg of quotation
        $smsMessage = SMSMessage::where('name', 'quotation')->first();

        if ($smsMessage) {
            $message_text = $smsMessage->text;
        } else {
            $message_text = '';
        }

        //Tags
        $random_number = Str::random(10);
        $quotation_url = url('/api/quote_pdf/' . $request->id . '?' . $random_number);
        $quotation_number = $quotation->ref;

        $total_amount = $currency . ' ' . number_format($quotation->grand_total, 2, '.', ',');

        $contact_name = $quotation['client']->name;
        $business_name = $settings->CompanyName;

        //receiver phone
        $receiverNumber = $quotation['client']->phone;

        //replace the text with tags
        $message_text = str_replace('{contact_name}', $contact_name, $message_text);
        $message_text = str_replace('{business_name}', $business_name, $message_text);
        $message_text = str_replace('{quotation_url}', $quotation_url, $message_text);
        $message_text = str_replace('{quotation_number}', $quotation_number, $message_text);
        $message_text = str_replace('{total_amount}', $total_amount, $message_text);

        //twilio
        if ($default_sms_gateway->title == "twilio") {
            try {

                $account_sid = env("TWILIO_SID");
                $auth_token = env("TWILIO_TOKEN");
                $twilio_number = env("TWILIO_FROM");

                $client = new Client_Twilio($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number,
                    'body' => $message_text]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            //nexmo
        } elseif ($default_sms_gateway->title == "nexmo") {
            try {

                $basic = new \Nexmo\Client\Credentials\Basic(env("NEXMO_KEY"), env("NEXMO_SECRET"));
                $client = new \Nexmo\Client($basic);
                $nexmo_from = env("NEXMO_FROM");

                $message = $client->message()->send([
                    'to' => $receiverNumber,
                    'from' => $nexmo_from,
                    'text' => $message_text
                ]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            //---- infobip
        } elseif ($default_sms_gateway->title == "infobip") {

            $BASE_URL = env("base_url");
            $API_KEY = env("api_key");
            $SENDER = env("sender_from");

            $configuration = (new Configuration())
                ->setHost($BASE_URL)
                ->setApiKeyPrefix('Authorization', 'App')
                ->setApiKey('Authorization', $API_KEY);

            $client = new Client_guzzle();

            $sendSmsApi = new SendSMSApi($client, $configuration);
            $destination = (new SmsDestination())->setTo($receiverNumber);
            $message = (new SmsTextualMessage())
                ->setFrom($SENDER)
                ->setText($message_text)
                ->setDestinations([$destination]);

            $request = (new SmsAdvancedTextualRequest())->setMessages([$message]);

            try {
                $smsResponse = $sendSmsApi->sendSmsMessage($request);
                echo("Response body: " . $smsResponse);
            } catch (Throwable $apiException) {
                echo("HTTP Code: " . $apiException->getCode() . "\n");
            }

        }

        return response()->json(['success' => true]);
    }


}

