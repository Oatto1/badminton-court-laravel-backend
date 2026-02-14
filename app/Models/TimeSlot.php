<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasUuids;

    protected $fillable = [
        'start_time',
        'end_time',
        'price_modifier',
        'is_peak',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_peak' => 'boolean',
    ];
}
