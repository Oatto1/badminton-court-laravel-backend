<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider',
        'signature',
        'payload',
        'status',
        'error_message',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
