<?php

namespace App\Filament\Admin\Resources\ProductOrders;

use App\Filament\Admin\Resources\ProductOrders\Pages\CreateProductOrder;
use App\Filament\Admin\Resources\ProductOrders\Pages\EditProductOrder;
use App\Filament\Admin\Resources\ProductOrders\Pages\ListProductOrders;
use App\Filament\Admin\Resources\ProductOrders\Schemas\ProductOrderForm;
use App\Filament\Admin\Resources\ProductOrders\Tables\ProductOrdersTable;
use App\Models\ProductOrder;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductOrderResource extends Resource
{
    protected static ?string $model = ProductOrder::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Product & Order Management';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ProductOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductOrdersTable::configure($table);
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
            'index' => ListProductOrders::route('/'),
            'create' => CreateProductOrder::route('/create'),
            'edit' => EditProductOrder::route('/{record}/edit'),
        ];
    }
}
