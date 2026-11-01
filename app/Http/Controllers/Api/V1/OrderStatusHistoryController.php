<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusHistory\IndexOrderStatusHistoryRequest;
use App\Http\Resources\OrderStatusHistoryResource;
use App\Models\OrderStatusHistory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderStatusHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexOrderStatusHistoryRequest $request)
    {
        $this->authorize('viewAny', OrderStatusHistory::class);

        $filters = $request->validated();

        $query = OrderStatusHistory::query()
            ->with(['order', 'user'])
            ->when(
                $filters['search'] ?? null,
                function ($q, $search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereHas('order', fn ($orderQuery) => 
                            $orderQuery->where('order_number', 'ilike', "%{$search}%")
                        )
                        ->orWhereHas('user', fn ($userQuery) => 
                            $userQuery->where('name', 'ilike', "%{$search}%")
                        );
                    });
                }
            );
        
        $histories = $query
            ->paginate($filters['per_page'] ?? 15);

        return OrderStatusHistoryResource::collection($histories);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderStatusHistory $history)
    {
        $this->authorize('view', $history);

        $history->load(['order', 'user']);

        return OrderStatusHistoryResource::make($history);
    }

}
