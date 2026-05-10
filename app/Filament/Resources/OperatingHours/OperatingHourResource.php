<?php

namespace App\Filament\Resources\OperatingHours;

use App\Filament\Resources\OperatingHours\Pages\CreateOperatingHour;
use App\Filament\Resources\OperatingHours\Pages\EditOperatingHour;
use App\Filament\Resources\OperatingHours\Pages\ListOperatingHours;
use App\Filament\Resources\OperatingHours\Schemas\OperatingHourForm;
use App\Filament\Resources\OperatingHours\Tables\OperatingHoursTable;
use App\Models\OperatingHour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OperatingHourResource extends Resource
{
    protected static ?string $model = OperatingHour::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.scheduling');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.operating_hour.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.operating_hour.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.operating_hour.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return OperatingHourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperatingHoursTable::configure($table);
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
            'index' => ListOperatingHours::route('/'),
            'create' => CreateOperatingHour::route('/create'),
            'edit' => EditOperatingHour::route('/{record}/edit'),
        ];
    }
}
