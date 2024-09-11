<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PaymentPurchase;
use App\Models\PaymentPurchaseReturns;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Setting;
use App\utils\Helper;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProvidersController extends BaseController
{

    //----------- Get ALL Suppliers-------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Provider::class);

        $perPage = $request->limit;
        $pageStart = $request->get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new Helper();
        // Filter fields With Params to retrieve
        $columns = array(0 => 'name', 1 => 'code', 2 => 'phone_1', 3 => 'phone_2', 4 => 'email');
        $param = array(0 => 'like', 1 => 'like', 2 => 'like', 3 => 'like', 4 => 'like');
        $data = array();
        $providers = Provider::where('deleted_at', '=', null)
            ->filter(request()->query());


        //Multiple Filter
        $Filtred = $helpers->filter($providers, $columns, $param, $request)
            // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%")
                        ->orWhere('phone_1', 'LIKE', "%{$request->search}%")
                        ->orWhere('phone_2', 'LIKE', "%{$request->search}%")
                        ->orWhere('email', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $Filtred->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $providers = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($providers as $provider) {

            $item['total_amount'] = DB::table('purchases')
                ->where('deleted_at', '=', null)
                ->where('status', 'received')
                ->where('provider_id', $provider->id)
                ->sum('grand_total');

            $item['total_paid'] = DB::table('purchases')
                ->where('deleted_at', '=', null)
                ->where('status', 'received')
                ->where('provider_id', $provider->id)
                ->sum('paid_amount');

            $item['due'] = $item['total_amount'] - $item['total_paid'];

            $item['total_amount_return'] = DB::table('purchase_returns')
                ->where('deleted_at', '=', null)
                ->where('provider_id', $provider->id)
                ->sum('grand_total');

            $item['total_paid_return'] = DB::table('purchase_returns')
                ->where('deleted_at', '=', null)
                ->where('provider_id', $provider->id)
                ->sum('paid_amount');

            $item['return_Due'] = $item['total_amount_return'] - $item['total_paid_return'];

            $item['id'] = $provider->id;
            $item['name'] = $provider->name;
            $item['phone_1'] = $provider->phone_1;
            $item['phone_2'] = $provider->phone_2;
            $item['tax_number'] = $provider->tax_number;
            $item['code'] = $provider->code;
            $item['email'] = $provider->email;
            $item['country'] = $provider->country;
            $item['city'] = $provider->city;
            $item['address'] = $provider->address;
            $data[] = $item;
        }

        $company_info = Setting::where('deleted_at', '=', null)->first();
        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id', 'account_name']);

        return response()->json([
            'providers' => $data,
            'company_info' => $company_info,
            'totalRows' => $totalRows,
            'accounts' => $accounts,
        ]);
    }

    //----------- Store new Supplier -------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Provider::class);

        $request->validate([
            'name' => 'required',
        ]);
        Provider::create([
            'name' => $request['name'],
            'code' => $request['code'] ?? $this->getNumberOrder(),
            'address' => $request['address'],
            'phone_1' => $request['phone_1'],
            'phone_2' => $request['phone_2'],
            'email' => $request['email'],
            'country' => $request['country'],
            'city' => $request['city'],
            'tax_number' => $request['tax_number'],
        ]);
        return response()->json(['success' => true]);

    }

    //------------ function show -----------\\

    public function show($id)
    {
        //

    }

    //----------- Update Supplier-------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Provider::class);

        $request->validate([
            'name' => 'required',
        ]);

        Provider::whereId($id)->update([
            'name' => $request['name'],
            'address' => $request['address'],
            'phone_1' => $request['phone_1'],
            'phone_2' => $request['phone_2'],
            'email' => $request['email'],
            'country' => $request['country'],
            'city' => $request['city'],
            'tax_number' => $request['tax_number'],
        ]);
        return response()->json(['success' => true]);

    }

    //----------- Remdeleteove Provider-------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Provider::class);

        Provider::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);

    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'delete', Provider::class);

        $selectedIds = $request->selectedIds;
        foreach ($selectedIds as $Provider_id) {
            Provider::whereId($Provider_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true]);
    }


    //----------- get Number Order Of Suppliers-------\\

    public function getNumberOrder()
    {

        $last = DB::table('providers')->latest('id')->first();
        $code = $last ? $last->code + 1 : 1;
        return $code;
    }

    // import providers
    public function import_providers(Request $request)
    {
        $file_upload = $request->file('providers');
        $ext = pathinfo($file_upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv') {
            return response()->json([
                'msg' => 'must be in csv format',
                'status' => false,
            ]);
        } else {
            $data = array();
            $rowcount = 0;
            if (($handle = fopen($file_upload, "r")) !== false) {
                $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
                $header = fgetcsv($handle, $max_line_length);
                $header_colcount = count($header);
                while (($row = fgetcsv($handle, $max_line_length)) !== false) {
                    $row_colcount = count($row);
                    if ($row_colcount == $header_colcount) {
                        $entry = array_combine($header, $row);
                        $data[] = $entry;
                    } else {
                        return null;
                    }
                    $rowcount++;
                }
                fclose($handle);
            } else {
                return null;
            }

            $rules = array('name' => 'required');

            //-- Create New Provider
            foreach ($data as $key => $value) {

                $input['name'] = $value['name'];

                $validator = Validator::make($input, $rules);
                if (!$validator->fails()) {

                    Provider::create([
                        'name' => $value['name'],
                        'code' => $value['code'] ?? $this->getNumberOrder(),
                        'address' => $value['address']?? null,
                        'phone_1' => $value['phone_1'] ?? null,
                        'phone_2' => $value['phone_2'] ?? null,
                        'email' => $value['email'] ?? null,
                        'country' => $value['country'] ?? null,
                        'city' => $value['city'] ?? null,
                        'tax_number' => $value['tax_number'] ?? null,
                    ]);
                }

            }

            return response()->json([
                'status' => true,
            ], 200);
        }

    }


    //------------- pay_supplier_due -------------\\

    public function pay_supplier_due(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'pay_supplier_due', Provider::class);

        if ($request['amount'] > 0) {
            $provider_purchases_due = Purchase::where('deleted_at', '=', null)
                ->where('status', 'received')
                ->where([
                    ['payment_status', '!=', 'paid'],
                    ['provider_id', $request->provider_id]
                ])->get();

            $paid_amount_total = $request->amount;

            foreach ($provider_purchases_due as $key => $provider_purchase) {
                if ($paid_amount_total == 0)
                    break;
                $due = $provider_purchase->grand_total - $provider_purchase->paid_amount;

                if ($paid_amount_total >= $due) {
                    $amount = $due;
                    $payment_status = 'paid';
                } else {
                    $amount = $paid_amount_total;
                    $payment_status = 'partial';
                }

                $payment_purchase = new PaymentPurchase();
                $payment_purchase->purchase_id = $provider_purchase->id;
                $payment_purchase->account_id = $request['account_id'] ? $request['account_id'] : NULL;
                $payment_purchase->Ref = app('App\Http\Controllers\PaymentPurchasesController')->getNumberOrder();
                $payment_purchase->date = Carbon::now();
                $payment_purchase->Reglement = $request['Reglement'];
                $payment_purchase->montant = $amount;
                $payment_purchase->change = 0;
                $payment_purchase->notes = $request['notes'];
                $payment_purchase->user_id = Auth::user()->id;
                $payment_purchase->save();

                $account = Account::where('id', $request['account_id'])->exists();

                if ($account) {
                    // Account exists, perform the update
                    $account = Account::find($request['account_id']);
                    $account->update([
                        'balance' => $account->balance - $amount,
                    ]);
                }

                $provider_purchase->paid_amount += $amount;
                $provider_purchase->payment_status = $payment_status;
                $provider_purchase->save();

                $paid_amount_total -= $amount;
            }
        }

        return response()->json(['success' => true]);

    }

    //------------- pay_purchase_return_due -------------\\

    public function pay_purchase_return_due(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'pay_purchase_return_due', Provider::class);

        if ($request['amount'] > 0) {
            $supplier_purchase_return_due = PurchaseReturn::where('deleted_at', '=', null)
                ->where([
                    ['payment_status', '!=', 'paid'],
                    ['provider_id', $request->provider_id]
                ])->get();

            $paid_amount_total = $request->amount;

            foreach ($supplier_purchase_return_due as $key => $supplier_purchase_return) {
                if ($paid_amount_total == 0)
                    break;
                $due = $supplier_purchase_return->grand_total - $supplier_purchase_return->paid_amount;

                if ($paid_amount_total >= $due) {
                    $amount = $due;
                    $payment_status = 'paid';
                } else {
                    $amount = $paid_amount_total;
                    $payment_status = 'partial';
                }

                $payment_purchase_return = new PaymentPurchaseReturns();
                $payment_purchase_return->purchase_return_id = $supplier_purchase_return->id;
                $payment_purchase_return->account_id = $request['account_id'] ? $request['account_id'] : NULL;
                $payment_purchase_return->Ref = app('App\Http\Controllers\PaymentPurchaseReturnsController')->getNumberOrder();
                $payment_purchase_return->date = Carbon::now();
                $payment_purchase_return->Reglement = $request['Reglement'];
                $payment_purchase_return->montant = $amount;
                $payment_purchase_return->change = 0;
                $payment_purchase_return->notes = $request['notes'];
                $payment_purchase_return->user_id = Auth::user()->id;
                $payment_purchase_return->save();

                $account = Account::where('id', $request['account_id'])->exists();

                if ($account) {
                    // Account exists, perform the update
                    $account = Account::find($request['account_id']);
                    $account->update([
                        'balance' => $account->balance + $amount,
                    ]);
                }

                $supplier_purchase_return->paid_amount += $amount;
                $supplier_purchase_return->payment_status = $payment_status;
                $supplier_purchase_return->save();

                $paid_amount_total -= $amount;
            }
        }

        return response()->json(['success' => true]);

    }

}
