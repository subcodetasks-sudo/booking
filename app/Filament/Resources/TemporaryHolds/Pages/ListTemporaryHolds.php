<?php

namespace App\Filament\Resources\TemporaryHolds\Pages;

use App\Filament\Resources\TemporaryHolds\TemporaryHoldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTemporaryHolds extends ListRecords
{
    protected static string $resource = TemporaryHoldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
