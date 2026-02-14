<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourtSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'court_id',
        'schedule_date',
        'time_slot_id',
        'status',
        'price',
        'hold_token',
        'hold_expired_at',
        'booking_id',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'price' => 'decimal:2',
        'hold_expired_at' => 'datetime',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
