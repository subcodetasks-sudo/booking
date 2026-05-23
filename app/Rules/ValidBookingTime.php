<?php

namespace App\Rules;

use App\Support\BookingWindow;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidBookingTime implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (BookingWindow::normalize($value) === null) {
            $fail(__('panel.pages.booking_time_invalid'));
        }
    }
}
