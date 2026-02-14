<?php

namespace App\Filament\Admin\Resources\Courts\Pages;

use App\Filament\Admin\Resources\Courts\CourtResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourt extends EditRecord
{
    protected static string $resource = CourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
