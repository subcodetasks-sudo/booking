<?php

namespace App\Filament\Resources\DietaryOptions\Pages;

use App\Filament\Resources\DietaryOptions\DietaryOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDietaryOptions extends ListRecords
{
    protected static string $resource = DietaryOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

