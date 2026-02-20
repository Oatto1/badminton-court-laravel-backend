<?php

namespace App\Filament\Admin\Resources\OperatingTimes;

use App\Filament\Admin\Resources\OperatingTimes\Pages\CreateOperatingTime;
use App\Filament\Admin\Resources\OperatingTimes\Pages\EditOperatingTime;
use App\Filament\Admin\Resources\OperatingTimes\Pages\ListOperatingTimes;
use App\Filament\Admin\Resources\OperatingTimes\Schemas\OperatingTimeForm;
use App\Filament\Admin\Resources\OperatingTimes\Tables\OperatingTimesTable;
use App\Models\OperatingTime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OperatingTimeResource extends Resource
{
    protected static ?string $model = OperatingTime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OperatingTimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperatingTimesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperatingTimes::route('/'),
            'create' => CreateOperatingTime::route('/create'),
            'edit' => EditOperatingTime::route('/{record}/edit'),
        ];
    }
}
