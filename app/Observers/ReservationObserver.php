<?php

namespace App\Observers;

use App\Actions\NotifyPanelUsersOfNewReservation;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        DB::afterCommit(function () use ($reservation): void {
            NotifyPanelUsersOfNewReservation::send($reservation);
        });
    }
}
