<?php

namespace App\Actions;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\SyncFilamentDatabaseNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

final class NotifyPanelUsersOfNewReservation
{
    public static function send(Reservation $reservation): void
    {
        try {
            $users = User::query()->get();
            if ($users->isEmpty()) {
                return;
            }

            $editUrl = ReservationResource::getUrl(
                'edit',
                ['record' => $reservation],
                isAbsolute: true,
                panel: 'admin',
            );

            $payload = FilamentNotification::make()
                ->title(__('panel.notifications.new_booking_title'))
                ->body(__('panel.notifications.new_booking_body', [
                    'code' => $reservation->reservation_code,
                    'name' => $reservation->customer_name,
                    'time' => substr((string) $reservation->reservation_time, 0, 5),
                    'date' => $reservation->reservation_date->format('Y-m-d'),
                ]))
                ->icon('heroicon-o-clipboard-document-list')
                ->actions([
                    Action::make('view')
                        ->label(__('panel.notifications.view_booking'))
                        ->url($editUrl),
                ])
                ->getDatabaseMessage();

            Notification::send($users, new SyncFilamentDatabaseNotification($payload));
        } catch (Throwable $e) {
            Log::error('Failed to send Filament notification for new reservation.', [
                'reservation_id' => $reservation->id,
                'exception' => $e,
            ]);
        }
    }
}
