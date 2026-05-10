<?php

namespace App\Filament\Resources\TimeSlots\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeSlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('slot_date')
                    ->required(),
                TimePicker::make('start_time')
                    ->required(),
                TimePicker::make('end_time')
                    ->required(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                TextInput::make('reserved_guests')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('held_guests')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_closed_manually')
                    ->required(),
            ]);
    }
}
