<?php

namespace App\Filament\Resources\Occasions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OccasionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->label(__('panel.forms.occasion.name_ar'))
                    ->required(),
                TextInput::make('name_en')
                    ->label(__('panel.forms.occasion.name_en'))
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('panel.forms.occasion.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
