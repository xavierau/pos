<?php

namespace App\Http\Controllers;

use App\Services\Cart\Cart;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    public function createCart(Request $request, Cart $cart)
    {
        $cart->create($request->get('id'),
            $request->get('warehouseId'),
            $request->get('clientId'));

        return response()->json(['message' => 'Cart created successfully']);
    }

    public function updateCart(Request $request, $cartId, Cart $cart)
    {

        $cart->update($cartId, $request->get('details'));

        return response()->json(['message' => 'Cart created successfully']);

    }
}
