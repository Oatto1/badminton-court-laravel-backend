<?php

namespace App\Filament\Admin\Resources\Members\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('points')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('wallet_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
