<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    public function defaultInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->record($this->getRecord());
    }

    public function infolist(Schema $schema): Schema
    {
        return static::getResource()::infolist($schema);
    }

    public function content(Schema $schema): Schema
    {
        if ($this->hasCombinedRelationManagerTabsWithContent()) {
            return parent::content($schema);
        }

        return $schema
            ->components([
                EmbeddedSchema::make('infolist'),
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
