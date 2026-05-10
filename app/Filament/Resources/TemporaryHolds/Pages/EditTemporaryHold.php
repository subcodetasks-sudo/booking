<?php

namespace App\Filament\Resources\TemporaryHolds\Pages;

use App\Filament\Resources\TemporaryHolds\TemporaryHoldResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTemporaryHold extends EditRecord
{
    protected static string $resource = TemporaryHoldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
