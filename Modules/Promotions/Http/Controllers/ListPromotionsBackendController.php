<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Promotions\DTO\PromotionFilter;
use Modules\Promotions\Entities\Promotion;

class ListPromotionsBackendController extends Controller
{
    public function __invoke(Request $request)
    {
        $paginator = Promotion::search($request->get('search', null))
            ->filter(PromotionFilter::fromRequest($request))
            ->orderBy(
                $request->get('SortField', 'created_at'),
                $request->get('SortType', 'desc')
            )
            ->paginate($request->get('limit', 15));
        return response()->json([
            'promotions' => $paginator->items(),
            'totalRows' => $paginator->total()
        ]);
    }
}
