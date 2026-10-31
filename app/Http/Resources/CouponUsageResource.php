<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponUsageResource extends JsonResource
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
            'coupon_id' => $this->coupon_id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'discount_amount' => $this->discount_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'coupon' => CouponResource::make(
                $this->whenLoaded('coupon')
            ),
        ];
    }
}
