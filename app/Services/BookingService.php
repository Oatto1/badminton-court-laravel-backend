<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class BookingService
{
    /**
     * Get availability for a specific date.
     * Returns an array of courts with their slots.
     */
    public function getAvailability(string $date)
    {
        $courts = Court::where('is_active', true)->get();
        $dateCarbon = Carbon::parse($date);

        // Fetch global operating time for this day (Monday, Tuesday, etc.)
        $dayName = $dateCarbon->format('l');
        $operatingTime = \App\Models\OperatingTime::where('day', $dayName)->first();

        $startHour = 10; // Default
        $endHour = 24; // Default (midnight)
        $openTimeStr = '10:00';
        $closeTimeStr = '24:00';

        if ($operatingTime) {
            $startHour = (int)substr($operatingTime->open_time, 0, 2);
            $endHour = (int)substr($operatingTime->close_time, 0, 2);
            $openTimeStr = substr($operatingTime->open_time, 0, 5);
            $closeTimeStr = substr($operatingTime->close_time, 0, 5);
            if ($endHour === 0) {
                $endHour = 24;
                $closeTimeStr = '24:00';
            }
        }

        // Generate slots based on global operating hours
        $slots = [];
        for ($i = $startHour; $i < $endHour; $i++) {
            $slots[] = [
                'start' => sprintf('%02d:00', $i),
                'end' => sprintf('%02d:00', $i + 1),
            ];
        }

        // Fetch existing bookings for this date
        // Only count slots as booked if booking is confirmed/paid,
        // OR pending_payment that hasn't expired yet
        $bookedItems = BookingItem::whereDate('date', $dateCarbon)
            ->whereHas('booking', function ($query) {
            $query->whereNotIn('status', ['cancelled', 'expired'])
                ->where(function ($q) {
                $q->whereIn('status', ['confirmed', 'paid'])
                    ->orWhere(function ($q2) {
                    $q2->where('status', 'pending_payment')
                        ->where('expires_at', '>', Carbon::now());
                }
                );
            }
            );
        })
            ->get();

        $availability = $courts->map(function ($court) use ($slots, $bookedItems, $dateCarbon, $openTimeStr, $closeTimeStr) {
            $courtSlots = collect($slots)->map(function ($slot) use ($court, $bookedItems, $dateCarbon) {
                    // If date is today, mark past slots as unavailable
                    $isPast = false;
                    if ($dateCarbon->isToday()) {
                        $slotStartHour = (int)substr($slot['start'], 0, 2);
                        $isPast = $slotStartHour <= Carbon::now()->hour;
                    }

                    $isBooked = $isPast || $bookedItems->contains(function ($item) use ($court, $slot) {
                            return $item->court_id === $court->id &&
                            substr($item->start_time, 0, 5) === $slot['start'];
                        }
                        );

                        return [
                        'start' => $slot['start'],
                        'end' => $slot['end'],
                        'is_booked' => $isBooked,
                        'price' => $court->price_per_hour,
                        ];
                    }
                    );

                    return [
                    'id' => $court->id,
                    'name' => $court->name,
                    'open_time' => $openTimeStr,
                    'close_time' => $closeTimeStr,
                    'slots' => $courtSlots,
                    ];
                });

        return $availability;
    }

    /**
     * Create a new booking with lock mechanism.
     */
    public function createBooking(User $user, array $items)
    {
        // Use Redis funnel to prevent race conditions for the same court/time slots
        // We create a lock key based on the first item or a general booking lock
        // For strict correctness, we should lock each slot, but a global user lock or court lock is a good start.
        // Let's lock per court for the given time.

        return Redis::funnel('booking_creation')->limit(1)->block(5)->then(function () use ($user, $items) {
            return DB::transaction(function () use ($user, $items) {
                    $totalPrice = 0;
                    $bookingItems = [];

                    // 1. Validate availability and calculate price
                    foreach ($items as $item) {
                        $court = Court::findOrFail($item['court_id']);

                        // Double check availability inside the lock/transaction
                        // Same logic: only block if confirmed/paid or pending and not yet expired
                        $exists = BookingItem::where('court_id', $item['court_id'])
                            ->where('date', $item['date'])
                            ->where('start_time', $item['start_time'])
                            ->whereHas('booking', function ($q) {
                        $q->whereNotIn('status', ['cancelled', 'expired'])
                            ->where(function ($q2) {
                            $q2->whereIn('status', ['confirmed', 'paid'])
                                ->orWhere(function ($q3) {
                                $q3->where('status', 'pending_payment')
                                    ->where('expires_at', '>', Carbon::now());
                            }
                            );
                        }
                        );
                    }
                    )
                        ->lockForUpdate()
                        ->exists();

                    if ($exists) {
                        throw new \Exception("Court {$court->name} is already booked at {$item['start_time']}.");
                    }

                    $price = $court->price_per_hour;
                    $totalPrice += $price;

                    $bookingItems[] = [
                        'court_id' => $court->id,
                        'date' => $item['date'],
                        'start_time' => $item['start_time'],
                        'end_time' => $item['end_time'],
                        'price' => $price,
                    ];
                }

                // 2. Create Booking
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'booking_number' => 'BK-' . strtoupper(Str::random(8)),
                    'total_price' => $totalPrice,
                    'status' => 'pending_payment',
                    'expires_at' => Carbon::now()->addMinutes(1),
                ]);

                // 3. Create Items
                foreach ($bookingItems as $data) {
                    $booking->items()->create($data);
                }

                // 4. Create Payment Record automatically
                $booking->payment()->create([
                    'user_id' => $user->id,
                    'amount' => $totalPrice,
                    'method' => 'promptpay',
                    'status' => 'pending_payment',
                    'transaction_ref' => null, // Will be filled when paid
                ]);

                return $booking;
            }
            );
        }, function () {
            throw new \Exception("System is busy, please try again.");
        });
    }

    /**
     * Confirm a payment and update booking status.
     */
    public function confirmPayment(string $bookingNumber, string $transactionRef = null)
    {
        return DB::transaction(function () use ($bookingNumber, $transactionRef) {
            $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();

            if ($booking->status === 'paid' || $booking->status === 'confirmed') {
                return $booking; // Already processed
            }

            if ($booking->status === 'expired' || $booking->status === 'cancelled') {
                throw new \Exception("Booking is {$booking->status} and cannot be paid.");
            }

            // Update Booking
            $booking->update([
                'status' => 'confirmed', // or 'paid' based on preference, using 'confirmed' as per test
            ]);

            // Update Payment
            if ($booking->payment) {
                $booking->payment->update([
                    'status' => 'paid',
                    'transaction_ref' => $transactionRef,
                ]);
            }
            else {
                // Should not happen usually, but create if missing
                $booking->payment()->create([
                    'user_id' => $booking->user_id,
                    'amount' => $booking->total_price,
                    'method' => 'promptpay', // Default or pass as arg
                    'status' => 'paid',
                    'transaction_ref' => $transactionRef,
                ]);
            }

            return $booking;
        });
    }
}
