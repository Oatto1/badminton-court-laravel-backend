<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $paidBookings = Booking::where('status', 'confirmed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');

        return [
            Stat::make('Total Bookings', number_format($totalBookings))
                ->description('All bookings')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Paid Bookings', number_format($paidBookings))
                ->description(
                    $totalBookings > 0
                        ? round(($paidBookings / $totalBookings) * 100, 1) . '% of total'
                        : '0% of total'
                )
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Cancelled Bookings', number_format($cancelledBookings))
                ->description(
                    $totalBookings > 0
                        ? round(($cancelledBookings / $totalBookings) * 100, 1) . '% of total'
                        : '0% of total'
                )
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Total Revenue', '฿' . number_format($totalRevenue, 2))
                ->description('From paid bookings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
