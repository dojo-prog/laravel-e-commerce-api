<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'shipping_fee' => $this->shipping_fee,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'shipping_address_snapshot' => $this->shipping_address_snapshot,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'orderItems' => OrderItemResource::collection(
                $this->whenLoaded('orderItems')
            ),
            'payment_attempts' => PaymentResource::collection(
                $this->whenLoaded('payments')
            ),
            'statusHistories' => OrderStatusHistoryResource::collection(
                $this->whenLoaded('statusHistories')
            ),
        ];
    }
}
