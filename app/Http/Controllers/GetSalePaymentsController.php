<?php

namespace App\Http\Controllers;

use App\Models\PaymentSale;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetSalePaymentsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentSale::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $sale = Sale::findOrFail($id);

        // Check If User Has Permission view All Records
        if (!$view_records) {
            // Check If User->id === Sale->id
            $this->authorizeForUser($request->user('api'), 'check_record', $sale);
        }

        $payments = $sale->payments()
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', request()->user()->id);
                }
            })->orderBy('id', 'DESC')->get();


        return response()->json(['payments' => $payments, 'due' => $sale->due]);

    }
}
