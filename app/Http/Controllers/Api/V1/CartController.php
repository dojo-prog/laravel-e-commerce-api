<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $cart = $request->user()->cart()
            ->firstOrCreate()
            ->load(['cartItems.product.category']);

        $this->authorize('view', $cart);

        return CartResource::make($cart);
    }
}
