<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Address extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'is_default'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'is_default' => 'boolean'
        ];
    }

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

}
