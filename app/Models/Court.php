<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'price_per_hour',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
