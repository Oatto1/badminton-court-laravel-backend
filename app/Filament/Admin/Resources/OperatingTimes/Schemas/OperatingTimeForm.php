<?php

namespace App\Filament\Admin\Resources\OperatingTimes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;

class OperatingTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day')
                    ->options([
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
                        'Sunday' => 'Sunday',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true),
                TimePicker::make('open_time')
                    ->required()
                    ->default('10:00')
                    ->seconds(false),
                TimePicker::make('close_time')
                    ->required()
                    ->default('00:00') // Midnight
                    ->seconds(false),
            ]);
    }
}