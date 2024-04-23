<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends BaseController
{

    //-------------- Get All Account ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Account::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Check If User Has Permission View  All Records
        $Accounts= Account::where('deleted_at', '=', null)
            
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('account_num', 'LIKE', "%{$request->search}%")
                    ->orWhere('account_name', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $Accounts->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $account_data = $Accounts->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($account_data as $account) {

            $item['id'] = $account->id;
            $item['account_num'] = $account->account_num;
            $item['account_name'] = $account->account_name;
            $item['balance'] = $account->balance;
            $item['note'] = $account->note;
           
            $data[] = $item;
        }

        return response()->json([
            'accounts' => $data,
            'totalRows' => $totalRows,
        ]);

    }

    //-------------- Store New Account ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Account::class);

        request()->validate([
            'account_num' => 'required',
            'account_name' => 'required',
            'initial_balance' => 'required',
        ]);

        Account::create([
            'account_num' => $request['account_num'],
            'account_name' => $request['account_name'],
            'initial_balance' => $request['initial_balance'],
            'balance' => $request['initial_balance'],
            'note' => $request['note'],
        ]);

        return response()->json(['success' => true], 200);
    }

    //------------ function show -----------\\

    public function show($id){
    //
    
    }

    //-------------- Update Account ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Account::class);
        $Account = Account::findOrFail($id);

        request()->validate([
            'account_num' => 'required',
            'account_name' => 'required',
        ]);

        $Account->update([
            'account_num' => $request['account_num'],
            'account_name' => $request['account_name'],
            'note' => $request['note'],
        ]);

        return response()->json(['success' => true], 200);

    }

    //-------------- Delete Account ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        $Account = Account::findOrFail($id);

        $Account->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true], 200);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Account::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $account_id) {
            $Account = Account::findOrFail($account_id);

            $Account->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true], 200);
    }

}
