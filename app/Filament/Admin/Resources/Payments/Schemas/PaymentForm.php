<?php

namespace App\Filament\Admin\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            Select::make('booking_id')
            ->relationship('booking', 'id')
            ->required(),
            Select::make('user_id')
            ->relationship('user', 'name')
            ->required(),
            TextInput::make('amount')
            ->required()
            ->numeric(),
            Select::make('method')
            ->options([
                'promptpay' => 'PromptPay',
                'cash' => 'Cash',
                'transfer' => 'Bank Transfer',
            ])
            ->required()
            ->default('promptpay'),
            Select::make('status')
            ->options([
                'pending' => 'Pending',
                'paid' => 'Paid',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
            ])
            ->required()
            ->default('pending'),
            TextInput::make('transaction_ref'),
        ]);
    }
}
