<?php

namespace App\Filament\Admin\Resources\ProductOrders\Pages;

use App\Filament\Admin\Resources\ProductOrders\ProductOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductOrder extends EditRecord
{
    protected static string $resource = ProductOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
