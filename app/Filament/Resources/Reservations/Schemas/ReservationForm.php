<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel.forms.reservation.section_booking'))
                    ->columns(2)
                    ->components([
                        TextInput::make('reservation_code')
                            ->required(),
                        DatePicker::make('reservation_date')
                            ->required(),
                        TimePicker::make('reservation_time')
                            ->required(),
                        TextInput::make('guest_count')
                            ->required()
                            ->numeric(),
                        Select::make('status')
                            ->label(__('panel.dashboard.table.status'))
                            ->required()
                            ->default('pending')
                            ->options([
                                'pending' => __('panel.statuses.reservation.pending'),
                                'confirmed' => __('panel.statuses.reservation.confirmed'),
                                'cancelled' => __('panel.statuses.reservation.cancelled'),
                            ])
                            ->native(false),
                        TextInput::make('order_status')
                            ->required()
                            ->default('no_order'),
                    ]),
                Section::make(__('panel.forms.reservation.section_customer'))
                    ->columns(2)
                    ->components([
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('customer_phone')
                            ->tel()
                            ->required(),
                        TextInput::make('customer_email')
                            ->email()
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('panel.forms.reservation.section_occasion_notes'))
                    ->columns(1)
                    ->components([
                        Select::make('occasion_id')
                            ->relationship(
                                'occasion',
                                'name_ar',
                                fn ($query) => $query->orderBy('name_ar'),
                            )
                            ->searchable()
                            ->preload()
                            ->default(null),
                        Textarea::make('allergies_notes')
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('reservation_notes')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('panel.forms.reservation.section_amounts'))
                    ->description(__('panel.forms.reservation.section_amounts_description'))
                    ->columns(3)
                    ->components([
                        TextInput::make('addons_total')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('SAR'),
                        TextInput::make('items_total')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('SAR'),
                        TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->default(0.0)
                            ->prefix('SAR'),
                    ]),
                Section::make(__('panel.forms.reservation.section_status_meta'))
                    ->columns(2)
                    ->collapsed()
                    ->components([
                        DateTimePicker::make('confirmed_at'),
                        DateTimePicker::make('cancelled_at'),
                        TextInput::make('cancel_reason')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
