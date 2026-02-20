<?php

namespace App\Filament\Admin\Resources\Bookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Select::make('user_id')
            ->relationship('user', 'name')
            ->required(),
            TextInput::make('booking_number')
            ->required(),
            TextInput::make('total_price')
            ->required()
            ->numeric()
            ->prefix('$'),
            Select::make('status')
            ->options([
                'pending' => 'Pending',
                'paid' => 'Paid',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'expired' => 'Expired',
            ])
            ->required()
            ->default('pending_payment'),
            DateTimePicker::make('expires_at'),
        ]);
    }
}
