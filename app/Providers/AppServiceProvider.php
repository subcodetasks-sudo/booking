<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use App\Models\Reservation;
use App\Observers\ReservationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TextInput::configureUsing(fn (TextInput $component) => $component->translateLabel());
        Textarea::configureUsing(fn (Textarea $component) => $component->translateLabel());
        Select::configureUsing(fn (Select $component) => $component->translateLabel());
        Toggle::configureUsing(fn (Toggle $component) => $component->translateLabel());
        FileUpload::configureUsing(fn (FileUpload $component) => $component->translateLabel());
        DatePicker::configureUsing(fn (DatePicker $component) => $component->translateLabel());
        DateTimePicker::configureUsing(fn (DateTimePicker $component) => $component->translateLabel());
        TimePicker::configureUsing(fn (TimePicker $component) => $component->translateLabel());

        TextColumn::configureUsing(fn (TextColumn $component) => $component->translateLabel());
        IconColumn::configureUsing(fn (IconColumn $component) => $component->translateLabel());
        ImageColumn::configureUsing(fn (ImageColumn $component) => $component->translateLabel());

        Reservation::observe(ReservationObserver::class);
    }
}
