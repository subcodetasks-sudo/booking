<?php

namespace App\Filament\Resources\ReservationAddons\Pages;

use App\Filament\Resources\ReservationAddons\ReservationAddonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReservationAddons extends ListRecords
{
    protected static string $resource = ReservationAddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
