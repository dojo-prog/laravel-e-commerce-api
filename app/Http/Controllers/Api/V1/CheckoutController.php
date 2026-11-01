<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\PlaceOrder;
use App\Actions\Payments\ProcessPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use LogicException;

class CheckoutController extends Controller
{
    public function store(
        StoreCheckoutRequest $request,
        PlaceOrder $placeOrder,
        ProcessPayment $processPayment,
    ): JsonResponse {
        $this->authorize('create', Order::class);

        $order = $placeOrder->handle($request->user(), $request->validated());
        $initialPayment = $order->payments->firstWhere('attempt_number', 1);

        if ($initialPayment === null) {
            throw new LogicException('The checkout order does not have an initial payment attempt.');
        }

        $processPayment->handle($initialPayment);
        $order->load(['orderItems.product', 'payments', 'statusHistories']);

        return OrderResource::make($order)
            ->response()
            ->setStatusCode($order->wasRecentlyCreated ? 201 : 200);
    }
}
