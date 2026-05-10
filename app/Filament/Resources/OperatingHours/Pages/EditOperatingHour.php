<?php

namespace App\Filament\Resources\OperatingHours\Pages;

use App\Filament\Resources\OperatingHours\OperatingHourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOperatingHour extends EditRecord
{
    protected static string $resource = OperatingHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
