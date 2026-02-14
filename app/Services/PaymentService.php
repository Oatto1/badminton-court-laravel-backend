<?php

namespace App\Services;

use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\Booking;
use App\Models\PaymentIntent;
use App\Models\WebhookLog;
use Illuminate\Support\Str;

class PaymentService
{
    public function createIntent(Booking $booking): PaymentIntent
    {
        // Check if existing valid intent
        $existing = $booking->paymentIntent;
        if ($existing && $existing->status === 'waiting' && $existing->expired_at > now()) {
            return $existing;
        }

        // Create new Intent
        return PaymentIntent::create([
            'booking_id' => $booking->id,
            'qr_ref' => 'REF-' . Str::random(12),
            'current_currency' => 'THB',
            'amount' => $booking->total_amount,
            'status' => 'waiting',
            'expired_at' => now()->addMinutes(15), // Payment window
            // 'payment_token' => Call external API to get QR payload? 
            // For simulation, we assume qr_ref IS the token/payload or we generate a dummy one.
            'payment_token' => '00020101021129370016A000000677010111' . Str::random(10), // Dummy TLV for QR
        ]);
    }

    public function handleWebhook(array $payload, string $signature = null): void
    {
        // Log webhook first (Audit)
        $log = WebhookLog::create([
            'provider' => 'promptpay', // or detected provider
            'signature' => $signature,
            'payload' => $payload,
            'status' => 'pending',
            'ip_address' => request()->ip(),
        ]);

        // Dispatch Job to process it asynchronously
        ProcessPaymentWebhookJob::dispatch($log);
    }
}
