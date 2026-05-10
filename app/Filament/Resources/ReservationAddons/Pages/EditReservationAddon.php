<?php

namespace App\Filament\Resources\ReservationAddons\Pages;

use App\Filament\Resources\ReservationAddons\ReservationAddonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReservationAddon extends EditRecord
{
    protected static string $resource = ReservationAddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
