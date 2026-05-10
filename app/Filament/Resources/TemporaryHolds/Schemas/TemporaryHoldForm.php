<?php

namespace App\Filament\Resources\TemporaryHolds\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class TemporaryHoldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('time_slot_id')
                    ->relationship('timeSlot', 'id')
                    ->required(),
                DatePicker::make('reservation_date')
                    ->required(),
                TimePicker::make('reservation_time')
                    ->required(),
                TextInput::make('guest_count')
                    ->required()
                    ->numeric(),
                TextInput::make('session_key')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->required(),
            ]);
    }
}
