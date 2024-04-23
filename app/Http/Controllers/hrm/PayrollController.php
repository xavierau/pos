<?php

namespace App\Http\Controllers\hrm;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Account;
use App\Models\Payroll;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use DB;


class PayrollController extends Controller
{

    //----------- GET ALL Holidays --------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Payroll::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $data = [];

        $payrolls = Payroll::with('account','employee')->where('deleted_at', '=', null)

        // Search With Multiple Param
        ->where(function ($query) use ($request) {
            return $query->when($request->filled('search'), function ($query) use ($request) {
                return $query->where('Ref', 'LIKE', "%{$request->search}%")
                    ->orWhere(function ($query) use ($request) {
                        return $query->whereHas('account', function ($q) use ($request) {
                            $q->where('account_name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        return $query->whereHas('employee', function ($q) use ($request) {
                            $q->where('username', 'LIKE', "%{$request->search}%");
                        });
                    });
            });
        });
        $totalRows = $payrolls->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $payrolls_data = $payrolls->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($payrolls_data as $payroll) {
 
            $item['id']            = $payroll->id;
            $item['Ref']           = $payroll->Ref;
            $item['account_id']    = $payroll->account_id;
            $item['employee_id']   = $payroll->employee_id;
            $item['account_name']  = $payroll['account']?$payroll['account']->account_name:'---';
            $item['employee_name'] = $payroll['employee']->username;
            $item['date']          = $payroll->date;
            $item['amount']        = $payroll->amount;
            $item['payment_method']= $payroll->payment_method;
            $item['payment_status']= $payroll->payment_status;
            
            $data[] = $item;
        }

        $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);
        $employees = Employee::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);

        return response()->json([
            'payrolls'  => $data,
            'totalRows' => $totalRows,
            'accounts'  => $accounts,
            'employees' => $employees,
        ]);
    }



    public function create(Request $request)
    {
        // 
    }

    //----------- Store new Payroll --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Payroll::class);

        request()->validate([
            'date'            => 'required',
            'employee_id'     => 'required',
            'amount'          => 'required',
            'payment_method'  => 'required',
        ]);

        \DB::transaction(function () use ($request) {

            Payroll::create([
                'user_id'         => Auth::user()->id,
                'Ref'             => $this->getNumberOrder(),
                'date'            => $request['date'],
                'employee_id'     => $request['employee_id'],
                'account_id'      => $request['account_id']?$request['account_id']:NULL,
                'amount'          => $request['amount'],
                'payment_method'  => $request['payment_method'],
                'payment_status'  => 'paid',
            ]);

            $account = Account::where('id', $request['account_id'])->exists();

            if ($account) {
                // Account exists, perform the update
                $account = Account::find($request['account_id']);
                $account->update([
                    'balance' => $account->balance - $request['amount'],
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);
    }

    //------------ function show -----------\\

    public function show($id){
        //
        
    }


    public function edit(Request $request ,$id)
    {
        // 

    }

    //-----------Update Payroll --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Payroll::class);

        request()->validate([
            'date'            => 'required',
            'employee_id'     => 'required',
            'amount'          => 'required',
            'payment_method'  => 'required',
        ]);


        \DB::transaction(function () use ($id, $request) {

            $payroll = Payroll::findOrFail($id);

            //delete old balance
            $account = Account::where('id', $payroll->account_id)->exists();

            if ($account) {
                // Account exists, perform the update
                $account = Account::find($payroll->account_id);
                $account->update([
                    'balance' => $account->balance + $payroll->amount,
                ]);
            }

            Payroll::whereId($id)->update([
                'date'            => $request['date'],
                'employee_id'     => $request['employee_id'],
                'account_id'      => $request['account_id']?$request['account_id']:NULL,
                'amount'          => $request['amount'],
                'payment_method'  => $request['payment_method'],
            ]);

             //update new account
             $new_account = Account::where('id', $request['account_id'])->exists();

             if ($new_account) {
                 // Account exists, perform the update
                 $new_account = Account::find($request['account_id']);
                 $new_account->update([
                     'balance' => $new_account->balance - $request['amount'],
                 ]);
             }

        }, 10);

        return response()->json(['success' => true]);
    }

    //----------- Delete  Payroll --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Payroll::class);
        \DB::transaction(function () use ($id, $request) {
            $payroll = Payroll::findOrFail($id);

            Payroll::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            $account = Account::where('id', $payroll->account_id)->exists();

            if ($account) {
                // Account exists, perform the update
                $account = Account::find($payroll->account_id);
                $account->update([
                    'balance' => $account->balance + $payroll->amount,
                ]);
            }

        }, 10);


        return response()->json(['success' => true]);
    }

      //------------ Reference Number of Adjustement  -----------\\

      public function getNumberOrder()
      {
  
          $last = DB::table('payrolls')->latest('id')->first();
  
          if ($last) {
              $item = $last->Ref;
              $nwMsg = explode("_", $item);
              $inMsg = $nwMsg[1] + 1;
              $code = $nwMsg[0] . '_' . $inMsg;
          } else {
              $code = 'PS_1';
          }
          return $code;
  
      }



}
