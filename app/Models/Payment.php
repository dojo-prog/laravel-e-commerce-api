<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'payment_refeference',
        'method',
        'status',
        'amount',
        'paid_at',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'timestampTz'
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
