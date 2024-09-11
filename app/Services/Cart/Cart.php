<?php

namespace App\Services\Cart;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class Cart
{

    public function create(string $cartId, int $warehouse_id, int $client_id)
    {
        $sessionId = $this->getSessionKey($cartId);

        if (!Session::has($sessionId)) {
            $cart = [
                'id' => $sessionId,
                'details' => [],
                'warehouse_id' => $warehouse_id ?? null,
                'client_id' => $client_id ?? null,
                'created_at' => now()->toISOString(),
            ];
            Session::put('cart', $cart);
        }


        return Session::get($sessionId);
    }

    public function fetch(string $cartId)
    {
        return Session::get($this->getSessionKey($cartId));
    }

    public function update(string $cartId, array $details, int $warehouse_id, int $client_id)
    {
        $cart = $this->fetch($cartId);

        if ($cart === null) {
            $cartId = $cartId ?? Str::random(8);
            $this->create($cartId, $warehouse_id, $client_id);
        }

        $cart = $this->fetch($cartId);

        $cart['details'] = $details;
        $cart['warehouse_id'] = $warehouse_id;
        $cart['client_id'] = $client_id;
        Session::put($this->getSessionKey($cartId), $cart);

        return $this->fetch($cartId);
    }

    private function getSessionKey(string $key)
    {
        return "cart_{$key}";
    }

}
