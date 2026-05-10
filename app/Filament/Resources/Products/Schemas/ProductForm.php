<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship(
                        'category',
                        'name_ar',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('id'),
                    )
                    ->getOptionLabelFromRecordUsing(static function (Category $record): string {
                        if (app()->getLocale() === 'ar') {
                            return filled($record->name_ar)
                                ? (string) $record->name_ar
                                : (string) ($record->name_en ?? '');
                        }

                        return filled($record->name_en)
                            ? (string) $record->name_en
                            : (string) ($record->name_ar ?? '');
                    })
                    ->searchable(['name_ar', 'name_en'])
                    ->preload()
                    ->required(),
                TextInput::make('name_ar')
                    ->required(),
                TextInput::make('name_en')
                    ->required(),
                Textarea::make('description_ar')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('description_en')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('ingredients_ar')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('ingredients_en')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
