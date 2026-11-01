<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payments\RetryPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PaymentController extends Controller
{
    public function store(Request $request, string $order, RetryPayment $retryPayment): PaymentResource
    {
        $query = $request->user()->hasRole(['admin'])
            ? Order::query()
            : $request->user()->orders();

        $orderModel = $query->whereKey($order)->firstOrFail();
        $this->authorize('retryPayment', $orderModel);

        if ($orderModel->status !== 'pending') {
            throw new ConflictHttpException(
                'Payments can only be retried while the order is pending.'
            );
        }

        return PaymentResource::make($retryPayment->handle($orderModel));
    }
}
