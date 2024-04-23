<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EcommerceClient;
use App\Models\Setting;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Http\Request;

class ClientsEcommerceController extends BaseController
{

    //------------- Get ALL clients_without_ecommerce -------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Client::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve
        $columns = array(0 => 'name', 1 => 'code', 2 => 'phone', 3 => 'email');
        $param = array(0 => 'like', 1 => 'like', 2 => 'like', 3 => 'like');
        $data = array();
        // $clients = Client::where('deleted_at', '=', null);
        $clients = \App\Models\Client::whereNotIn('id', function($query){
            $query->select('client_id')->from('ecommerce_clients');
        });

        //Multiple Filter
        $Filtred = $helpers->filter($clients, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%")
                        ->orWhere('phone', 'LIKE', "%{$request->search}%")
                        ->orWhere('email', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $Filtred->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $clients = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($clients as $client) {

            $item['id'] = $client->id;
            $item['name'] = $client->name;
            $item['phone'] = $client->phone;
            $item['code'] = $client->code;
            $item['email'] = $client->email;
            $data[] = $item;
        }

        $clientsWithoutEcommerce = \App\Models\Client::whereNotIn('id', function($query){
            $query->select('client_id')->from('ecommerce_clients');
        })->count();
        
        return response()->json([
            'clients' => $data,
            'totalRows' => $totalRows,
            'clients_without_ecommerce' => $clientsWithoutEcommerce,
        ]);
    }

    //------------- Store new Customer -------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Client::class);

        $this->validate($request, [
            'email'    => 'required|unique:ecommerce_clients',
            'password' => 'required|string|min:6',
        ], [
            'email.unique' => 'This Email already taken.',
        ]);

         // Check if the client_id already exists in the users table
         $client_exist = EcommerceClient::where('client_id', $request->client_id)->exists();

         if($client_exist){
            return response()->json(['success' => false] , 403);
         }else{
            $client = Client::where('id' , $request->client_id)->first();

            \DB::transaction(function () use ($request , $client) {

                EcommerceClient::create([
                    'client_id' => $request->client_id,
                    'username'  => $client->name,
                    'email'     => $request['email'],
                    'password'  => Hash::make($request['password']),
                    'status'    => 1,
                ]);
    
            }, 10);
         }


        return response()->json(['success' => true]);

    }

    //------------ function show -----------\\

    public function show($id){
        //
        
    }

    //------------- Update Customer -------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Client::class);
        
        $client_exist = EcommerceClient::where('client_id', $id)->exists();

        if($client_exist){
            $client_ecommerce = EcommerceClient::where('client_id' , $id)->first();
        }
      
        $this->validate($request, [
            'email' => [
                'required',
                'email',
                Rule::unique('ecommerce_clients')->ignore($client_ecommerce->client_id, 'client_id'),
            ],
        ], [
            'email.unique' => 'This Email is already taken.',
        ]);


        \DB::transaction(function () use ($id , $client_ecommerce , $request) {
            $current = $client_ecommerce->password;

            if ($request->NewPassword == 'null' || $request->NewPassword === null || $request->NewPassword == '') {
                $pass = $client_ecommerce->password;
            }else{

                if ($request->NewPassword != $current) {
                    $pass = Hash::make($request->NewPassword);
                } else {
                    $pass = $client_ecommerce->password;
                }

            }
                  
            EcommerceClient::where('client_id' , $id)->update([
                'email' => $request['email'],
                'password' => $pass,
            ]);

            Client::whereId($id)->update([
                'email' => $request['email'],
            ]);

        }, 10);
        
        return response()->json(['success' => true]);

    }

    //------------- delete client -------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Client::class);

        Client::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }



    //------------- get Number Order Customer -------------\\

    public function getNumberOrder()
    {
        $last = DB::table('clients')->latest('id')->first();

        if ($last) {
            $code = $last->code + 1;
        } else {
            $code = 1;
        }
        return $code;
    }


}
