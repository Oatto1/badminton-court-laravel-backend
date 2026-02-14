<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasUuids;

    protected $appends = ['booking_code'];

    public function getBookingCodeAttribute()
    {
        return $this->booking_number;
    }

    public function getRouteKeyName()
    {
        return 'booking_number';
    }

    protected $fillable = [
        'user_id',
        'booking_number',
        'total_price',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function paymentIntent(): HasOne
    {
        return $this->hasOne(PaymentIntent::class);
    }
}
