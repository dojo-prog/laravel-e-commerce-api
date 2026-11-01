<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItem\StoreCartItemRequest;
use App\Http\Requests\CartItem\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;

class CartItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartItemRequest $request)
    {
        $this->authorize('create', CartItem::class);

        $validated = $request->validated();

        $userCart = $request->user()->cart;

        $existing = CartItem::query()
            ->where('product_id', $validated['product_id'])
            ->where('cart_id', $userCart->id)
            ->first();
            

        if ($existing) {
            $existing->quantity += $validated['quantity'];

            $existing->save();

            $existing->load('product.category');

            return CartItemResource::make($existing);
        } 


        $cartItem = $userCart->cartItems()->create($validated);
        $cartItem->load('product.category');

        return (CartItemResource::make($cartItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CartItem $cartItem)
    {
        $this->authorize('view', $cartItem);

        $cartItem->load('product.category');

        return CartItemResource::make($cartItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        $this->authorize('update', $cartItem);

        $validated = $request->validated();

        $newQty = $validated['quantity'];

        if ($newQty === 0) {
            $cartItem->delete();

            return response()->noContent();
        }

        $cartItem->quantity = $newQty;
        $cartItem->save();
        $cartItem->load('product.category');

        return CartItemResource::make($cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {
        $this->authorize('delete', $cartItem);

        $cartItem->delete();

        return response()->noContent();
    }
}
