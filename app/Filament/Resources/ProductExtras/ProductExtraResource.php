<?php

namespace App\Filament\Resources\ProductExtras;

use App\Filament\Resources\ProductExtras\Pages\CreateProductExtra;
use App\Filament\Resources\ProductExtras\Pages\EditProductExtra;
use App\Filament\Resources\ProductExtras\Pages\ListProductExtras;
use App\Filament\Resources\ProductExtras\Schemas\ProductExtraForm;
use App\Filament\Resources\ProductExtras\Tables\ProductExtrasTable;
use App\Models\ProductExtra;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductExtraResource extends Resource
{
    protected static ?string $model = ProductExtra::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.menu');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.product_extra.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.product_extra.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.product_extra.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductExtraForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductExtrasTable::configure($table);
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
            'index' => ListProductExtras::route('/'),
            'create' => CreateProductExtra::route('/create'),
            'edit' => EditProductExtra::route('/{record}/edit'),
        ];
    }
}
