<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $availability = $this->bookingService->getAvailability($request->date);

        return response()->json($availability);
    }

    public function index(Request $request)
    {
        // Auto-expire pending bookings that have passed their expiry time
        $request->user()->bookings()
            ->where('status', 'pending_payment')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $bookings = $request->user()->bookings()
            ->with(['items.court', 'payment'])
            ->latest()
            ->get();

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        // Handle raw array payload
        if (array_is_list($data) && count($data) > 0) {
            $data = ['items' => $data];
            $request->replace($data);
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.court_id' => 'required|exists:courts,id',
            'items.*.date' => 'required|date_format:Y-m-d',
            'items.*.start_time' => 'required|date_format:H:i',
            'items.*.end_time' => 'required|date_format:H:i|after:items.*.start_time',
        ]);

        try {
            $booking = $this->bookingService->createBooking($request->user(), $request->items);
            // Refresh to ensure all attributes and appends are available
            $booking->refresh();
            return response()->json($booking->load('items', 'payment'), 201);
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        if ($booking->status === 'pending_payment' && $booking->expires_at < now()) {
            $booking->update(['status' => 'expired']);
        }

        return response()->json($booking->load(['items.court', 'payment']));
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('update', $booking);

        if ($booking->status === 'paid') {
            return response()->json(['message' => 'Cannot cancel paid booking via API. Contact support.'], 400);
        }

        $booking->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Booking cancelled']);
    }
}
