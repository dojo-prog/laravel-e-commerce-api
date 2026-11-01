<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function store(
        StoreCheckoutRequest $request,
        PlaceOrder $placeOrder
    ): JsonResponse {
        $this->authorize('create', Order::class);

        $order = $placeOrder->handle($request->user(), $request->validated());

        return OrderResource::make($order)
            ->response()
            ->setStatusCode($order->wasRecentlyCreated ? 201 : 200);
    }
}
