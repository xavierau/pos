<?php

namespace Modules\Promotions\Handlers;

use App\Events\CartUpdatedEvent;
use App\Services\Cart\Cart;
use Modules\Promotions\Entities\Promotion;

class CheckPromotionWhenCartUpdated
{

    public function __construct(
        public Cart $cart
    )
    {
    }

    public function handle(CartUpdatedEvent $event)
    {
        // get cart from session
        $cart = $this->cart->fetch($event->cartId);

        if (!$cart === null) return;

        // get details from cart
        $details = $cart['details'];

        $promotions = Promotion::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();


        // check promotions on the cart,
        $rewards = [];

        foreach ($promotions as $promotion) {
            $reward = $this->getPromotionRewards($promotion, $details);

            $rewards[] = $reward;
        }

        // update the cart

        $this->updateCart($event->cartId, $rewards);
    }

    private function updateCart(string $cartId, array $rewards)
    {
        // rewards are list of item that can be added to the cart. Item can free products, discount, etc.
        // update the cart with the rewards

        $cart = $this->cart->fetch($cartId);

        $cart['rewards'] = $rewards;

        $this->cart->update($cartId, $cart);

    }

    private function getPromotionRewards(mixed $promotion, mixed $details)
    {
    }

}
