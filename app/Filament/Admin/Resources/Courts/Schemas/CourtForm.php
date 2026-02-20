<?php

namespace App\Filament\Admin\Resources\Courts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            TextInput::make('name')
            ->required(),
            TextInput::make('price_per_hour')
            ->required()
            ->numeric()
            ->default(210),
            Toggle::make('is_active')
            ->required(),
        ]);
    }
}
