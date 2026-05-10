<?php

namespace App\Filament\Resources\DietaryOptions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DietaryOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label(__('panel.forms.dietary_option.key'))
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true)
                ->helperText(__('panel.forms.dietary_option.key_help')),
            TextInput::make('name_ar')
                ->label(__('panel.forms.dietary_option.name_ar'))
                ->required()
                ->maxLength(255),
            TextInput::make('name_en')
                ->label(__('panel.forms.dietary_option.name_en'))
                ->required()
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label(__('panel.forms.dietary_option.sort_order'))
                ->required()
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->label(__('panel.forms.dietary_option.is_active'))
                ->required()
                ->default(true),
        ]);
    }
}

