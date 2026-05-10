<?php

namespace App\Filament\Resources\TimeSlots;

use App\Filament\Resources\TimeSlots\Pages\CreateTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\EditTimeSlot;
use App\Filament\Resources\TimeSlots\Pages\ListTimeSlots;
use App\Filament\Resources\TimeSlots\Schemas\TimeSlotForm;
use App\Filament\Resources\TimeSlots\Tables\TimeSlotsTable;
use App\Models\TimeSlot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimeSlotResource extends Resource
{
    protected static ?string $model = TimeSlot::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.scheduling');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.time_slot.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.time_slot.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.time_slot.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return TimeSlotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeSlotsTable::configure($table);
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
            'index' => ListTimeSlots::route('/'),
            'create' => CreateTimeSlot::route('/create'),
            'edit' => EditTimeSlot::route('/{record}/edit'),
        ];
    }
}
