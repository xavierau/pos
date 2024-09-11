<?php

namespace App\Http\Controllers;

use App\DTO\CreatePosSaleDTO;
use App\DTO\PosGetProductsDTO;
use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\DraftSale;
use App\Models\DraftSaleDetail;
use App\Models\PaymentSale;
use App\Models\PaymentWithCreditCard;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\Services\pos\actions\CreatePosSale;
use App\Services\pos\GetProducts as GetProductsService;
use App\utils\Helper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe;

class PosController extends BaseController
{

    //------------ Create New  POS --------------\\

    public function CreatePOS(Request $request, CreatePosSale $action)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
            'payment.amount' => 'required',
        ]);

        $saleDto = CreatePosSaleDTO::fromRequest($request);
        $item = $action->execute($saleDto, $request->user('api'));

        return response()->json(['success' => true, 'id' => $item]);

//        $payment = $request->get('payment');


//        $dto = new CreatePosSaleDTO(
//            client_id: $request->get('client_id'),
//            warehouse_id: $request->get('warehouse_id'),
//            tax_rate: $request->get('tax_rate'),
//            tax_net: $request->get('tax_net'),
//            discount: $request->get('discount'),
//            shipping: $request->get('shipping'),
//            grand_total: $request->get('grand_total'),
//            date: \Illuminate\Support\Carbon::parse($request->get('date')),
//            amount: $request->get('amount'),
//            change: $request->get('change'),
//            payment: new PaymentDTO(
//                type: PaymentType::from($payment['type']),
//                notes: $payment['notes'] ?? null,
//                account_id: $payment['account_id'] ?? null,
//            ),
//            details: collect($request->get('details', []))->map(fn($detail) => new SaleDetailDTO(
//                sale_unit_id: $detail['sale_unit_id'],
//                quantity: $detail['quantity'],
//                product_id: $detail['product_id'],
//                subtotal: $detail['subtotal'],
//                unit_price: $detail['unit_price'],
//                tax_percent: $detail['tax_percent'],
//                tax_method: $detail['tax_method'],
//                discount: $detail['discount'],
//                discount_method: $detail['discount_method'],
//                imei_number: $detail['imei_number'],
//                product_variant_id: $detail['product_variant_id']
//            ))->toArray(),
//            notes: $request->get('notes'),
//            ref: $request->get('ref'),
//        );
//
//        $item = $action->execute($dto, $request->user('api'));


    }

    //------------- get_draft_sales -----------\\

    public function get_draft_sales(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // How many items do you want to display.
        $perPage = $request->limit;

        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = 'id';
        $dir = 'DESC';
        $helpers = new Helper();

        $data = array();

        // Check If User Has Permission View  All Records
        $draft_sales = DraftSale::with('client', 'warehouse', 'user')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });


        $totalRows = $draft_sales->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }

        $drafts = $draft_sales->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($drafts as $draft) {

            $item['id'] = $draft['id'];
            $item['date'] = $draft['date'];
            $item['ref'] = $draft['ref'];
            $item['warehouse_name'] = $draft['warehouse']['name'];
            $item['client_name'] = $draft['client']['name'];
            $item['grand_total'] = number_format($draft['grand_total'], 2, '.', '');
            $item['actions'] = '';

            $data[] = $item;
        }


        return response()->json([
            'totalRows' => $totalRows,
            'draft_sales' => $data,
        ]);
    }


    //------------ Create Draft --------------\\

    public function CreateDraft(Request $request)
    {
        //   dd($request->all());
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            $helpers = new Helper();
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $order = new DraftSale;

            $order->date = Carbon::now();
            $order->ref = $this->getNumberOrderDraft();
            $order->client_id = $request->client_id;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->tax_net = $request->tax_net;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->grand_total = $request->grand_total;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];
            foreach ($data as $key => $value) {

                $unit = Unit::where('id', $value['sale_unit_id'])->first();
                $orderDetails[] = [
                    'date' => Carbon::now(),
                    'draft_sale_id' => $order->id,
                    'sale_unit_id' => $value['sale_unit_id'],
                    'quantity' => $value['quantity'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'],
                    'total' => $value['subtotal'],
                    'price' => $value['unit_price'],
                    'tax_net' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_Method'],
                    'imei_number' => $value['imei_number'],
                ];
            }

            DraftSaleDetail::insert($orderDetails);

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ remove_draft_sale -------------\\

    public function remove_draft_sale(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        \DB::transaction(function () use ($id, $request) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $draft = DraftSale::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === draft->id
                $this->authorizeForUser($request->user('api'), 'check_record', $draft);
            }
            $draft->details()->delete();
            $draft->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ submit_sale_from_draft --------------\\

    public function submit_sale_from_draft(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
            'payment.amount' => 'required',
        ]);

        $draft = DraftSale::findOrFail($request['draft_sale_id']);
        if ($draft) {

            $item = \DB::transaction(function () use ($request, $draft) {
                $helpers = new Helper();
                $role = Auth::user()->roles()->first();
                $view_records = Role::findOrFail($role->id)->inRole('record_view');
                $order = new Sale;

                $order->is_pos = 1;
                $order->date = Carbon::now();
                $order->ref = app('App\Http\Controllers\SalesController')->getNumberOrder();
                $order->client_id = $request->client_id;
                $order->warehouse_id = $request->warehouse_id;
                $order->tax_rate = $request->tax_rate;
                $order->tax_net = $request->tax_net;
                $order->discount = $request->discount;
                $order->shipping = $request->shipping;
                $order->grand_total = $request->grand_total;
                $order->notes = $request->notes;
                $order->status = 'completed';
                $order->payment_status = 'unpaid';
                $order->user_id = Auth::user()->id;

                $order->save();

                $data = $request['details'];
                foreach ($data as $key => $value) {

                    $unit = Unit::where('id', $value['sale_unit_id'])
                        ->first();
                    $orderDetails[] = [
                        'date' => Carbon::now(),
                        'sale_id' => $order->id,
                        'sale_unit_id' => $value['sale_unit_id'],
                        'quantity' => $value['quantity'],
                        'product_id' => $value['product_id'],
                        'product_variant_id' => $value['product_variant_id'],
                        'total' => $value['subtotal'],
                        'price' => $value['unit_price'],
                        'tax_net' => $value['tax_percent'],
                        'tax_method' => $value['tax_method'],
                        'discount' => $value['discount'],
                        'discount_method' => $value['discount_Method'],
                        'imei_number' => $value['imei_number'],
                    ];

                    if ($value['product_variant_id'] !== null) {
                        $product_warehouse = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])->where('product_variant_id', $value['product_variant_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }

                    } else {
                        $product_warehouse = ProductWarehouse::where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->first();
                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }
                    }
                }

                SaleDetail::insert($orderDetails);

                $sale = Sale::findOrFail($order->id);
                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === sale->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $sale);
                }

                try {

                    $total_paid = $sale->paid_amount + $request['amount'];
                    $due = $sale->grand_total - $total_paid;

                    if ($due === 0.0 || $due < 0.0) {
                        $payment_status = 'paid';
                    } else if ($due != $sale->grand_total) {
                        $payment_status = 'partial';
                    } else if ($due == $sale->grand_total) {
                        $payment_status = 'unpaid';
                    }

                    if ($request['amount'] > 0) {
                        if ($request->payment['type'] == 'credit card') {
                            $Client = Client::whereId($request->client_id)->first();
                            Stripe\Stripe::setApiKey(config('app.STRIPE_SECRET'));

                            // Check if the payment record exists
                            $PaymentWithCreditCard = PaymentWithCreditCard::where('customer_id', $request->client_id)->first();
                            if (!$PaymentWithCreditCard) {

                                // Create a new customer and charge the customer with a new credit card
                                $customer = \Stripe\Customer::create([
                                    'source' => $request->token,
                                    'email' => $Client->email,
                                    'name' => $Client->name,
                                ]);

                                // Charge the Customer instead of the card:
                                $charge = \Stripe\Charge::create([
                                    'amount' => $request['amount'] * 100,
                                    'currency' => 'usd',
                                    'customer' => $customer->id,
                                ]);
                                $PaymentCard['customer_stripe_id'] = $customer->id;

                                // Check if the payment record not exists
                            } else {

                                // Retrieve the customer ID and card ID
                                $customer_id = $PaymentWithCreditCard->customer_stripe_id;
                                $card_id = $request->card_id;

                                // Charge the customer with the new credit card or the selected card
                                if ($request->is_new_credit_card || $request->is_new_credit_card == 'true' || $request->is_new_credit_card === 1) {
                                    // Retrieve the customer
                                    $customer = \Stripe\Customer::retrieve($customer_id);

                                    // Create New Source
                                    $card = \Stripe\Customer::createSource(
                                        $customer_id,
                                        [
                                            'source' => $request->token,
                                        ]
                                    );

                                    $charge = \Stripe\Charge::create([
                                        'amount' => $request['amount'] * 100,
                                        'currency' => 'usd',
                                        'customer' => $customer_id,
                                        'source' => $card->id,
                                    ]);
                                    $PaymentCard['customer_stripe_id'] = $customer_id;

                                } else {
                                    $charge = \Stripe\Charge::create([
                                        'amount' => $request['amount'] * 100,
                                        'currency' => 'usd',
                                        'customer' => $customer_id,
                                        'source' => $card_id,
                                    ]);
                                    $PaymentCard['customer_stripe_id'] = $customer_id;
                                }
                            }

                            $PaymentSale = new PaymentSale();
                            $PaymentSale->sale_id = $order->id;
                            $PaymentSale->ref = PaymentSale::generateOrderNumber();
                            $PaymentSale->date = Carbon::now();
                            $PaymentSale->type = $request->payment['type'];
                            $PaymentSale->amount = $request['amount'];
                            $PaymentSale->change = $request['change'];
                            $PaymentSale->notes = $request->payment['notes'];
                            $PaymentSale->user_id = Auth::user()->id;
                            $PaymentSale->account_id = $request->payment['account_id'] ? $request->payment['account_id'] : NULL;

                            $PaymentSale->save();

                            $account = Account::where('id', $request->payment['account_id'])->exists();

                            if ($account) {
                                // Account exists, perform the update
                                $account = Account::find($request->payment['account_id']);
                                $account->update([
                                    'balance' => $account->balance + $request['amount'],
                                ]);
                            }

                            $sale->update([
                                'paid_amount' => $total_paid,
                                'payment_status' => $payment_status,
                            ]);

                            $PaymentCard['customer_id'] = $request->client_id;
                            $PaymentCard['payment_id'] = $PaymentSale->id;
                            $PaymentCard['charge_id'] = $charge->id;
                            PaymentWithCreditCard::create($PaymentCard);

                            // Paying Method Cash
                        } else {

                            PaymentSale::create([
                                'sale_id' => $order->id,
                                'account_id' => $request->payment['account_id'] ? $request->payment['account_id'] : NULL,
                                'ref' => PaymentSale::generateOrderNumber(),
                                'date' => Carbon::now(),
                                'type' => $request->payment['type'],
                                'amount' => $request['amount'],
                                'change' => $request['change'],
                                'notes' => $request->payment['notes'],
                                'user_id' => Auth::user()->id,
                            ]);

                            $account = Account::where('id', $request->payment['account_id'])->exists();

                            if ($account) {
                                // Account exists, perform the update
                                $account = Account::find($request->payment['account_id']);
                                $account->update([
                                    'balance' => $account->balance + $request['amount'],
                                ]);
                            }

                            $sale->update([
                                'paid_amount' => $total_paid,
                                'payment_status' => $payment_status,
                            ]);
                        }

                    }

                } catch (Exception $e) {
                    return response()->json(['message' => $e->getMessage()], 500);
                }

                $draft->details()->delete();
                $draft->update([
                    'deleted_at' => Carbon::now(),
                ]);

                return $order->id;

            }, 10);

            return response()->json(['success' => true, 'id' => $item]);
        } else {
            return response()->json(['success' => false], 404);
        }

    }

    //--------------------- data_draft_convert_sale ------------------------\\

    public function data_draft_convert_sale(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        $clients = Client::whereNull('deleted_at')->get(['id', 'name']);
        $settings = Setting::whereNull('deleted_at')->with('Client')->first();
        $accounts = Account::whereNull('deleted_at')->orderBy('id', 'desc')->get(['id', 'account_name']);

        $draft_sale_data = DraftSale::with('details.product.unitSale')->whereNull('deleted_at')->findOrFail($id);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);

        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }


        if ($draft_sale_data->client_id) {
            if (Client::where('id', $draft_sale_data->client_id)->whereNull('deleted_at')->first()) {
                $sale['client_id'] = $draft_sale_data->client_id;
                $client_name = $draft_sale_data['Client']->name;
            } else {
                $sale['client_id'] = '';
                $client_name = '';
            }
        } else {
            $sale['client_id'] = '';
            $client_name = '';
        }

        if ($draft_sale_data->warehouse_id) {
            if (Warehouse::where('id', $draft_sale_data->warehouse_id)
                ->whereNull('deleted_at')
                ->first()) {
                $sale['warehouse_id'] = $draft_sale_data->warehouse_id;
            } else {
                $sale['warehouse_id'] = '';
            }
        } else {
            $sale['warehouse_id'] = '';
        }

        $sale['tax_rate'] = $draft_sale_data->tax_rate;
        $sale['tax_net'] = $draft_sale_data->tax_net;
        $sale['discount'] = $draft_sale_data->discount;
        $sale['shipping'] = $draft_sale_data->shipping;
        $grand_total = $draft_sale_data->grand_total;

        $detail_id = 0;
        foreach ($draft_sale_data['details'] as $detail) {

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
                    ->whereNull('deleted_at')
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $draft_sale_data->warehouse_id)
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
                    ->whereNull('deleted_at')->where('warehouse_id', $draft_sale_data->warehouse_id)
                    ->where('product_variant_id', '=', null)->first();

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
            $data['fix_stock'] = $detail['product']['type'] != 'is_service' ? $stock : '---';
            $data['current'] = $detail['product']['type'] != 'is_service' ? $stock : '---';

            $data['product_type'] = $detail['product']['type'];
            $data['detail_id'] = $detail_id += 1;
            $data['product_id'] = $detail->product_id;
            $data['total'] = $detail->total;
            $data['quantity'] = $detail->quantity;
            $data['qte_copy'] = $detail->quantity;
            $data['etat'] = 'current';
            $data['unitSale'] = $unit ? $unit->short_name : '';
            $data['sale_unit_id'] = $unit ? $unit->id : '';
            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;
            $data['subtotal'] = $detail->total;

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
            $data['discount_Method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['discount_net'];
                $data['taxe'] = $tax_price;
                $data['Total_price'] = $data['Net_price'] + $data['taxe'];
            } else {
                $data['Net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['taxe'] = $detail->price - $data['Net_price'] - $data['discount_net'];
                $data['Total_price'] = $data['Net_price'] + $data['taxe'];
            }

            $details[] = $data;
        }

        $categories = Category::whereNull('deleted_at')->get(['id', 'name']);
        $brands = Brand::whereNull('deleted_at')->get();
        $stripe_key = config('app.STRIPE_KEY');

        return response()->json([
            'stripe_key' => $stripe_key,
            'brands' => $brands,
            'warehouse_id' => $sale['warehouse_id'],
            'client_id' => $sale['client_id'],
            'client_name' => $client_name,
            'clients' => $clients,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'accounts' => $accounts,
            'sale' => $sale,
            'grand_total' => $grand_total,
            'details' => $details,
        ]);
    }


    //------------ Get Products--------------\\

    public function GetProducts(Request $request, GetProductsService $service)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        if (!$request->filled('warehouse_id')) {
            return response()->json(['products' => [],
                'totalRows' => 0,]);
        }

        [$data, $totalRows] = $service->execute(new PosGetProductsDTO(
            warehouse_id: $request->get('warehouse_id'),
            stock: $request->get('stock') == "1",
            product_service: $request->get('product_service') == "1",
            category_id: $request->get('category_id'),
            brand_id: $request->get('brand_id'),
            page: $request->get('page', 1),
            perPage: $request->get('perPage', 8),
        ));

        return response()->json(['products' => $data,
            'totalRows' => $totalRows,]);
    }

