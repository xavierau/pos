<?php

namespace App\Http\Controllers;

use App\Models\DepositCategory;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryDepositController extends BaseController
{

    //-------------- Get All Expense Categories ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', DepositCategory::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Check If User Has Permission View  All Records
        $DepositCategory = DepositCategory::where('deleted_at', '=', null)
            
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('title', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $DepositCategory->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $data = $DepositCategory->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'deposits_category' => $data,
            'totalRows' => $totalRows,
        ]);

    }

    //-------------- Store New Category ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', DepositCategory::class);

        request()->validate([
            'title' => 'required',
        ]);

        DepositCategory::create([
            'title' => $request['title'],
        ]);

        return response()->json(['success' => true], 200);
    }

    //------------ function show -----------\\

    public function show($id){
    //
    
    }

    //-------------- Update Category ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', DepositCategory::class);
        $DepositCategory = DepositCategory::findOrFail($id);

        request()->validate([
            'title' => 'required',
        ]);

        $DepositCategory->update([
            'title' => $request['title'],
        ]);

        return response()->json(['success' => true], 200);

    }

    //-------------- Delete Category ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', DepositCategory::class);
        $DepositCategory = DepositCategory::findOrFail($id);

        $DepositCategory->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true], 200);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', DepositCategory::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $category_id) {
            $DepositCategory = DepositCategory::findOrFail($category_id);

            $DepositCategory->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true], 200);
    }

}
