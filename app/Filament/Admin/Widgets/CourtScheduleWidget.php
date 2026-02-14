<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BookingItem;
use App\Models\Court;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CourtScheduleWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.court-schedule';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public string $selectedDate = '';

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function updatedSelectedDate(): void
    {
    }

    public function getScheduleData(): array
    {
        $courts = Court::where('is_active', true)->orderBy('name')->get();
        $startHour = 10;
        $endHour = 23;

        $timeSlots = [];
        for ($i = $startHour; $i < $endHour; $i++) {
            $timeSlots[] = [
                'start' => sprintf('%02d:00', $i),
                'end' => sprintf('%02d:00', $i + 1),
                'label' => sprintf('%02d:00', $i),
            ];
        }

        $bookedItems = BookingItem::with(['booking.user'])
            ->whereDate('date', $this->selectedDate)
            ->whereHas('booking', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'expired']);
            })
            ->get();

        $grid = [];
        foreach ($courts as $court) {
            $courtRow = [
                'court' => $court,
                'slots' => [],
            ];

            foreach ($timeSlots as $slot) {
                $bookedItem = $bookedItems->first(function ($item) use ($court, $slot) {
                    return $item->court_id === $court->id &&
                        substr($item->start_time, 0, 5) === $slot['start'];
                });

                $courtRow['slots'][] = [
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                    'label' => $slot['label'],
                    'is_booked' => $bookedItem !== null,
                    'user_name' => $bookedItem?->booking?->user?->name ?? null,
                    'booking_status' => $bookedItem?->booking?->status ?? null,
                    'booking_number' => $bookedItem?->booking?->booking_number ?? null,
                ];
            }

            $grid[] = $courtRow;
        }

        return [
            'grid' => $grid,
            'timeSlots' => $timeSlots,
        ];
    }
}
