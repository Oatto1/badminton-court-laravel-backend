<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Payment;
use App\Jobs\ExpireUnpaidBookings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class BookingExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_expires_after_time_limit()
    {
        // 1. Setup
        Carbon::setTestNow(now());
        $user = User::factory()->create();

        // Create a booking that expires in 5 minutes
        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_number' => 'TEST-Expiry',
            'total_price' => 100,
            'status' => 'pending_payment',
            'expires_at' => now()->addMinutes(5),
            'items' => [] // Assuming items not strictly required for this test or handled by model
        ]);

        // Create associated payment
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'pending',
            'method' => 'promptpay'
        ]);

        // 2. Fast forward time by 6 minutes
        Carbon::setTestNow(now()->addMinutes(6));

        // 3. Run the expiry job
        $job = new ExpireUnpaidBookings();
        $job->handle();

        // 4. Verify booking status
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'expired',
        ]);

        // 5. Verify payment status
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'expired',
        ]);
    }

    public function test_paid_booking_does_not_expire()
    {
        // 1. Setup
        Carbon::setTestNow(now());
        $user = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $user->id,
            'booking_number' => 'TEST-Paid',
            'total_price' => 100,
            'status' => 'paid', // Already paid
            'expires_at' => now()->addMinutes(5),
        ]);

        // 2. Fast forward time
        Carbon::setTestNow(now()->addMinutes(6));

        // 3. Run the expiry job
        $job = new ExpireUnpaidBookings();
        $job->handle();

        // 4. Verify status remains paid
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'paid',
        ]);
    }
}
