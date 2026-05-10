<?php

namespace App\Filament\Resources\ProductExtras\Pages;

use App\Filament\Resources\ProductExtras\ProductExtraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductExtras extends ListRecords
{
    protected static string $resource = ProductExtraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
