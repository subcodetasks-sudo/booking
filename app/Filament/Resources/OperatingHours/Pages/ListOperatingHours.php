<?php

namespace App\Filament\Resources\OperatingHours\Pages;

use App\Filament\Resources\OperatingHours\OperatingHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOperatingHours extends ListRecords
{
    protected static string $resource = OperatingHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
