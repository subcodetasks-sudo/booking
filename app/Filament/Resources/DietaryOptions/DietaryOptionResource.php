<?php

namespace App\Filament\Resources\DietaryOptions;

use App\Filament\Resources\DietaryOptions\Pages\CreateDietaryOption;
use App\Filament\Resources\DietaryOptions\Pages\EditDietaryOption;
use App\Filament\Resources\DietaryOptions\Pages\ListDietaryOptions;
use App\Filament\Resources\DietaryOptions\Schemas\DietaryOptionForm;
use App\Filament\Resources\DietaryOptions\Tables\DietaryOptionsTable;
use App\Models\DietaryOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DietaryOptionResource extends Resource
{
    protected static ?string $model = DietaryOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.reservations');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.dietary_option.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.dietary_option.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.dietary_option.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return DietaryOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DietaryOptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDietaryOptions::route('/'),
            'create' => CreateDietaryOption::route('/create'),
            'edit' => EditDietaryOption::route('/{record}/edit'),
        ];
    }
}

