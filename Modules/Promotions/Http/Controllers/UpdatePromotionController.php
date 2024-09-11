<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Promotions\DTO\UpdatePromotionDTO;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Http\Requests\UpdatePromotionRequest;
use Modules\Promotions\Services\Actions\UpdatePromotion;

class UpdatePromotionController extends Controller
{
    public function __invoke(UpdatePromotionRequest $request, Promotion $promotion, UpdatePromotion $action)
    {

        $request->validated();

        $dto = UpdatePromotionDTO::fromRequest($request);

        $promotion = $action->execute($dto);

        return response()->json(compact('promotion'));

    }
}
