<?php

namespace App\Http\Controllers;

use App\Enums\AggregateDateType;
use App\Models\Role;
use App\Services\Data\DTO\DataQueryOption;
use App\Services\Data\GetPaymentPurchasesDataService;
use App\Services\Data\GetPaymentSalesDataService;
use App\Services\Data\GetPaymentSalesReturnsDataService;
use App\Services\Data\GetPurchaseDataService;
use App\Services\Data\GetSaleDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardChartController extends Controller
{
    public function getSalesPurchasesData(Request                $request,
                                          GetSaleDataService     $saleDataService,
                                          GetPurchaseDataService $purchaseDataService)
    {

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $queryOption = new DataQueryOption(
            aggregateType: AggregateDateType::Day,
            start_date: $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(6)->clone(),
            end_date: $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->clone(),
            warehouse_id: $request->get('warehouse_id', null),
            user_id: $view_records ? null : $request->user('api')->id,
        );

        $salesData = $saleDataService->execute($queryOption);

        $purchaseData = $purchaseDataService->execute($queryOption);

        return response()->json([
            'sales' => $salesData,
            'purchase' => $purchaseData,
        ]);
    }

    public function getSalesPurchasesPaymentsData(Request                           $request,
                                                  GetPaymentPurchasesDataService    $paymentPurchasesDataService,
                                                  GetPaymentSalesDataService        $paymentSalesDataService,
                                                  GetPaymentSalesReturnsDataService $paymentSalesReturnsDataService)
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $queryOption = new DataQueryOption(
            aggregateType: AggregateDateType::Day,
            start_date: $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(6)->clone(),
            end_date: $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now()->clone(),
            warehouse_id: $request->get('warehouse_id', null),
            user_id: $view_records ? null : $request->user('api')->id,
        );

        $paymentPurchasesData = $paymentPurchasesDataService->execute($queryOption);

        $paymentSalesData = $paymentSalesDataService->execute($queryOption);

        $paymentSalesReturnsData = $paymentSalesReturnsDataService->execute($queryOption);

        return response()->json([
            'payment_purchases' => $paymentPurchasesData,
            'payment_sales' => $paymentSalesData,
            'payment_sales_returns' => $paymentSalesReturnsData,
        ]);

    }
}
