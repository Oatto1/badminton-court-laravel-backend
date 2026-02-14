<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentIntent;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Simulate a payment webhook/confirmation.
     * In production, this would be a webhook from SCB/Stripe/2C2P.
     */
    public function mockSuccess(Request $request)
    {
        $request->validate([
            'booking_number' => 'required|exists:bookings,booking_number',
        ]);

        $booking = Booking::where('booking_number', $request->booking_number)->firstOrFail();

        if ($booking->status === 'paid') {
            return response()->json(['message' => 'Booking already paid'], 200);
        }

        // Load the payment record
        $payment = $booking->payment;

        if (!$payment) {
            // Should not happen if BookingService works, but create if missing
            $payment = $booking->payment()->create([
                'user_id' => $booking->user_id,
                'amount' => $booking->total_price,
                'method' => 'promptpay',
                'status' => 'pending',
            ]);
        }

        DB::transaction(function () use ($booking, $payment) {
            // 1. Update Payment Record
            $payment->update([
                'status' => 'paid',
                'transaction_ref' => 'TXN-' . strtoupper(Str::random(12)),
                'paid_at' => now(), // Assume we add paid_at column or just rely on updated_at
            ]);

            // 2. Update Booking
            $booking->update([
                'status' => 'paid', // Or 'confirmed' as per requirement, user said "paid" -> "confirmed" in reqs
            ]);
            // Re-read reqs: "Booking status = confirmed"
            $booking->update([
                'status' => 'confirmed',
            ]);
        });

        return response()->json(['message' => 'Payment successful', 'booking' => $booking->fresh(['payment'])]);
    }
}
