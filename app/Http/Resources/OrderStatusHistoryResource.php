<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
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
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'order' => OrderResource::make(
                $this->whenLoaded('order')
            ),
            'user' => UserResource::make(
                $this->whenLoaded('user')
            )
        ];
    }
}
