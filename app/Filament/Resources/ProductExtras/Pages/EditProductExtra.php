<?php

namespace App\Filament\Resources\ProductExtras\Pages;

use App\Filament\Resources\ProductExtras\ProductExtraResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductExtra extends EditRecord
{
    protected static string $resource = ProductExtraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
