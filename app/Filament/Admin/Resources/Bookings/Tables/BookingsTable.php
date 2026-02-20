<?php

namespace App\Filament\Admin\Resources\Bookings\Tables;

use App\Models\Booking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('booking_number')
                    ->searchable(),
                TextColumn::make('items_summary')
                    ->label('Court / Time')
                    ->html()
                    ->state(function (Booking $record): string {
                        return $record->items->map(function ($item) {
                            $courtName = $item->court?->name ?? 'N/A';
                            
                            // Decode if existing name is a JSON string (e.g. ["Court 1"])
                            if (str_starts_with($courtName, '[') && str_ends_with($courtName, ']')) {
                                $decoded = json_decode($courtName, true);
                                if (is_array($decoded) && !empty($decoded)) {
                                    $courtName = $decoded[0];
                                }
                            }

                            $date = \Carbon\Carbon::parse($item->date)->format('d M Y');
                            $start = \Carbon\Carbon::parse($item->start_time)->format('H:i');
                            $end = \Carbon\Carbon::parse($item->end_time)->format('H:i');

                            return "{$courtName} | {$date} {$start}-{$end}";
                        })->join('<br>');
                    })
                    ->wrap(),
                TextColumn::make('total_price')
                    ->money()
                    ->sortable(),
                TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                        'pending', 'pending_payment' => 'gray',
                        'confirmed', 'paid' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        'expired' => 'warning',
                        default => 'warning',
                    }),
                // TextColumn::make('expires_at')
                //     ->dateTime()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
