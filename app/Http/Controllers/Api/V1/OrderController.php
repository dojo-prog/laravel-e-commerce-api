<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(IndexOrderRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Order::class);

        $filters = $request->validated();
        $user = $request->user();
        $query = $user->hasRole(['admin'])
            ? Order::query()
            : $user->orders();

        $orders = $query
            ->with(['orderItems', 'payments', 'statusHistories'])
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['payment_status'] ?? null,
                fn ($query, $status) => $query->whereHas(
                    'payments',
                    fn ($paymentQuery) => $paymentQuery->where('status', $status)
                )
            )
            ->when(
                $filters['from'] ?? null,
                fn ($query, $from) => $query->whereDate('created_at', '>=', $from)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $to) => $query->whereDate('created_at', '<=', $to)
            )
            ->when(
                $filters['order_number'] ?? null,
                fn ($query, $orderNumber) => $query->where(
                    'order_number',
                    'ilike',
                    "%{$orderNumber}%"
                )
            )
            ->orderBy(
                $filters['sort_by'] ?? 'created_at',
                $filters['sort_order'] ?? 'desc'
            )
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 15);

        return OrderResource::collection($orders);
    }

    public function show(Request $request, string $order): OrderResource
    {
        $query = $request->user()->hasRole(['admin'])
            ? Order::query()
            : $request->user()->orders();

        $orderModel = $query
            ->with(['orderItems.product', 'payments', 'statusHistories'])
            ->whereKey($order)
            ->firstOrFail();

        $this->authorize('view', $orderModel);

        return OrderResource::make($orderModel);
    }
}
