<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'booking_id',
        'court_id',
        'date',
        'start_time',
        'end_time',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
        // 'start_time' and 'end_time' are usually strings in 'H:i:s' format, or we can cast to custom date objects
        'price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
