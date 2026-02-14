<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'payment_intent_id',
        'provider_tx_id',
        'amount',
        'payer_name',
        'payer_bank',
        'raw_payload',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class);
    }
}
