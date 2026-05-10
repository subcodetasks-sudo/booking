<?php

namespace App\Filament\Resources\OperatingHours\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OperatingHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('day_of_week')
                    ->required()
                    ->numeric(),
                TimePicker::make('open_time')
                    ->required(),
                TimePicker::make('close_time')
                    ->required(),
                TextInput::make('slot_duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('capacity_per_slot')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_open')
                    ->required(),
            ]);
    }
}
