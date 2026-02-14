<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Court;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPaymentFlowTest extends TestCase
{
    use RefreshDatabase; // Use with caution on production/dev database if not configured for testing

    public function test_booking_creation_and_payment_flow()
    {
        // 1. Setup
        $user = User::factory()->create();
        $court = Court::create([ // Create a court manually as factory might not exist
            'name' => 'Test Court 1',
            'price_per_hour' => 150.00,
            'is_active' => true,
        ]);

        $date = now()->addDay()->format('Y-m-d');
        $startTime = '10:00';
        $endTime = '11:00'; // 1 hour

        // 2. Booking Request
        $response = $this->actingAs($user)->postJson('/api/bookings', [
            'items' => [
                [
                    'court_id' => $court->id,
                    'date' => $date, // Y-m-d
                    'start_time' => $startTime, // H:i
                    'end_time' => $endTime, // H:i
                ]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'booking_number', 'total_price', 'status', 'payment' => ['status']
        ]);

        $bookingNumber = $response->json('booking_number');
        $totalPrice = $response->json('total_price');

        // 3. Verify Database State (Pending)
        $this->assertDatabaseHas('bookings', [
            'booking_number' => $bookingNumber,
            'status' => 'pending_payment',
            'user_id' => $user->id,
            'total_price' => 150.00,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $response->json('id'),
            'status' => 'pending',
            'amount' => 150.00,
            'method' => 'promptpay',
        ]);

        // 4. Payment Confirmation
        $confirmResponse = $this->actingAs($user)->postJson('/api/payments/confirm', [
            'booking_number' => $bookingNumber,
        ]);

        $confirmResponse->assertStatus(200);
        $confirmResponse->assertJson(['message' => 'Payment successful']);

        // 5. Verify Database State (Paid/Confirmed)
        $this->assertDatabaseHas('bookings', [
            'booking_number' => $bookingNumber,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $response->json('id'),
            'status' => 'paid',
        ]);
    }
}
