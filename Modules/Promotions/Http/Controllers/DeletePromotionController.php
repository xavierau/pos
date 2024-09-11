<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Promotions\Entities\Promotion;

class DeletePromotionController extends Controller
{
    public function __invoke(Request $request, Promotion $promotion)
    {
        $promotion->delete();

        return response()->json(['status'=>'completed']);
    }
}
