<?php

namespace App\Filament\Resources\Occasions;

use App\Filament\Resources\Occasions\Pages\CreateOccasion;
use App\Filament\Resources\Occasions\Pages\EditOccasion;
use App\Filament\Resources\Occasions\Pages\ListOccasions;
use App\Filament\Resources\Occasions\Schemas\OccasionForm;
use App\Filament\Resources\Occasions\Tables\OccasionsTable;
use App\Models\Occasion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OccasionResource extends Resource
{
    protected static ?string $model = Occasion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.reservations');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.occasion.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.occasion.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.occasion.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return OccasionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OccasionsTable::configure($table);
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
            'index' => ListOccasions::route('/'),
            'create' => CreateOccasion::route('/create'),
            'edit' => EditOccasion::route('/{record}/edit'),
        ];
    }
}
