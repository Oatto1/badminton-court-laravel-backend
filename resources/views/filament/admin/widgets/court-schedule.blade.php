<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <span style="font-size:1.1rem;font-weight:700;">🏸 Court Schedule</span>
                <input
                    type="date"
                    wire:model.live="selectedDate"
                    style="border:1px solid #d1d5db;border-radius:8px;padding:6px 12px;font-size:0.85rem;"
                />
            </div>
        </x-slot>

        @php
            $data = $this->getScheduleData();
            $grid = $data['grid'];
            $timeSlots = $data['timeSlots'];
        @endphp

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.8rem;table-layout:fixed;">
                <thead>
                    <tr>
                        <th style="border:1px solid #e5e7eb;padding:10px 12px;background:#f3f4f6;text-align:left;font-weight:700;width:100px;position:sticky;left:0;z-index:1;">
                            Court
                        </th>
                        @foreach ($timeSlots as $slot)
                            <th style="border:1px solid #e5e7eb;padding:8px 4px;background:#f3f4f6;text-align:center;font-weight:600;min-width:75px;">
                                {{ $slot['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grid as $row)
                        <tr>
                            <td style="border:1px solid #e5e7eb;padding:10px 12px;font-weight:700;background:#fff;white-space:nowrap;position:sticky;left:0;z-index:1;">
                                {{ $row['court']->name }}
                            </td>
                            @foreach ($row['slots'] as $slot)
                                @if ($slot['is_booked'])
                                    <td style="border:1px solid #e5e7eb;padding:6px 4px;text-align:center;background:#fee2e2;cursor:default;" title="Booking: {{ $slot['booking_number'] }}">
                                        <div style="font-size:0.7rem;font-weight:600;color:#b91c1c;line-height:1.3;">
                                            {{ $slot['user_name'] ?? 'Booked' }}
                                        </div>
                                        <div style="font-size:0.6rem;color:#ef4444;margin-top:2px;">
                                            {{ $slot['booking_status'] }}
                                        </div>
                                    </td>
                                @else
                                    <td style="border:1px solid #e5e7eb;padding:6px 4px;text-align:center;background:#f0fdf4;">
                                        <div style="font-size:0.65rem;color:#16a34a;">✓</div>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:20px;margin-top:12px;font-size:0.8rem;color:#6b7280;">
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="display:inline-block;width:16px;height:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:3px;"></span>
                Available
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span style="display:inline-block;width:16px;height:16px;background:#fee2e2;border:1px solid #fecaca;border-radius:3px;"></span>
                Booked
            </div>
        </div>

        @if (empty($grid))
            <div style="text-align:center;color:#9ca3af;padding:32px 0;">No courts found.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
