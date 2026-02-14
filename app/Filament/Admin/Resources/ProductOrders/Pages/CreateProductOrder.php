<?php

namespace App\Filament\Admin\Resources\ProductOrders\Pages;

use App\Filament\Admin\Resources\ProductOrders\ProductOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductOrder extends CreateRecord
{
    protected static string $resource = ProductOrderResource::class;
}
