<?php

namespace App\Filament\Admin\Resources\OperatingTimes\Pages;

use App\Filament\Admin\Resources\OperatingTimes\OperatingTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOperatingTime extends EditRecord
{
    protected static string $resource = OperatingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
