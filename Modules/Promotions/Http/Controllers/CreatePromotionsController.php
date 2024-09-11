<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Promotions\DTO\StorePromotionDTO;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Http\Requests\CreatePromotionRequest;
use Modules\Promotions\Services\Actions\StorePromotion;

class CreatePromotionsController extends Controller
{
    public function __invoke(CreatePromotionRequest $request, StorePromotion $action)
    {

        $request->validated();

        $dto = StorePromotionDTO::fromRequest($request);

        $action->execute($dto);

        return response()->json([
            'promotions' => Promotion::all()
        ]);
    }
}
