<?php

namespace App\Filament\Resources\TemporaryHolds;

use App\Filament\Resources\TemporaryHolds\Pages\CreateTemporaryHold;
use App\Filament\Resources\TemporaryHolds\Pages\EditTemporaryHold;
use App\Filament\Resources\TemporaryHolds\Pages\ListTemporaryHolds;
use App\Filament\Resources\TemporaryHolds\Schemas\TemporaryHoldForm;
use App\Filament\Resources\TemporaryHolds\Tables\TemporaryHoldsTable;
use App\Models\TemporaryHold;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TemporaryHoldResource extends Resource
{
    protected static ?string $model = TemporaryHold::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.scheduling');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.temporary_hold.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.temporary_hold.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.temporary_hold.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return TemporaryHoldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemporaryHoldsTable::configure($table);
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
            'index' => ListTemporaryHolds::route('/'),
            'create' => CreateTemporaryHold::route('/create'),
            'edit' => EditTemporaryHold::route('/{record}/edit'),
        ];
    }
}
