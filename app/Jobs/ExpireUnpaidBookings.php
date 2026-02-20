<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireUnpaidBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();

        // Find bookings that are pending payment and past their expiration time
        // We chunk the results to handle large datasets efficiently
        Booking::where('status', 'pending_payment')
            ->where('expires_at', '<', $now)
            ->chunkById(100, function ($bookings) {
            foreach ($bookings as $booking) {
                try {
                    DB::transaction(function () use ($booking) {
                                    // 1. Mark booking as expired
                                    $booking->update(['status' => 'expired']);

                                    // 2. Mark associated payment as expired if it's still pending
                                    // We use the relationship to find the payment
                                    if ($booking->payment && $booking->payment->status === 'pending_payment') {
                                        $booking->payment->update(['status' => 'expired']);
                                    }

                                    // 3. Log the expiration
                                    Log::info("Booking {$booking->booking_number} expired automatically.");
                                }
                                );
                            }
                            catch (\Exception $e) {
                                Log::error("Failed to expire booking {$booking->id}: " . $e->getMessage());
                            }
                        }
                    });
    }
}