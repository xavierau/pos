<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use App\Models\Account;
use App\Models\Client;
use App\Models\EmailMessage;
use App\Models\PaymentSale;
use App\Models\PaymentWithCreditCard;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\sms_gateway;
use App\Models\SMSMessage;
use App\Models\Unit;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\utils\Helper;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SalesController extends BaseController
{

    //------------- GET ALL SALES -----------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Sale::class);
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
            3 => 'like',
            4 => '=',
            5 => '=',
            6 => 'like',
        );
        $columns = array(
            0 => 'ref',
            1 => 'status',
            2 => 'client_id',
            3 => 'payment_status',
            4 => 'warehouse_id',
            5 => 'date',
            6 => 'shipping_status',
        );
        $data = array();

        // Check If User Has Permission View  All Records
        $query = Sale::with('facture', 'client', 'warehouse', 'user', 'returns')
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->latest();
        //Multiple Filter
        $Filtred = $helpers->filter($query, $columns, $param, $request)
            // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('status', 'LIKE', "%{$request->search}%")
                        ->orWhere('grand_total', $request->search)
                        ->orWhere('payment_status', 'like', "%{$request->search}%")
                        ->orWhere('shipping_status', 'like', "%{$request->search}%")
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
        if ($perPage === "-1") {
            $perPage = $totalRows;
        }

        $sales = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($sales as $sale) {

            $item['id'] = $sale->id;
            $item['date'] = $sale->date;
            $item['ref'] = $sale->ref;
            $item['created_by'] = $sale->user->username;
            $item['status'] = $sale->status;
            $item['shipping_status'] = $sale->shipping_status;
            $item['discount'] = $sale->discount;
            $item['shipping'] = $sale->shipping;
            $item['warehouse_name'] = $sale->warehouse->name;
            $item['client_id'] = $sale->client?->id;
            $item['client_name'] = $sale->client?->name;
            $item['client_email'] = $sale->client?->email;
            $item['client_tele'] = $sale->client?->phone;
            $item['client_code'] = $sale->client?->code;
            $item['client_adr'] = $sale->client?->address;
            $item['grand_total'] = number_format($sale->grand_total, 2, '.', '');
            $item['paid_amount'] = number_format($sale->paid_amount, 2, '.', '');
            $item['due'] = number_format($sale->due, 2, '.', '');
            $item['payment_status'] = $sale->payment_status;

            $item['sale_return_id'] = $sale->returns->first()?->id;
            $item['sale_has_return'] = $sale->hasReturn;

            $data[] = $item;
        }

        $stripe_key = config('app . STRIPE_KEY');
        $customers = Client::get(['id', 'name']);
        $accounts = Account::latest()->get(['id', 'account_name']);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        $query = Warehouse::select(['id', 'name']);
        $warehouses = ($user_auth->is_all_warehouses) ?
            $query->get() :
            $query->whereIn('id', function ($q) {
                $q->select('warehouse_id')
                    ->from('user_warehouse')
                    ->where('user_id', auth()->user()->id);
            })->get();

        return response()->json([
            'stripe_key' => $stripe_key,
            'totalRows' => $totalRows,
            'sales' => $data,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'accounts' => $accounts,
        ]);
    }

    //------------- STORE NEW SALE-----------\\

    public function store(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        DB::transaction(function () use ($request) {
            $helpers = new Helper();
            $order = new Sale;

            $order->is_pos = 0;
            $order->date = $request->date;
            $order->ref = $this->getNumberOrder();
            $order->client_id = $request->client_id;
            $order->grand_total = $request->grand_total;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->tax_net = $request->tax_net;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->status = $request->status;
            $order->payment_status = 'unpaid';
            $order->notes = $request->notes;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];
            foreach ($data as $key => $value) {
                $unit = Unit::where('id', $value['sale_unit_id'])
                    ->first();
                $orderDetails[] = [
                    'date' => $request->date,
                    'sale_id' => $order->id,
                    'sale_unit_id' => $value['sale_unit_id'] ? $value['sale_unit_id'] : NULL,
                    'quantity' => $value['quantity'],
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


                if ($order->status == "completed") {
                    if ($value['product_variant_id'] !== null) {
                        $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->where('product_variant_id', $value['product_variant_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == ' / ') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }

                    } else {
                        $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->first();

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == ' / ') {
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }
                    }
                }
            }
            SaleDetail::insert($orderDetails);

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');

            if ($request->payment['status'] != 'pending') {
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

                    if ($request['amount'] > 0 && $request->payment['status'] != 'pending') {
                        if ($request->payment['type'] == 'credit card') {
                            $Client = Client::whereId($request->client_id)->first();
                            Stripe\Stripe::setApiKey(config('app . STRIPE_SECRET'));

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
                            $PaymentSale->notes = NULL;
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
                                'ref' => PaymentSale::generateOrderNumber(),
                                'date' => Carbon::now(),
                                'account_id' => $request->payment['account_id'] ? $request->payment['account_id'] : NULL,
                                'type' => $request->payment['type'],
                                'amount' => $request['amount'],
                                'change' => $request['change'],
                                'notes' => NULL,
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

            }

        }, 10);

        return response()->json(['success' => true]);
    }


    //------------- UPDATE SALE -----------

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Sale::class);

        request()->validate([
            'warehouse_id' => 'required',
            'client_id' => 'required',
        ]);

        DB::transaction(function () use ($request, $id) {

            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_Sale = Sale::findOrFail($id);

            if (SaleReturn::where('sale_id', $id)->where('deleted_at', '=', null)->exists()) {
                return response()->json(['success' => false, 'return exist for the Transaction' => false], 403);
            } else {
                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === Sale->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $current_Sale);
                }
                $old_sale_details = SaleDetail::where('sale_id', $id)->get();
                $new_sale_details = $request['details'];
                $length = sizeof($new_sale_details);

                // Get Ids for new Details
                $new_products_id = [];
                foreach ($new_sale_details as $new_detail) {
                    $new_products_id[] = $new_detail['id'];
                }

                // Init Data with old Parametre
                $old_products_id = [];
                foreach ($old_sale_details as $key => $value) {
                    $old_products_id[] = $value->id;

                    //check if detail has sale_unit_id Or Null
                    if ($value['sale_unit_id'] !== null) {
                        $old_unit = Unit::where('id', $value['sale_unit_id'])->first();
                    } else {
                        $product_unit_sale_id = Product::with('unitSale')
                            ->where('id', $value['product_id'])
                            ->first();

                        if ($product_unit_sale_id['unit_sale']) {
                            $old_unit = Unit::where('id', $product_unit_sale_id['unit_sale']->id)->first();
                        }
                        {
                            $old_unit = NULL;
                        }
                    }

                    if ($current_Sale->status == "completed") {

                        if ($value['product_variant_id'] !== null) {
                            $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_Sale->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->where('product_variant_id', $value['product_variant_id'])
                                ->first();

                            if ($product_warehouse && $old_unit) {
                                if ($old_unit->operator == ' / ') {
                                    $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                }
                                $product_warehouse->save();
                            }

                        } else {
                            $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_Sale->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->first();
                            if ($product_warehouse && $old_unit) {
                                if ($old_unit->operator == ' / ') {
                                    $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                }
                                $product_warehouse->save();
                            }
                        }
                    }
                    // Delete Detail
                    if (!in_array($old_products_id[$key], $new_products_id)) {
                        $SaleDetail = SaleDetail::findOrFail($value->id);
                        $SaleDetail->delete();
                    }
                }


                // Update Data with New request
                foreach ($new_sale_details as $prd => $prod_detail) {

                    $get_type_product = Product::where('id', $prod_detail['product_id'])->first()->type;


                    if ($prod_detail['sale_unit_id'] !== null || $get_type_product == 'is_service') {
                        $unit_prod = Unit::where('id', $prod_detail['sale_unit_id'])->first();

                        if ($request['status'] == "completed") {

                            if ($prod_detail['product_variant_id'] !== null) {
                                $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                    ->where('warehouse_id', $request->warehouse_id)
                                    ->where('product_id', $prod_detail['product_id'])
                                    ->where('product_variant_id', $prod_detail['product_variant_id'])
                                    ->first();

                                if ($product_warehouse && $unit_prod) {
                                    if ($unit_prod->operator == ' / ') {
                                        $product_warehouse->qte -= $prod_detail['quantity'] / $unit_prod->operator_value;
                                    } else {
                                        $product_warehouse->qte -= $prod_detail['quantity'] * $unit_prod->operator_value;
                                    }
                                    $product_warehouse->save();
                                }

                            } else {
                                $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                    ->where('warehouse_id', $request->warehouse_id)
                                    ->where('product_id', $prod_detail['product_id'])
                                    ->first();

                                if ($product_warehouse && $unit_prod) {
                                    if ($unit_prod->operator == ' / ') {
                                        $product_warehouse->qte -= $prod_detail['quantity'] / $unit_prod->operator_value;
                                    } else {
                                        $product_warehouse->qte -= $prod_detail['quantity'] * $unit_prod->operator_value;
                                    }
                                    $product_warehouse->save();
                                }
                            }

                        }

                        $orderDetails['sale_id'] = $id;
                        $orderDetails['date'] = $request['date'];
                        $orderDetails['price'] = $prod_detail['unit_price'];
                        $orderDetails['sale_unit_id'] = $prod_detail['sale_unit_id'];
                        $orderDetails['tax_net'] = $prod_detail['tax_percent'];
                        $orderDetails['tax_method'] = $prod_detail['tax_method'];
                        $orderDetails['discount'] = $prod_detail['discount'];
                        $orderDetails['discount_method'] = $prod_detail['discount_method'];
                        $orderDetails['quantity'] = $prod_detail['quantity'];
                        $orderDetails['product_id'] = $prod_detail['product_id'];
                        $orderDetails['product_variant_id'] = $prod_detail['product_variant_id'];
                        $orderDetails['total'] = $prod_detail['subtotal'];
                        $orderDetails['imei_number'] = $prod_detail['imei_number'];

                        if (!in_array($prod_detail['id'], $old_products_id)) {
                            $orderDetails['date'] = Carbon::now();
                            $orderDetails['sale_unit_id'] = $unit_prod ? $unit_prod->id : Null;
                            SaleDetail::Create($orderDetails);
                        } else {
                            SaleDetail::where('id', $prod_detail['id'])->update($orderDetails);
                        }
                    }
                }

                $due = $request['grand_total'] - $current_Sale->paid_amount;
                if ($due === 0.0 || $due < 0.0) {
                    $payment_status = 'paid';
                } else if ($due != $request['grand_total']) {
                    $payment_status = 'partial';
                } else if ($due == $request['grand_total']) {
                    $payment_status = 'unpaid';
                }

                $current_Sale->update([
                    'date' => $request['date'],
                    'client_id' => $request['client_id'],
                    'warehouse_id' => $request['warehouse_id'],
                    'notes' => $request['notes'],
                    'status' => $request['status'],
                    'tax_rate' => $request['tax_rate'],
                    'tax_net' => $request['tax_net'],
                    'discount' => $request['discount'],
                    'shipping' => $request['shipping'],
                    'grand_total' => $request['grand_total'],
                    'payment_status' => $payment_status,
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------- Remove SALE BY ID -----------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Sale::class);

        DB::transaction(function () use ($id, $request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $current_Sale = Sale::findOrFail($id);
            $old_sale_details = SaleDetail::where('sale_id', $id)->get();
            $shipment_data = Shipment::where('sale_id', $id)->first();

            if (SaleReturn::where('sale_id', $id)->where('deleted_at', '=', null)->exists()) {
                return response()->json(['success' => false, 'return exist for the Transaction' => false], 403);
            } else {

                // Check If User Has Permission view All Records
                if (!$view_records) {
                    // Check If User->id === Sale->id
                    $this->authorizeForUser($request->user('api'), 'check_record', $current_Sale);
                }
                foreach ($old_sale_details as $key => $value) {

                    //check if detail has sale_unit_id Or Null
                    if ($value['sale_unit_id'] !== null) {
                        $old_unit = Unit::where('id', $value['sale_unit_id'])->first();
                    } else {
                        $product_unit_sale_id = Product::with('unitSale')
                            ->where('id', $value['product_id'])
                            ->first();
                        if ($product_unit_sale_id['unit_sale']) {
                            $old_unit = Unit::where('id', $product_unit_sale_id['unit_sale']->id)->first();
                        }
                        {
                            $old_unit = NULL;
                        }
                    }

                    if ($current_Sale->status == "completed") {

                        if ($value['product_variant_id'] !== null) {
                            $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_Sale->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->where('product_variant_id', $value['product_variant_id'])
                                ->first();

                            if ($product_warehouse && $old_unit) {
                                if ($old_unit->operator == ' / ') {
                                    $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                }
                                $product_warehouse->save();
                            }

                        } else {
                            $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $current_Sale->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->first();
                            if ($product_warehouse && $old_unit) {
                                if ($old_unit->operator == ' / ') {
                                    $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                } else {
                                    $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                }
                                $product_warehouse->save();
                            }
                        }
                    }

                }

                if ($shipment_data) {
                    $shipment_data->delete();
                }
                $current_Sale->details()->delete();
                $current_Sale->update([
                    'deleted_at' => Carbon::now(),
                    'shipping_status' => NULL,
                ]);


                $Payment_Sale_data = PaymentSale::where('sale_id', $id)->get();
                foreach ($Payment_Sale_data as $Payment_Sale) {
                    if ($Payment_Sale->type == 'credit card') {
                        $PaymentWithCreditCard = PaymentWithCreditCard::where('payment_id', $Payment_Sale->id)->first();
                        if ($PaymentWithCreditCard) {
                            $PaymentWithCreditCard->delete();
                        }
                    }

                    $account = Account::find($Payment_Sale->account_id);

                    if ($account) {
                        $account->update([
                            'balance' => $account->balance - $Payment_Sale->amount,
                        ]);
                    }

                    $Payment_Sale->delete();
                }
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'delete', Sale::class);

        DB::transaction(function () use ($request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $selectedIds = $request->selectedIds;
            foreach ($selectedIds as $sale_id) {

                if (SaleReturn::where('sale_id', $sale_id)->where('deleted_at', '=', null)->exists()) {
                    return response()->json(['success' => false, 'return exist for the Transaction' => false], 403);
                } else {
                    $current_Sale = Sale::findOrFail($sale_id);
                    $old_sale_details = SaleDetail::where('sale_id', $sale_id)->get();
                    $shipment_data = Shipment::where('sale_id', $sale_id)->first();

                    // Check If User Has Permission view All Records
                    if (!$view_records) {
                        // Check If User->id === current_Sale->id
                        $this->authorizeForUser($request->user('api'), 'check_record', $current_Sale);
                    }
                    foreach ($old_sale_details as $key => $value) {

                        //check if detail has sale_unit_id Or Null
                        if ($value['sale_unit_id'] !== null) {
                            $old_unit = Unit::where('id', $value['sale_unit_id'])->first();
                        } else {
                            $product_unit_sale_id = Product::with('unitSale')
                                ->where('id', $value['product_id'])
                                ->first();
                            if ($product_unit_sale_id['unit_sale']) {
                                $old_unit = Unit::where('id', $product_unit_sale_id['unit_sale']->id)->first();
                            }
                            {
                                $old_unit = NULL;
                            }
                        }

                        if ($current_Sale->status == "completed") {

                            if ($value['product_variant_id'] !== null) {
                                $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                    ->where('warehouse_id', $current_Sale->warehouse_id)
                                    ->where('product_id', $value['product_id'])
                                    ->where('product_variant_id', $value['product_variant_id'])
                                    ->first();

                                if ($product_warehouse && $old_unit) {
                                    if ($old_unit->operator == ' / ') {
                                        $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                    } else {
                                        $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                    }
                                    $product_warehouse->save();
                                }

                            } else {
                                $product_warehouse = ProductWarehouse::where('deleted_at', '=', null)
                                    ->where('warehouse_id', $current_Sale->warehouse_id)
                                    ->where('product_id', $value['product_id'])
                                    ->first();
                                if ($product_warehouse && $old_unit) {
                                    if ($old_unit->operator == ' / ') {
                                        $product_warehouse->qte += $value['quantity'] / $old_unit->operator_value;
                                    } else {
                                        $product_warehouse->qte += $value['quantity'] * $old_unit->operator_value;
                                    }
                                    $product_warehouse->save();
                                }
                            }
                        }

                    }

                    if ($shipment_data) {
                        $shipment_data->delete();
                    }

                    $current_Sale->details()->delete();
                    $current_Sale->update([
                        'deleted_at' => Carbon::now(),
                        'shipping_status' => NULL,
                    ]);


                    $Payment_Sale_data = PaymentSale::where('sale_id', $sale_id)->get();
                    foreach ($Payment_Sale_data as $Payment_Sale) {
                        if ($Payment_Sale->type == 'credit card') {
                            $PaymentWithCreditCard = PaymentWithCreditCard::where('payment_id', $Payment_Sale->id)->first();
                            if ($PaymentWithCreditCard) {
                                $PaymentWithCreditCard->delete();
                            }
                        }

                        $account = Account::find($Payment_Sale->account_id);

                        if ($account) {
                            $account->update([
                                'balance' => $account->balance - $Payment_Sale->amount,
                            ]);
                        }

                        $Payment_Sale->delete();
                    }
                }
            }

        }, 10);

        return response()->json(['success' => true]);
    }


    //---------------- Get Details Sale-----------------\\

    public function show(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', Sale::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $sale = Sale::with('details.product.unitSale', 'details.unitSale', 'details.productVariant', 'warehouse', 'client', 'returns')
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === sale->id
            $this->authorizeForUser($request->user('api'), 'check_record', $sale);
        }

        $sale_details['ref'] = $sale->ref;
        $sale_details['date'] = $sale->date;
        $sale_details['note'] = $sale->notes;
        $sale_details['status'] = $sale->status;
        $sale_details['warehouse'] = $sale->warehouse->name;
        $sale_details['discount'] = $sale->discount;
        $sale_details['shipping'] = $sale->shipping;
        $sale_details['tax_rate'] = $sale->tax_rate;
        $sale_details['tax_net'] = $sale->tax_net;
        $sale_details['client_name'] = $sale->client->name;
        $sale_details['client_phone'] = $sale->client->phone;
        $sale_details['client_adr'] = $sale->client->address;
        $sale_details['client_email'] = $sale->client->email;
        $sale_details['client_tax'] = $sale->client->tax_number;
        $sale_details['grand_total'] = number_format($sale->grand_total, 2, '.', '');
        $sale_details['paid_amount'] = number_format($sale->paid_amount, 2, '.', '');
        $sale_details['due'] = number_format($sale->due, 2, '.', '');
        $sale_details['payment_status'] = $sale->payment_status;

        if ($return = $sale->returns->first()) {
            $sale_details['sale_return_id'] = $return->id;
            $sale_details['sale_has_return'] = true;
        } else {
            $sale_details['sale_has_return'] = false;
        }

        foreach ($sale->details as $detail) {

            $data['code'] = $detail['product']['code'];
            $data['name'] = $detail['product']['name'];
            $data['unit_price'] = $detail->price;
            $data['discount'] = $detail->discount;
            $data['tax_net'] = $detail->tax_net;

            if ($variant = $detail->productVariant) {
                $data['code'] = $variant->code;
                $data['name'] = '[' . $variant->name . ']' . $detail['product']['name'];
            }

            $unit = $detail->unitSale ?? $detail->product->unitSale;

            $data['quantity'] = $detail->quantity;

            $data['price'] = $detail->price;
            $data['unit_sale'] = $unit ? $unit->short_name : '';

            $data['discount_net'] = ($detail->discount_method == '2') ?
                $detail->discount :
                $detail->price * $detail->discount / 100;

            if ($detail->tax_method == '1') {
                $data['net_price'] = $detail->price - $data['discount_net'];
                $data['tax'] = $detail->tax_net * (($detail->price - $data['discount_net']) / 100);;
            } else {
                $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                $data['tax'] = $detail->price - $data['net_price'] - $data['discount_net'];
            }

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $data['total'] = $detail->quantity * $data['net_price'] ?? 0;

            $details[] = $data;
        }

        return response()->json([
            'details' => $details,
            'sale' => $sale_details,
            'company' => Setting::first()
        ]);

    }


//----------------Show Form Create Sale ---------------\\

    public
    function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Sale::class);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $accounts = Account::where('deleted_at', '=', null)->get(['id', 'account_name']);

        $stripe_key = config('app . STRIPE_KEY');

        return response()->json([
            'stripe_key' => $stripe_key,
            'clients' => $clients,
            'warehouses' => $warehouses,
            'accounts' => $accounts,
        ]);

    }

//------------- Show Form Edit Sale -----------\\

    public
    function edit(Request $request, $id)
    {
        if (SaleReturn::where('sale_id', $id)->where('deleted_at', '=', null)->exists()) {
            return response()->json(['success' => false, 'return exist for the Transaction' => false], 403);
        } else {
            $this->authorizeForUser($request->user('api'), 'update', Sale::class);
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $Sale_data = Sale::with('details.product.unitSale')
                ->where('deleted_at', '=', null)
                ->findOrFail($id);
            $details = array();
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === sale->id
                $this->authorizeForUser($request->user('api'), 'check_record', $Sale_data);
            }

            if ($Sale_data->client_id) {
                if (Client::where('id', $Sale_data->client_id)
                    ->where('deleted_at', '=', null)
                    ->first()) {
                    $sale['client_id'] = $Sale_data->client_id;
                } else {
                    $sale['client_id'] = '';
                }
            } else {
                $sale['client_id'] = '';
            }

            if ($Sale_data->warehouse_id) {
                if (Warehouse::where('id', $Sale_data->warehouse_id)
                    ->where('deleted_at', '=', null)
                    ->first()) {
                    $sale['warehouse_id'] = $Sale_data->warehouse_id;
                } else {
                    $sale['warehouse_id'] = '';
                }
            } else {
                $sale['warehouse_id'] = '';
            }

            $sale['date'] = $Sale_data->date;
            $sale['tax_rate'] = $Sale_data->tax_rate;
            $sale['tax_net'] = $Sale_data->tax_net;
            $sale['discount'] = $Sale_data->discount;
            $sale['shipping'] = $Sale_data->shipping;
            $sale['status'] = $Sale_data->status;
            $sale['notes'] = $Sale_data->notes;

            $detail_id = 0;
            foreach ($Sale_data['details'] as $detail) {

                //check if detail has sale_unit_id Or Null
                if ($detail->sale_unit_id !== null) {
                    $unit = Unit::where('id', $detail->sale_unit_id)->first();
                    $data['no_unit'] = 1;
                } else {
                    $product_unit_sale_id = Product::with('unitSale')
                        ->where('id', $detail->product_id)
                        ->first();

                    if ($product_unit_sale_id['unit_sale']) {
                        $unit = Unit::where('id', $product_unit_sale_id['unit_sale']->id)->first();
                    }
                    {
                        $unit = NULL;
                    }

                    $data['no_unit'] = 0;
                }


                if ($detail->product_variant_id) {
                    $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                        ->where('deleted_at', '=', null)
                        ->where('product_variant_id', $detail->product_variant_id)
                        ->where('warehouse_id', $Sale_data->warehouse_id)
                        ->first();

                    $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                        ->where('id', $detail->product_variant_id)->first();

                    $item_product ? $data['del'] = 0 : $data['del'] = 1;
                    $data['product_variant_id'] = $detail->product_variant_id;
                    $data['code'] = $productsVariants->code;
                    $data['name'] = '[' . $productsVariants->name . ']' . $detail['product']['name'];

                    if ($unit && $unit->operator == ' / ') {
                        $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                    } else if ($unit && $unit->operator == ' * ') {
                        $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                    } else {
                        $stock = 0;
                    }

                } else {
                    $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                        ->where('deleted_at', '=', null)->where('warehouse_id', $Sale_data->warehouse_id)
                        ->where('product_variant_id', '=', null)->first();

                    $item_product ? $data['del'] = 0 : $data['del'] = 1;
                    $data['product_variant_id'] = null;
                    $data['code'] = $detail['product']['code'];
                    $data['name'] = $detail['product']['name'];

                    if ($unit && $unit->operator == ' / ') {
                        $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                    } else if ($unit && $unit->operator == ' * ') {
                        $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                    } else {
                        $stock = 0;
                    }

                }

                $data['id'] = $detail->id;
                $data['stock'] = $detail['product']['type'] != 'is_service' ? $stock : '-- - ';
                $data['product_type'] = $detail['product']['type'];
                $data['detail_id'] = $detail_id += 1;
                $data['product_id'] = $detail->product_id;
                $data['total'] = $detail->total;
                $data['quantity'] = $detail->quantity;
                $data['qte_copy'] = $detail->quantity;
                $data['etat'] = 'current';
                $data['unit_sale'] = $unit ? $unit->short_name : '';
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
                    $data['tax'] = $tax_price;
                    $data['subtotal'] = ($data['net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
                } else {
                    $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                    $data['tax'] = $detail->price - $data['net_price'] - $data['discount_net'];
                    $data['subtotal'] = ($data['net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
                }

                $details[] = $data;
            }

            //get warehouses assigned to user
            $user_auth = auth()->user();
            if ($user_auth->is_all_warehouses) {
                $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
            } else {
                $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
                $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
            }

            $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);

            return response()->json([
                'details' => $details,
                'sale' => $sale,
                'clients' => $clients,
                'warehouses' => $warehouses,
            ]);
        }

    }


//------------- Show Form Convert To Sale -----------\\

    public
    function Elemens_Change_To_Sale(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Quotation::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Quotation = Quotation::with('details.product.unitSale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);
        $details = array();
        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Quotation->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Quotation);
        }

        if ($Quotation->client_id) {
            if (Client::where('id', $Quotation->client_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $sale['client_id'] = $Quotation->client_id;
            } else {
                $sale['client_id'] = '';
            }
        } else {
            $sale['client_id'] = '';
        }

        if ($Quotation->warehouse_id) {
            if (Warehouse::where('id', $Quotation->warehouse_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $sale['warehouse_id'] = $Quotation->warehouse_id;
            } else {
                $sale['warehouse_id'] = '';
            }
        } else {
            $sale['warehouse_id'] = '';
        }

        $sale['date'] = $Quotation->date;
        $sale['tax_net'] = $Quotation->tax_net;
        $sale['tax_rate'] = $Quotation->tax_rate;
        $sale['discount'] = $Quotation->discount;
        $sale['shipping'] = $Quotation->shipping;
        $sale['status'] = 'completed';
        $sale['notes'] = $Quotation->notes;

        $detail_id = 0;
        foreach ($Quotation['details'] as $detail) {

            //check if detail has sale_unit_id Or Null
            if ($detail->sale_unit_id !== null || $detail['product']['type'] == 'is_service') {
                $unit = Unit::where('id', $detail->sale_unit_id)->first();

                if ($detail->product_variant_id) {
                    $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                        ->where('product_variant_id', $detail->product_variant_id)
                        ->where('warehouse_id', $Quotation->warehouse_id)
                        ->where('deleted_at', '=', null)
                        ->first();
                    $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                        ->where('id', $detail->product_variant_id)->where('deleted_at', null)->first();

                    $item_product ? $data['del'] = 0 : $data['del'] = 1;
                    $data['product_variant_id'] = $detail->product_variant_id;
                    $data['code'] = $productsVariants->code;
                    $data['name'] = '[' . $productsVariants->name . ']' . $detail['product']['name'];

                    if ($unit && $unit->operator == ' / ') {
                        $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                    } else if ($unit && $unit->operator == ' * ') {
                        $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                    } else {
                        $stock = 0;
                    }

                } else {
                    $item_product = ProductWarehouse::where('product_id', $detail->product_id)
                        ->where('warehouse_id', $Quotation->warehouse_id)
                        ->where('product_variant_id', '=', null)
                        ->where('deleted_at', '=', null)
                        ->first();

                    $item_product ? $data['del'] = 0 : $data['del'] = 1;
                    $data['product_variant_id'] = null;
                    $data['code'] = $detail['product']['code'];
                    $data['name'] = $detail['product']['name'];

                    if ($unit && $unit->operator == ' / ') {
                        $stock = $item_product ? $item_product->qte * $unit->operator_value : 0;
                    } else if ($unit && $unit->operator == ' * ') {
                        $stock = $item_product ? $item_product->qte / $unit->operator_value : 0;
                    } else {
                        $stock = 0;
                    }
                }

                $data['id'] = $id;
                $data['stock'] = $detail['product']['type'] != 'is_service' ? $stock : '-- - ';
                $data['product_type'] = $detail['product']['type'];
                $data['detail_id'] = $detail_id += 1;
                $data['quantity'] = $detail->quantity;
                $data['product_id'] = $detail->product_id;
                $data['total'] = $detail->total;
                $data['etat'] = 'current';
                $data['qte_copy'] = $detail->quantity;
                $data['unit_sale'] = $unit ? $unit->short_name : '';
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
                    $data['tax'] = $tax_price;
                    $data['subtotal'] = ($data['net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
                } else {
                    $data['net_price'] = ($detail->price - $data['discount_net']) / (($detail->tax_net / 100) + 1);
                    $data['tax'] = $detail->price - $data['net_price'] - $data['discount_net'];
                    $data['subtotal'] = ($data['net_price'] * $data['quantity']) + ($tax_price * $data['quantity']);
                }

                $details[] = $data;
            }
        }

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'details' => $details,
            'sale' => $sale,
            'clients' => $clients,
            'warehouses' => $warehouses,
        ]);

    }



//------------- Send sale on Email -----------\\

    public
    function Send_Email(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Sale::class);

        //sale
        $sale = Sale::with('client')->findOrFail($request->id);

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //settings
        $settings = Setting::first();

        //the custom msg of sale
        $emailMessage = EmailMessage::where('name', 'sale')->first();

        if ($emailMessage) {
            $message_body = $emailMessage->body;
            $message_subject = $emailMessage->subject;
        } else {
            $message_body = '';
            $message_subject = '';
        }

        //Tags
        $random_number = Str::random(10);
        $invoice_url = url('/api / sale_pdf / ' . $request->id . ' ? ' . $random_number);
        $invoice_number = $sale->ref;

        $total_amount = $currency . ' ' . number_format($sale->grand_total, 2, '.', ',');
        $paid_amount = $currency . ' ' . number_format($sale->paid_amount, 2, '.', ',');
        $due_amount = $currency . ' ' . number_format($sale->grand_total - $sale->paid_amount, 2, '.', ',');

        $contact_name = $sale['client']->name;
        $business_name = $settings->CompanyName;

        //receiver email
        $receiver_email = $sale['client']->email;

        //replace the text with tags
        $message_body = str_replace('{
                contact_name}', $contact_name, $message_body);
        $message_body = str_replace('{
                business_name}', $business_name, $message_body);
        $message_body = str_replace('{
                invoice_url}', $invoice_url, $message_body);
        $message_body = str_replace('{
                invoice_number}', $invoice_number, $message_body);

        $message_body = str_replace('{
                total_amount}', $total_amount, $message_body);
        $message_body = str_replace('{
                paid_amount}', $paid_amount, $message_body);
        $message_body = str_replace('{
                due_amount}', $due_amount, $message_body);

        $email['subject'] = $message_subject;
        $email['body'] = $message_body;
        $email['company_name'] = $business_name;

        $this->set_config_mail();

        Mail::to($receiver_email)->send(new CustomEmail($email));

    }


//-------------------Sms Notifications -----------------\\

    public
    function Send_SMS(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Sale::class);

        //sale
        $sale = Sale::with('client')->where('deleted_at', '=', null)->findOrFail($request->id);

        $helpers = new Helper();
        $currency = $helpers->Get_Currency();

        //settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        $default_sms_gateway = sms_gateway::where('id', $settings->sms_gateway)
            ->where('deleted_at', '=', null)->first();

        //the custom msg of sale
        $smsMessage = SMSMessage::where('name', 'sale')->first();

        if ($smsMessage) {
            $message_text = $smsMessage->text;
        } else {
            $message_text = '';
        }

        //Tags
        $random_number = Str::random(10);
        $invoice_url = url('/api/sale_pdf/' . $request->id . '?' . $random_number);
        $invoice_number = $sale->ref;

        $total_amount = $currency . ' ' . number_format($sale->grand_total, 2, '.', ',');
        $paid_amount = $currency . ' ' . number_format($sale->paid_amount, 2, '.', ',');
        $due_amount = $currency . ' ' . number_format($sale->grand_total - $sale->paid_amount, 2, '.', ',');

        $contact_name = $sale['client']->name;
        $business_name = $settings->CompanyName;

        //receiver Number
        $receiverNumber = $sale['client']->phone;

        //replace the text with tags
        $message_text = str_replace('{
                contact_name}', $contact_name, $message_text);
        $message_text = str_replace('{
                business_name}', $business_name, $message_text);
        $message_text = str_replace('{
                invoice_url}', $invoice_url, $message_text);
        $message_text = str_replace('{
                invoice_number}', $invoice_number, $message_text);

        $message_text = str_replace('{
                total_amount}', $total_amount, $message_text);
        $message_text = str_replace('{
                paid_amount}', $paid_amount, $message_text);
        $message_text = str_replace('{
                due_amount}', $due_amount, $message_text);

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
