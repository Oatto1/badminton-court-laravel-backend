<?php

namespace App\Filament\Admin\Resources\OperatingTimes\Pages;

use App\Filament\Admin\Resources\OperatingTimes\OperatingTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOperatingTimes extends ListRecords
{
    protected static string $resource = OperatingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
