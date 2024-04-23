<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\DepositCategory;
use App\Models\Role;
use App\utils\helpers;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositsController extends BaseController
{

    //-------------- Show All  Deposits -----------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Deposit::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // Filter fields With Params to retrieve
        $columns = array(0 => 'deposit_ref', 1 => 'account_id', 2 => 'date', 3 => 'deposit_category_id');
        $param = array(0 => 'like', 1 => '=', 2 => '=', 3 => '=');
        $data = array();

        // Check If User Has Permission View  All Records
        $Deposits = Deposit::with('deposit_category', 'account')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });

        //Multiple Filter
        $Filtred = $helpers->filter($Deposits, $columns, $param, $request)
        //Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('deposit_ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('date', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('deposit_category', function ($q) use ($request) {
                                $q->where('title', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('account', function ($q) use ($request) {
                                $q->where('account_name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });
        $totalRows = $Filtred->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $Deposits = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Deposits as $Deposit) {

            $item['id'] = $Deposit->id;
            $item['date'] = $Deposit->date;
            $item['deposit_ref'] = $Deposit->deposit_ref;
            $item['description'] = $Deposit->description;
            $item['amount'] = $Deposit->amount;
            $item['account_name'] = $Deposit['account']?$Deposit['account']->account_name:'N/D';
            $item['category_name'] = $Deposit['deposit_category']->title;
            $data[] = $item;
        }

        $Deposits_category = DepositCategory::where('deleted_at', '=', null)->get(['id', 'title']);
        $accounts = Account::where('deleted_at', '=', null)->get(['id', 'account_name']);

        return response()->json([
            'deposits' => $data,
            'accounts' => $accounts,
            'Deposits_category' => $Deposits_category,
            'totalRows' => $totalRows,
        ]);

    }

    //-------------- Store New Deposit -----------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Deposit::class);

        \DB::transaction(function () use ($request) {
            request()->validate([
                'deposit.date' => 'required',
                'deposit.category_id' => 'required',
                'deposit.amount' => 'required',
            ]);

            Deposit::create([
                'user_id' => Auth::user()->id,
                'date' => $request['deposit']['date'],
                'deposit_ref' => $this->getNumberOrder(),
                'account_id' => $request['deposit']['account_id'],
                'deposit_category_id' => $request['deposit']['category_id'],
                'description' => $request['deposit']['description'],
                'amount' => $request['deposit']['amount'],
            ]);

            $account = Account::find($request['deposit']['account_id']);

            if($account){
                $account->update([
                    'balance' => $account->balance + $request['deposit']['amount'],
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ function show -----------\\

    public function show($id){
        //
        
        }

    //-------------- Update  Deposit -----------\\

    public function update(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Deposit::class);

        \DB::transaction(function () use ($request , $id) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');
            $deposit = Deposit::findOrFail($id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === deposit->id
                $this->authorizeForUser($request->user('api'), 'check_record', $deposit);
            }
       
            request()->validate([
                'deposit.date' => 'required',
                'deposit.category_id' => 'required',
                'deposit.amount' => 'required',
            ]);

            $account = Account::find($deposit->account_id);

            if ($account) {
                $account->update([
                    'balance' => $account->balance - $deposit->amount,
                ]);
            }

            Deposit::whereId($id)->update([
                'date' => $request['deposit']['date'],
                'account_id' => $request['deposit']['account_id']?$request['deposit']['account_id']:NULL,
                'deposit_category_id' => $request['deposit']['category_id'],
                'description' => $request['deposit']['description'],
                'amount' => $request['deposit']['amount'],
            ]);

            $account = Account::find($request['deposit']['account_id']);
            if($account){
                $account->update([
                    'balance' => $account->balance + $request['deposit']['amount'],
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //-------------- Delete Deposit -----------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Deposit::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $deposit = Deposit::findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === deposit->id
            $this->authorizeForUser($request->user('api'), 'check_record', $deposit);
        }

        Deposit::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        $account = Account::where('id', $deposit->account_id)->exists();

        if ($account) {
            // Account exists, perform the update
            $account = Account::find($deposit->account_id);
            $account->update([
                'balance' => $account->balance - $deposit->amount,
            ]);
        }

        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Deposit::class);
        $selectedIds = $request->selectedIds;
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        foreach ($selectedIds as $deposit_id) {
            $deposit = Deposit::findOrFail($deposit_id);

            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === deposit->id
                $this->authorizeForUser($request->user('api'), 'check_record', $deposit);
            }

            $account = Account::where('id', $deposit->account_id)->exists();

            if ($account) {
                // Account exists, perform the update
                $account = Account::find($deposit->account_id);
                $account->update([
                    'balance' => $account->balance - $deposit->amount,
                ]);
            }

            Deposit::whereId($deposit_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
            
        }
        return response()->json(['success' => true]);
    }

    //--------------- Reference Number of Deposit ----------------\\

    public function getNumberOrder()
    {

        $last = DB::table('deposits')->latest('id')->first();

        if ($last) {
            $item = $last->deposit_ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'DP_1111';
        }
        return $code;

    }


    //---------------- Show Form Create Deposit ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Deposit::class);

        $deposits_category = DepositCategory::where('deleted_at', '=', null)->get(['id', 'title']);
        $accounts = Account::where('deleted_at', '=', null)->get(['id', 'account_name']);

        return response()->json([
            'deposits_category' => $deposits_category,
            'accounts' => $accounts,
        ]);
    }

    //------------- Show Form Edit Deposit -----------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Deposit::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Deposit = Deposit::where('deleted_at', '=', null)->findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Deposit->id
            $this->authorizeForUser($request->user('api'), 'check_record', $Deposit);
        }

        if ($Deposit->account_id) {
            if (Account::where('id', $Deposit->account_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $data['account_id'] = $Deposit->account_id;
            } else {
                $data['account_id'] = '';
            }
        } else {
            $data['account_id'] = '';
        }

        if ($Deposit->deposit_category_id) {
            if (DepositCategory::where('id', $Deposit->deposit_category_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $data['category_id'] = $Deposit->deposit_category_id;
            } else {
                $data['category_id'] = '';
            }
        } else {
            $data['category_id'] = '';
        }

        $data['date'] = $Deposit->date;
        $data['amount'] = $Deposit->amount;
        $data['description'] = $Deposit->description;


        $deposit_category = DepositCategory::where('deleted_at', '=', null)->get(['id', 'title']);
        $accounts = Account::where('deleted_at', '=', null)->get(['id', 'account_name']);

        return response()->json([
            'deposit' => $data,
            'deposit_category' => $deposit_category,
            'accounts' => $accounts,
        ]);
    }

}
