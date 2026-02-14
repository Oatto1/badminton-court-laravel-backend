<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'reference_no',
        'total_amount',
        'status',
        'payment_method',
        'expires_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        // Assuming OrderItem model exists or will be relevant, 
        // strictly following OrderResource which doesn't explicitly show items relationship in table view 
        // but it's good practice. 
        // However, I'll stick to what is strictly needed for now.
        // Actually, the cart checkout sends "items". So we probably need OrderItem.
        // Let's check if OrderItem exists. Yes, it does in the file list I saw earlier.
        return $this->hasMany(OrderItem::class);
    }
}
