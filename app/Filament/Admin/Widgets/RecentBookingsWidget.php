<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class RecentBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('booking_number')
                    ->searchable()
                    ->label('Booking No.'),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('THB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Court / Time')
                    ->html() // เพิ่มบรรทัดนี้เพื่อให้ใช้ <br> ได้
                    ->state(function (Booking $record): string {
                        return $record->items->map(function ($item) {
                            $courtName = $item->court?->name ?? 'N/A';
                            
                            // เพิ่มส่วนนี้เพื่อแก้ปัญหาชื่อสนามที่เป็น JSON ["..."]
                            if (str_starts_with($courtName, '[') && str_ends_with($courtName, ']')) {
                                $decoded = json_decode($courtName, true);
                                if (is_array($decoded) && !empty($decoded)) {
                                    $courtName = $decoded[0]; // ดึงชื่อแรกออกมา
                                }
                            }
                            $date = \Carbon\Carbon::parse($item->date)->format('d M Y');
                            $start = \Carbon\Carbon::parse($item->start_time)->format('H:i');
                            $end = \Carbon\Carbon::parse($item->end_time)->format('H:i');
                            return "{$courtName} | {$date} {$start}-{$end}";
                        })->join('<br>'); // เปลี่ยนจาก , เป็น <br> เพื่อขึ้นบรรทัดใหม่
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending', 'pending_payment' => 'gray',
                        'confirmed', 'paid' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        'expired' => 'warning',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Booked At'),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by User'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('date')
                            ->label('Filter by Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', $date),
                            );
                    }),
            ]);
    }
}