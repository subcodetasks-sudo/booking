<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel.forms.reservation.section_summary'))
                    ->description(__('panel.forms.reservation.section_summary_description'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('reservation_code')
                            ->label(__('panel.dashboard.table.reservation_code')),
                        TextEntry::make('reservation_date')
                            ->label(__('panel.forms.reservation.reservation_date'))
                            ->date(),
                        TextEntry::make('reservation_time')
                            ->label(__('panel.dashboard.table.reservation_time'))
                            ->time('H:i'),
                        TextEntry::make('guest_count')
                            ->label(__('panel.dashboard.table.guest_count')),
                        TextEntry::make('status')
                            ->label(__('panel.dashboard.table.status'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __("panel.statuses.reservation.{$state}"))
                            ->color(fn (string $state): string => match ($state) {
                                'confirmed' => 'success',
                                'cancelled' => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('order_status')
                            ->label(__('panel.dashboard.table.order_status'))
                            ->formatStateUsing(fn (string $state): string => __("panel.statuses.order.{$state}"))
                            ->badge()
                            ->color('gray'),
                    ]),
                Section::make(__('panel.forms.reservation.section_customer'))
                    ->columns(2)
                    ->components([
                        TextEntry::make('customer_name')
                            ->label(__('panel.dashboard.table.customer_name')),
                        TextEntry::make('customer_phone')
                            ->label(__('panel.dashboard.table.customer_phone')),
                        TextEntry::make('customer_email')
                            ->label(__('panel.forms.reservation.customer_email'))
                            ->placeholder('—'),
                    ]),
                Section::make(__('panel.forms.reservation.section_occasion_notes'))
                    ->columns(1)
                    ->components([
                        TextEntry::make('occasion.name_ar')
                            ->label(__('panel.resources.occasion.singular'))
                            ->placeholder('—'),
                        RepeatableEntry::make('addons')
                            ->label(__('panel.forms.reservation.addons_line_items'))
                            ->placeholder(__('panel.forms.reservation.no_addons'))
                            ->schema([
                                TextEntry::make('pivot.addon_name')
                                    ->label(__('panel.forms.reservation.addon_name')),
                                TextEntry::make('pivot.quantity')
                                    ->label(__('panel.forms.reservation.quantity')),
                                TextEntry::make('pivot.line_total')
                                    ->label(__('panel.forms.reservation.line_total'))
                                    ->money('AED'),
                            ])
                            ->columns(3),
                        TextEntry::make('allergies_notes')
                            ->label(__('panel.forms.reservation.allergies_notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('reservation_notes')
                            ->label(__('panel.forms.reservation.reservation_notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('panel.forms.reservation.section_amounts'))
                    ->columns(3)
                    ->components([
                        TextEntry::make('addons_total')
                            ->label(__('panel.forms.reservation.addons_total'))
                            ->money('AED'),
                        TextEntry::make('items_total')
                            ->label(__('panel.forms.reservation.items_total'))
                            ->money('AED'),
                        TextEntry::make('total_amount')
                            ->label(__('panel.dashboard.table.total_amount'))
                            ->money('AED'),
                    ]),
                Section::make(__('panel.forms.reservation.section_audit'))
                    ->columns(2)
                    ->collapsed()
                    ->components([
                        TextEntry::make('confirmed_at')
                            ->label(__('panel.forms.reservation.confirmed_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('cancelled_at')
                            ->label(__('panel.forms.reservation.cancelled_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('cancel_reason')
                            ->label(__('panel.forms.reservation.cancel_reason'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label(__('panel.dashboard.table.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('panel.forms.reservation.updated_at'))
                            ->dateTime(),
                    ]),
            ]);
    }
}
