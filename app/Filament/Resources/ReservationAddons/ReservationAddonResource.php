<?php

namespace App\Filament\Resources\ReservationAddons;

use App\Filament\Resources\ReservationAddons\Pages\CreateReservationAddon;
use App\Filament\Resources\ReservationAddons\Pages\EditReservationAddon;
use App\Filament\Resources\ReservationAddons\Pages\ListReservationAddons;
use App\Filament\Resources\ReservationAddons\Schemas\ReservationAddonForm;
use App\Filament\Resources\ReservationAddons\Tables\ReservationAddonsTable;
use App\Models\ReservationAddon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReservationAddonResource extends Resource
{
    protected static ?string $model = ReservationAddon::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.reservations');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.reservation_addon.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.reservation_addon.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.reservation_addon.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ReservationAddonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationAddonsTable::configure($table);
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
            'index' => ListReservationAddons::route('/'),
            'create' => CreateReservationAddon::route('/create'),
            'edit' => EditReservationAddon::route('/{record}/edit'),
        ];
    }
}
