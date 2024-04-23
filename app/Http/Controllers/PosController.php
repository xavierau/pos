<?php

namespace App\Http\Controllers;
use App\Models\Account;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\PaymentSale;
use App\Models\Product;
use App\Models\Setting;
use App\Models\PosSetting;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\PaymentWithCreditCard;
use App\Models\Role;
use App\Models\Unit;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\DraftSale;
use App\Models\DraftSaleDetail;
use App\Models\Warehouse;
use App\utils\helpers;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe;

class PosController extends BaseController
{

    //------------ Create New  POS --------------\\

    public function CreatePOS(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
            'payment.amount' => 'required',
        ]);

        $item = \DB::transaction(function () use ($request) {
            $helpers = new helpers();
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $order = new Sale;

            $order->is_pos = 1;
            $order->date = Carbon::now();
            $order->Ref = app('App\Http\Controllers\SalesController')->getNumberOrder();
            $order->client_id = $request->client_id;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->TaxNet = $request->TaxNet;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->GrandTotal = $request->GrandTotal;
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
                    'sale_unit_id' =>  $value['sale_unit_id'],
                    'quantity' => $value['quantity'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'],
                    'total' => $value['subtotal'],
                    'price' => $value['Unit_price'],
                    'TaxNet' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_Method'],
                    'imei_number' => $value['imei_number'],
                ];

                if ($value['product_variant_id'] !== null) {
                    $product_warehouse = product_warehouse::where('warehouse_id', $order->warehouse_id)
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
                    $product_warehouse = product_warehouse::where('warehouse_id', $order->warehouse_id)
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
                $due = $sale->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_status = 'paid';
                } else if ($due != $sale->GrandTotal) {
                    $payment_status = 'partial';
                } else if ($due == $sale->GrandTotal) {
                    $payment_status = 'unpaid';
                }

                if($request['amount'] > 0){
                    if ($request->payment['Reglement'] == 'credit card') {
                        $Client = Client::whereId($request->client_id)->first();
                        Stripe\Stripe::setApiKey(config('app.STRIPE_SECRET'));

                        // Check if the payment record exists
                        $PaymentWithCreditCard = PaymentWithCreditCard::where('customer_id', $request->client_id)->first();
                        if (!$PaymentWithCreditCard) {

                            // Create a new customer and charge the customer with a new credit card
                            $customer = \Stripe\Customer::create([
                                'source' => $request->token,
                                'email'  => $Client->email,
                                'name'   => $Client->name,
                            ]);

                            // Charge the Customer instead of the card:
                            $charge = \Stripe\Charge::create([
                                'amount'   => $request['amount'] * 100,
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
                                    'amount'   => $request['amount'] * 100,
                                    'currency' => 'usd',
                                    'customer' => $customer_id,
                                    'source'   => $card->id,
                                ]);
                                $PaymentCard['customer_stripe_id'] = $customer_id;

                            } else {
                                $charge = \Stripe\Charge::create([
                                    'amount'   => $request['amount'] * 100,
                                    'currency' => 'usd',
                                    'customer' => $customer_id,
                                    'source'   => $card_id,
                                ]);
                                $PaymentCard['customer_stripe_id'] = $customer_id;
                            }
                        }

                        $PaymentSale            = new PaymentSale();
                        $PaymentSale->sale_id   = $order->id;
                        $PaymentSale->Ref       = app('App\Http\Controllers\PaymentSalesController')->getNumberOrder();
                        $PaymentSale->date      = Carbon::now();
                        $PaymentSale->Reglement = $request->payment['Reglement'];
                        $PaymentSale->montant   = $request['amount'];
                        $PaymentSale->change    = $request['change'];
                        $PaymentSale->notes     = $request->payment['notes'];
                        $PaymentSale->user_id   = Auth::user()->id;
                        $PaymentSale->account_id   = $request->payment['account_id']?$request->payment['account_id']:NULL;

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
                            'paid_amount'    => $total_paid,
                            'payment_status' => $payment_status,
                        ]);

                        $PaymentCard['customer_id'] = $request->client_id;
                        $PaymentCard['payment_id']  = $PaymentSale->id;
                        $PaymentCard['charge_id']   = $charge->id;
                        PaymentWithCreditCard::create($PaymentCard);

                        // Paying Method Cash
                    } else {

                        PaymentSale::create([
                            'sale_id' => $order->id,
                            'account_id' => $request->payment['account_id']?$request->payment['account_id']:NULL,
                            'Ref' => app('App\Http\Controllers\PaymentSalesController')->getNumberOrder(),
                            'date' => Carbon::now(),
                            'Reglement' => $request->payment['Reglement'],
                            'montant' => $request['amount'],
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

            return $order->id;

        }, 10);

        return response()->json(['success' => true, 'id' => $item]);

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
        $helpers = new helpers();

        $data = array();

        // Check If User Has Permission View  All Records
        $draft_sales = DraftSale::with('client', 'warehouse','user')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });


        $totalRows = $draft_sales->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }

        $drafts = $draft_sales->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($drafts as $draft) {

            $item['id'] = $draft['id'];
            $item['date'] = $draft['date'];
            $item['Ref'] = $draft['Ref'];
            $item['warehouse_name'] = $draft['warehouse']['name'];
            $item['client_name'] = $draft['client']['name'];
            $item['GrandTotal'] = number_format($draft['GrandTotal'], 2, '.', '');
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
            $helpers = new helpers();
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $order = new DraftSale;

            $order->date = Carbon::now();
            $order->Ref = $this->getNumberOrderDraft();
            $order->client_id = $request->client_id;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->TaxNet = $request->TaxNet;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->GrandTotal = $request->GrandTotal;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];
            foreach ($data as $key => $value) {

                $unit = Unit::where('id', $value['sale_unit_id'])->first();
                $orderDetails[] = [
                'date' => Carbon::now(),
                'draft_sale_id' => $order->id,
                'sale_unit_id' =>  $value['sale_unit_id'],
                'quantity' => $value['quantity'],
                'product_id' => $value['product_id'],
                'product_variant_id' => $value['product_variant_id'],
                'total' => $value['subtotal'],
                'price' => $value['Unit_price'],
                'TaxNet' => $value['tax_percent'],
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
        if($draft){

            $item = \DB::transaction(function () use ($request, $draft) {
                $helpers = new helpers();
                $role = Auth::user()->roles()->first();
                $view_records = Role::findOrFail($role->id)->inRole('record_view');
                $order = new Sale;

                $order->is_pos = 1;
                $order->date = Carbon::now();
                $order->Ref = app('App\Http\Controllers\SalesController')->getNumberOrder();
                $order->client_id = $request->client_id;
                $order->warehouse_id = $request->warehouse_id;
                $order->tax_rate = $request->tax_rate;
                $order->TaxNet = $request->TaxNet;
                $order->discount = $request->discount;
                $order->shipping = $request->shipping;
                $order->GrandTotal = $request->GrandTotal;
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
                        'sale_unit_id' =>  $value['sale_unit_id'],
                        'quantity' => $value['quantity'],
                        'product_id' => $value['product_id'],
                        'product_variant_id' => $value['product_variant_id'],
                        'total' => $value['subtotal'],
                        'price' => $value['Unit_price'],
                        'TaxNet' => $value['tax_percent'],
                        'tax_method' => $value['tax_method'],
                        'discount' => $value['discount'],
                        'discount_method' => $value['discount_Method'],
                        'imei_number' => $value['imei_number'],
                    ];

                    if ($value['product_variant_id'] !== null) {
                        $product_warehouse = product_warehouse::where('warehouse_id', $order->warehouse_id)
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
                        $product_warehouse = product_warehouse::where('warehouse_id', $order->warehouse_id)
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
                    $due = $sale->GrandTotal - $total_paid;

                    if ($due === 0.0 || $due < 0.0) {
                        $payment_status = 'paid';
                    } else if ($due != $sale->GrandTotal) {
                        $payment_status = 'partial';
                    } else if ($due == $sale->GrandTotal) {
                        $payment_status = 'unpaid';
                    }

                    if($request['amount'] > 0){
                        if ($request->payment['Reglement'] == 'credit card') {
                            $Client = Client::whereId($request->client_id)->first();
                            Stripe\Stripe::setApiKey(config('app.STRIPE_SECRET'));

                            // Check if the payment record exists
                            $PaymentWithCreditCard = PaymentWithCreditCard::where('customer_id', $request->client_id)->first();
                            if (!$PaymentWithCreditCard) {

                                // Create a new customer and charge the customer with a new credit card
                                $customer = \Stripe\Customer::create([
                                    'source' => $request->token,
                                    'email'  => $Client->email,
                                    'name'   => $Client->name,
                                ]);

                                // Charge the Customer instead of the card:
                                $charge = \Stripe\Charge::create([
                                    'amount'   => $request['amount'] * 100,
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
                                        'amount'   => $request['amount'] * 100,
                                        'currency' => 'usd',
                                        'customer' => $customer_id,
                                        'source'   => $card->id,
                                    ]);
                                    $PaymentCard['customer_stripe_id'] = $customer_id;

                                } else {
                                    $charge = \Stripe\Charge::create([
                                        'amount'   => $request['amount'] * 100,
                                        'currency' => 'usd',
                                        'customer' => $customer_id,
                                        'source'   => $card_id,
                                    ]);
                                    $PaymentCard['customer_stripe_id'] = $customer_id;
                                }
                            }

                            $PaymentSale            = new PaymentSale();
                            $PaymentSale->sale_id   = $order->id;
                            $PaymentSale->Ref       = app('App\Http\Controllers\PaymentSalesController')->getNumberOrder();
                            $PaymentSale->date      = Carbon::now();
                            $PaymentSale->Reglement = $request->payment['Reglement'];
                            $PaymentSale->montant   = $request['amount'];
                            $PaymentSale->change    = $request['change'];
                            $PaymentSale->notes     = $request->payment['notes'];
                            $PaymentSale->user_id   = Auth::user()->id;
                            $PaymentSale->account_id   = $request->payment['account_id']?$request->payment['account_id']:NULL;

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
                                'paid_amount'    => $total_paid,
                                'payment_status' => $payment_status,
                            ]);

                            $PaymentCard['customer_id'] = $request->client_id;
                            $PaymentCard['payment_id']  = $PaymentSale->id;
                            $PaymentCard['charge_id']   = $charge->id;
                            PaymentWithCreditCard::create($PaymentCard);

                            // Paying Method Cash
                        } else {

                            PaymentSale::create([
                                'sale_id' => $order->id,
                                'account_id' => $request->payment['account_id']?$request->payment['account_id']:NULL,
                                'Ref' => app('App\Http\Controllers\PaymentSalesController')->getNumberOrder(),
                                'date' => Carbon::now(),
                                'Reglement' => $request->payment['Reglement'],
                                'montant' => $request['amount'],
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
        }else{
            return response()->json(['success' => false], 404);
        }

    }

    //--------------------- data_draft_convert_sale ------------------------\\

    public function data_draft_convert_sale(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $settings = Setting::where('deleted_at', '=', null)->with('Client')->first();
        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);

        $draft_sale_data = DraftSale::with('details.product.unitSale')->where('deleted_at', '=', null)->findOrFail($id);

          //get warehouses assigned to user
          $user_auth = auth()->user();
          if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);

          }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
          }


        if ($draft_sale_data->client_id) {
            if (Client::where('id', $draft_sale_data->client_id)->where('deleted_at', '=', null)->first()) {
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
            if (Warehouse::where('id', $draft_sale_data->warehouse_id)->where('deleted_at', '=', null)->first()) {
                $sale['warehouse_id'] = $draft_sale_data->warehouse_id;
            } else {
                $sale['warehouse_id'] = '';
            }
        } else {
            $sale['warehouse_id'] = '';
        }

        $sale['tax_rate'] = $draft_sale_data->tax_rate;
        $sale['TaxNet']   = $draft_sale_data->TaxNet;
        $sale['discount'] = $draft_sale_data->discount;
        $sale['shipping'] = $draft_sale_data->shipping;
        $GrandTotal = $draft_sale_data->GrandTotal;

        $detail_id = 0;
        foreach ($draft_sale_data['details'] as $detail) {

            //check if detail has sale_unit_id Or Null
            if($detail->sale_unit_id !== null){
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
                $data['no_unit'] = 1;
            }else{
                $product_unit_sale_id = Product::with('unitSale')
                ->where('id', $detail->product_id)
                ->first();

                if($product_unit_sale_id['unitSale']){
                    $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
                }{
                    $unit = NULL;
                }

                $data['no_unit'] = 0;
            }


            if ($detail->product_variant_id) {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $draft_sale_data->warehouse_id)
                    ->first();

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = $detail->product_variant_id;
                $data['code'] = $productsVariants->code;
                $data['name'] = '['.$productsVariants->name . ']' . $detail['product']['name'];

                if ($unit && $unit->operator == '/') {
                $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                $stock = 0;
                }

            } else {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)->where('warehouse_id', $draft_sale_data->warehouse_id)
                    ->where('product_variant_id', '=', null)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = null;
                $data['code'] = $detail['product']['code'];
                $data['name'] = $detail['product']['name'];

                if ($unit && $unit->operator == '/') {
                    $stock= $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                $stock = 0;
                }

            }

            $data['id'] = $detail->id;
            $data['fix_stock'] = $detail['product']['type'] !='is_service'?$stock:'---';
            $data['current'] = $detail['product']['type'] !='is_service'?$stock:'---';

            $data['product_type'] = $detail['product']['type'];
            $data['detail_id'] = $detail_id += 1;
            $data['product_id'] = $detail->product_id;
            $data['total'] = $detail->total;
            $data['quantity'] = $detail->quantity;
            $data['qte_copy'] = $detail->quantity;
            $data['etat'] = 'current';
            $data['unitSale'] = $unit?$unit->ShortName:'';
            $data['sale_unit_id'] = $unit?$unit->id:'';
            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;
            $data['subtotal'] = $detail->total;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->price * $detail->discount / 100;
            }

            $tax_price = $detail->TaxNet * (($detail->price - $data['DiscountNet']) / 100);
            $data['Unit_price'] = $detail->price;

            $data['tax_percent'] = $detail->TaxNet;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_Method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['Net_price'] = $detail->price - $data['DiscountNet'];
                $data['taxe'] = $tax_price;
                $data['Total_price'] = $data['Net_price'] +  $data['taxe'];
            } else {
                $data['Net_price'] = ($detail->price - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->price - $data['Net_price'] - $data['DiscountNet'];
                $data['Total_price'] = $data['Net_price'] +  $data['taxe'];
            }

            $details[] = $data;
        }

        $categories = Category::where('deleted_at', '=', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', '=', null)->get();
        $stripe_key = config('app.STRIPE_KEY');

        return response()->json([
            'stripe_key'     => $stripe_key,
            'brands'         => $brands,
            'warehouse_id'   => $sale['warehouse_id'],
            'client_id'      => $sale['client_id'],
            'client_name'    => $client_name,
            'clients'        => $clients,
            'warehouses'     => $warehouses,
            'categories'     => $categories,
            'accounts'       => $accounts,
            'sale'           => $sale,
            'GrandTotal'           => $GrandTotal,
            'details'        => $details,
        ]);
    }


    //------------ Get Products--------------\\

    public function GetProductsByParametre(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        // How many items do you want to display.
        $perPage = 8;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $data = array();

        $product_warehouse_data = product_warehouse::where('warehouse_id', $request->warehouse_id)
            ->with('product', 'product.unitSale')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                return $query->whereHas('product', function ($q) use ($request) {
                    $q->where('not_selling', '=', 0);
                })
                ->where(function ($query) use ($request) {
                    if ($request->stock == '1' && $request->product_service == '1') {
                        return $query->where('qte', '>', 0)->orWhere('manage_stock', false);

                    }elseif($request->stock == '1' && $request->product_service == '0') {
                        return $query->where('qte', '>', 0)->orWhere('manage_stock', true);

                    }else{
                        return $query->where('manage_stock', true);
                    }
                });
            })

        // Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('category_id'), function ($query) use ($request) {
                    return $query->whereHas('product', function ($q) use ($request) {
                        $q->where('category_id', '=', $request->category_id);
                    });
                });
            })
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('brand_id'), function ($query) use ($request) {
                    return $query->whereHas('product', function ($q) use ($request) {
                        $q->where('brand_id', '=', $request->brand_id);
                    });
                });
            });

        $totalRows = $product_warehouse_data->count();

        $product_warehouse_data = $product_warehouse_data
            ->offset($offSet)
            ->limit(8)
            ->get();

        foreach ($product_warehouse_data as $product_warehouse) {
            if ($product_warehouse->product_variant_id) {
                $productsVariants = ProductVariant::where('product_id', $product_warehouse->product_id)
                    ->where('id', $product_warehouse->product_variant_id)
                    ->where('deleted_at', null)
                    ->first();

                $item['product_variant_id'] = $product_warehouse->product_variant_id;
                $item['Variant'] = '['.$productsVariants->name . ']' . $product_warehouse['product']->name;
                $item['name'] = '['.$productsVariants->name . ']' . $product_warehouse['product']->name;

                $item['code'] = $productsVariants->code;
                $item['barcode'] = $productsVariants->code;

                $product_price = $product_warehouse['productVariant']->price;

            } else {
                $item['product_variant_id'] = null;
                $item['Variant'] = null;
                $item['code'] = $product_warehouse['product']->code;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;

                $product_price =  $product_warehouse['product']->price;

            }
            $item['id'] = $product_warehouse->product_id;
            $firstimage = explode(',', $product_warehouse['product']->image);
            $item['image'] = $firstimage[0];

            if($product_warehouse['product']['unitSale']){

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_price / $product_warehouse['product']['unitSale']->operator_value;

                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_price * $product_warehouse['product']['unitSale']->operator_value;

                }

            }else{
                $item['qte_sale'] = $product_warehouse['product']->type!='is_service'?$product_warehouse->qte:'---';
                $price = $product_price;
            }

            $item['unitSale'] = $product_warehouse['product']['unitSale']?$product_warehouse['product']['unitSale']->ShortName:'';
            $item['qte'] = $product_warehouse['product']->type!='is_service'?$product_warehouse->qte:'---';
            $item['product_type'] = $product_warehouse['product']->type;

            if ($product_warehouse['product']->TaxNet !== 0.0) {

                //Exclusive
                if ($product_warehouse['product']->tax_method == '1') {
                    $tax_price = $price * $product_warehouse['product']->TaxNet / 100;

                    $item['Net_price'] = $price + $tax_price;

                    // Inxclusive
                } else {
                    $item['Net_price'] = $price;
                }
            } else {
                $item['Net_price'] = $price;
            }

            $data[] = $item;
        }

        return response()->json([
            'products' => $data,
            'totalRows' => $totalRows,
        ]);
    }

    //--------------------- Get Element POS ------------------------\\

    public function GetELementPos(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $settings = Setting::where('deleted_at', '=', null)->with('Client')->first();
        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);

          //get warehouses assigned to user
          $user_auth = auth()->user();
          if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);

             if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->where('deleted_at', '=', null)->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }

          }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);

             if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->whereIn('id', $warehouses_id)->where('deleted_at', '=', null)->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }
          }





        if ($settings->client_id) {
            if (Client::where('id', $settings->client_id)->where('deleted_at', '=', null)->first()) {
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
        $categories = Category::where('deleted_at', '=', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', '=', null)->get();
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
            'accounts'           => $accounts,
        ]);
    }


      //------------- Reference Number Draft -----------\\

      public function getNumberOrderDraft()
      {

          $last = DB::table('draft_sales')->latest('id')->first();

          if ($last) {
              $item = $last->Ref;
              $nwMsg = explode("_", $item);
              $inMsg = $nwMsg[1] + 1;
              $code = $nwMsg[0] . '_' . $inMsg;
          } else {
              $code = 'DR_1111';
          }
          return $code;
      }

}
