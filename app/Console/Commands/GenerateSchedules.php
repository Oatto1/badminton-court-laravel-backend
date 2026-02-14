<?php

namespace App\Console\Commands;

use App\Models\Court;
use App\Models\CourtSchedule;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateSchedules extends Command
{
    protected $signature = 'schedule:generate {days=30}';
    protected $description = 'Generate court schedules for the next X days';

    public function handle()
    {
        $days = (int)$this->argument('days');
        $courts = Court::all();
        $timeSlots = TimeSlot::all();

        if ($courts->isEmpty() || $timeSlots->isEmpty()) {
            $this->error('No courts or time slots found. Please seed them first.');
            return;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($days);
        $count = 0;

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            foreach ($courts as $court) {
                // If court is maintenance, maybe skip? Or just mark as maintenance?
                // Let's generate as available, and admin can change to maintenance.
                // Or if court status is maintenance, generate as maintenance?
                // Better to generate based on defaults.

                foreach ($timeSlots as $slot) {
                    $scheduleDate = $date->format('Y-m-d');

                    // Check if exists
                    $exists = CourtSchedule::where('court_id', $court->id)
                        ->where('schedule_date', $scheduleDate)
                        ->where('time_slot_id', $slot->id)
                        ->exists();

                    if (!$exists) {
                        // Calculate price
                        $price = $court->hourly_price * $slot->price_modifier;
                        // TODO: Apply peak pricing logic properly if needed

                        CourtSchedule::create([
                            'court_id' => $court->id,
                            'schedule_date' => $scheduleDate,
                            'time_slot_id' => $slot->id,
                            'status' => 'available',
                            'price' => $price,
                        ]);
                        $count++;
                    }
                }
            }
        }

        $this->info("Generated {$count} schedule slots.");
    }
}
