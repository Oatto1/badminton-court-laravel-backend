<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Recent Bookings';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::with(['user', 'items.court'])->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Booked By')
                    ->searchable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Court / Time')
                    ->state(function (Booking $record): string {
                        return $record->items->map(function ($item) {
                            $court = $item->court?->name ?? 'N/A';
                            $date = \Carbon\Carbon::parse($item->date)->format('d M Y');
                            $start = \Carbon\Carbon::parse($item->start_time)->format('H:i');
                            $end = \Carbon\Carbon::parse($item->end_time)->format('H:i');

                            return $court . ' | ' . $date . ' ' . $start . '-' . $end;
                        })->join(', ');
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Amount')
                    ->money('THB'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                        ->color(function ($state) {
                            return match ($state) {
                                'pending_payment' => 'warning',
                                'confirmed' => 'success',
                                'cancelled' => 'danger',
                                'expired' => 'gray',
                                default => 'secondary',
                            };
                        }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 20]);
    }
}
