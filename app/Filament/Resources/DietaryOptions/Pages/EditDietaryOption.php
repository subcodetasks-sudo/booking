<?php

namespace App\Filament\Resources\DietaryOptions\Pages;

use App\Filament\Resources\DietaryOptions\DietaryOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDietaryOption extends EditRecord
{
    protected static string $resource = DietaryOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