//--------------------- Get Element POS ------------------------\\

    public function GetElementPos(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        $clients = Client::whereNull('deleted_at')->get(['id', 'name']);
        $settings = Setting::whereNull('deleted_at')->with('Client')->first();
        $accounts = Account::whereNull('deleted_at')->orderBy('id', 'desc')->get(['id', 'account_name']);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);

            if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->whereNull('deleted_at')->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }

        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);

            if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->whereIn('id', $warehouses_id)->whereNull('deleted_at')->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }
        }


        if ($settings->client_id) {
            if (Client::where('id', $settings->client_id)->whereNull('deleted_at')->first()) {
                $defaultClient = $settings->client_id;
                $default_client_name = $settings['Client']->name;
            } else {
                $defaultClient = '';
                $default_client_name = '';
            }
        } else {
            $defaultClient = '';
            $default_client_name = '';
        }
        $categories = Category::whereNull('deleted_at')->get(['id', 'name']);
        $brands = Brand::whereNull('deleted_at')->get();
        $stripe_key = config('app.STRIPE_KEY');

        return response()->json([
            'stripe_key' => $stripe_key,
            'brands' => $brands,
            'defaultWarehouse' => $defaultWarehouse,
            'defaultClient' => $defaultClient,
            'default_client_name' => $default_client_name,
            'clients' => $clients,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'accounts' => $accounts,
        ]);
    }


//------------- reference Number Draft -----------\\

    public function getNumberOrderDraft()
    {

        $last = DB::table('draft_sales')->latest('id')->first();

        if ($last) {
            $item = $last->ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'DR_1111';
        }
        return $code;
    }

}
