<?php

namespace App\Filament\Resources\ProductExtras\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductExtraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
                TextInput::make('name_ar')
                    ->required(),
                TextInput::make('name_en')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
