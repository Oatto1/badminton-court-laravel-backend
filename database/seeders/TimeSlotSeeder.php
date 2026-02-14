<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $startTime = Carbon::createFromTime(9, 0, 0);
        $endTime = Carbon::createFromTime(22, 0, 0);

        while ($startTime->lt($endTime)) {
            $slotEnd = $startTime->copy()->addHour();

            // Peak pricing logic: 17:00 - 21:00
            $isPeak = $startTime->hour >= 17 && $startTime->hour < 21;
            $modifier = $isPeak ? 1.5 : 1.0;

            TimeSlot::firstOrCreate(
            [
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s'),
            ],
            [
                'is_peak' => $isPeak,
                'price_modifier' => $modifier,
            ]
            );

            $startTime->addHour();
        }
    }
}
