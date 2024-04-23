<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TransferMoney;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferMoneyController extends BaseController
{

    //-------------- Get All Transfer Money ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', TransferMoney::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Check If User Has Permission View  All Records
        $transfers = TransferMoney::with('from_account', 'to_account')->where('deleted_at', '=', null)
            
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return  $query->where(function ($query) use ($request) {
                        return $query->whereHas('from_account', function ($q) use ($request) {
                            $q->where('account_name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        return $query->whereHas('to_account', function ($q) use ($request) {
                            $q->where('account_name', 'LIKE', "%{$request->search}%");
                        });
                    });
                });
            });

        $totalRows = $transfers->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $transfer_data = $transfers->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($transfer_data as $transfer) {

            $item['id']           = $transfer->id;
            $item['from_account'] = $transfer['from_account']->account_name;
            $item['to_account']   = $transfer['to_account']->account_name;
            $item['from_account_id']     = $transfer->from_account_id;
            $item['to_account_id']       = $transfer->to_account_id;
            $item['amount']       = $transfer->amount;
            $item['date']         = $transfer->date;
           
            $data[] = $item;
        }

        $accounts = Account::where('deleted_at', '=', null)->get(['id', 'account_name']);

        return response()->json([
            'transfers' => $data,
            'accounts' => $accounts,
            'totalRows' => $totalRows,
        ]);

    }

    //-------------- Store New TransferMoney ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', TransferMoney::class);


        request()->validate([
            'from_account_id' => 'required|different:to_account_id',
            'to_account_id' => 'required|different:from_account_id',
            'amount' => 'required',
            'date' => 'required',
        ],
        [
        'from_account_id.different' => 'From and to accounts cannot be the same.',
        'to_account_id.different' => 'To and from accounts cannot be the same.'
        ]);
       

        // Additional check if from_account_id and to_account_id are not the same
        if ($request->from_account_id === $request->to_account_id) {
            return response()->json(['error' => 'Account From and to account cannot be the same.'], 400);
        }

        // Retrieve account balances
        $from_account = Account::findOrFail($request->from_account_id);
        $to_account   = Account::findOrFail($request->to_account_id);

        // Check if from_account has enough balance
        if ($from_account->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance in the from account.'], 400);
        }
         
        \DB::transaction(function () use ($request, $from_account, $to_account) {

            $from_account->update([
                'balance' => $from_account->balance - $request->amount,
            ]);

            $to_account->update([
                'balance' => $to_account->balance + $request->amount,
            ]);

            TransferMoney::create([
                'from_account_id' => $request['from_account_id'],
                'to_account_id'   => $request['to_account_id'],
                'amount'          => $request['amount'],
                'date'            => $request['date'],
            ]);

        }, 10);

        return response()->json(['success' => true], 200);
    }

    //------------ function show -----------\\

    public function show($id){
    //
    
    }

    //-------------- Update TransferMoney ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', TransferMoney::class);
        

        $transfer = TransferMoney::findOrFail($id);

            
        request()->validate([
            'amount' => 'required',
            'date' => 'required',
        ]);

        // Retrieve account
        $from_account = Account::findOrFail($transfer->from_account_id);
        $to_account   = Account::findOrFail($transfer->to_account_id);

        // Additional check if from_account_id and to_account_id are not the same
        if ($request->from_account_id === $request->to_account_id) {
            return response()->json(['error' => 'Account From and to account cannot be the same.'], 400);
        }

        // Check if from_account has enough balance
        if (($from_account->balance + $transfer->amount) < $request->amount) {
            return response()->json(['error' => 'Insufficient balance in the from account.'], 400);
        }
           

        \DB::transaction(function () use ($request, $id, $from_account, $to_account, $transfer) {

            $from_account->update([
                'balance' => $from_account->balance + $transfer->amount - $request->amount,
            ]);
    
            $to_account->update([
                'balance' => $to_account->balance - $transfer->amount + $request->amount,
            ]);


            $transfer->update([
                'amount'          => $request['amount'],
                'date'            => $request['date'],
            ]);

        }, 10);
        return response()->json(['success' => true], 200);

    }

    //-------------- Delete TransferMoney ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', TransferMoney::class);

        \DB::transaction(function () use ($request, $id) {

           $transfer = TransferMoney::findOrFail($id);

            // Retrieve account
            $from_account = Account::findOrFail($transfer->from_account_id);
            $to_account   = Account::findOrFail($transfer->to_account_id);

            $from_account->update([
                'balance' => $from_account->balance + $transfer->amount,
            ]);

            $to_account->update([
                'balance' => $to_account->balance - $transfer->amount,
            ]);

            $transfer->update([
                'deleted_at' => Carbon::now(),
            ]);
            
        }, 10);

        return response()->json(['success' => true], 200);
    }


}
