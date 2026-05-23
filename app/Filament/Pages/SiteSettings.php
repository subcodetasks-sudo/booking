<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Rules\ValidBookingTime;
use App\Support\BookingWindow;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettings extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('panel.navigation.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.pages.site_settings');
    }

    public function getTitle(): string
    {
        return __('panel.pages.site_settings');
    }

    public function mount(): void
    {
        $this->form->fill([
            'home_h1_text' => SiteSetting::getValue('home_h1_text', 'احجز طاولتك بسهولة'),
            'home_h1_text_en' => SiteSetting::getValue('home_h1_text_en', 'Book your table easily'),
            'home_h1_color' => SiteSetting::getValue('home_h1_color', '#5b4a36'),
            'booking_start_time' => BookingWindow::normalize(SiteSetting::getValue('booking_start_time', BookingWindow::DEFAULT_START))
                ?? BookingWindow::DEFAULT_START,
            'booking_end_time' => BookingWindow::normalize(SiteSetting::getValue('booking_end_time', BookingWindow::DEFAULT_END))
                ?? BookingWindow::DEFAULT_END,
            'booking_is_active' => (bool) SiteSetting::getValue('booking_is_active', true),
            'max_guest_count' => (int) SiteSetting::getValue('max_guest_count', 20),
            'booking_tables_per_hour' => (int) SiteSetting::getValue('booking_tables_per_hour', 1),
            'booking_max_reservations_per_day' => SiteSetting::getValue('booking_max_reservations_per_day'),
            'booking_whatsapp_phone' => SiteSetting::getValue('booking_whatsapp_phone', '905528255694'),
            'social_instagram_url' => SiteSetting::getValue('social_instagram_url', 'https://www.instagram.com/'),
            'social_facebook_url' => SiteSetting::getValue('social_facebook_url', 'https://www.facebook.com/'),
            'social_tiktok_url' => SiteSetting::getValue('social_tiktok_url', 'https://www.tiktok.com/'),
            'social_whatsapp_url' => SiteSetting::getValue('social_whatsapp_url', 'https://wa.me/905528255694'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel.pages.homepage') ?? 'Homepage')
                    ->components([
                        TextInput::make('home_h1_text')
                            ->label(__('panel.pages.home_h1_text'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('home_h1_text_en')
                            ->label(__('panel.pages.home_h1_text_en'))
                            ->required()
                            ->maxLength(255),
                        ColorPicker::make('home_h1_color')
                            ->label(__('panel.pages.home_h1_color')),
                    ])
                    ->columns(3),
                Section::make(__('panel.pages.booking_hours'))
                    ->description(__('panel.pages.booking_hours_description'))
                    ->components([
                        TextInput::make('booking_start_time')
                            ->label(__('panel.pages.booking_start_time'))
                            ->required()
                            ->placeholder('04:00')
                            ->helperText(__('panel.pages.booking_time_format_help').' '.__('panel.pages.booking_time_all_days_help'))
                            ->rule(new ValidBookingTime()),
                        TextInput::make('booking_end_time')
                            ->label(__('panel.pages.booking_end_time'))
                            ->required()
                            ->placeholder('12:00')
                            ->helperText(__('panel.pages.booking_time_format_help').' '.__('panel.pages.booking_time_all_days_help'))
                            ->rule(new ValidBookingTime()),
                        Toggle::make('booking_is_active')
                            ->label(__('panel.pages.booking_is_active'))
                            ->default(true)
                            ->required(),
                        TextInput::make('max_guest_count')
                            ->label(__('panel.pages.max_guest_count'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500)
                            ->default(20),
                        TextInput::make('booking_tables_per_hour')
                            ->label(__('panel.pages.booking_tables_per_hour'))
                            ->helperText(__('panel.pages.booking_tables_per_hour_description'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99)
                            ->default(1),
                        TextInput::make('booking_max_reservations_per_day')
                            ->label(__('panel.pages.booking_max_reservations_per_day'))
                            ->helperText(__('panel.pages.booking_max_reservations_per_day_description'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10000),
                        TextInput::make('booking_whatsapp_phone')
                            ->label(__('panel.pages.booking_whatsapp_phone'))
                            ->helperText(__('panel.pages.booking_whatsapp_help'))
                            ->required()
                            ->maxLength(20)
                            ->placeholder('966508891883'),
                    ])
                    ->columns(4),
                Section::make(__('panel.pages.social_media'))
                    ->description(__('panel.pages.social_media_description'))
                    ->components([
                        TextInput::make('social_instagram_url')
                            ->label(__('panel.pages.social_instagram_url'))
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.instagram.com/username'),
                        TextInput::make('social_facebook_url')
                            ->label(__('panel.pages.social_facebook_url'))
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.facebook.com/page'),
                        TextInput::make('social_tiktok_url')
                            ->label(__('panel.pages.social_tiktok_url'))
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.tiktok.com/@username'),
                        TextInput::make('social_whatsapp_url')
                            ->label(__('panel.pages.social_whatsapp_url'))
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://wa.me/971500000000'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setValue('home_h1_text', $state['home_h1_text'] ?? null);
        SiteSetting::setValue('home_h1_text_en', $state['home_h1_text_en'] ?? null);
        SiteSetting::setValue('home_h1_color', $state['home_h1_color'] ?? null);
        $bookingStart = BookingWindow::normalize($state['booking_start_time'] ?? null);
        $bookingEnd = BookingWindow::normalize($state['booking_end_time'] ?? null);

        if ($bookingStart === null || $bookingEnd === null || $bookingStart >= $bookingEnd) {
            Notification::make()
                ->title(__('panel.pages.booking_time_range_invalid'))
                ->danger()
                ->send();

            return;
        }

        SiteSetting::setValue('booking_start_time', $bookingStart);
        SiteSetting::setValue('booking_end_time', $bookingEnd);
        SiteSetting::setValue('booking_is_active', ! empty($state['booking_is_active']) ? '1' : '0');
        SiteSetting::setValue('max_guest_count', (string) max(1, (int) ($state['max_guest_count'] ?? 20)));
        SiteSetting::setValue(
            'booking_tables_per_hour',
            (string) max(1, min(99, (int) ($state['booking_tables_per_hour'] ?? 1))),
        );
        $dayCap = $state['booking_max_reservations_per_day'] ?? null;
        if ($dayCap === null || $dayCap === '') {
            SiteSetting::setValue('booking_max_reservations_per_day', null);
        } else {
            SiteSetting::setValue(
                'booking_max_reservations_per_day',
                (string) max(1, min(10000, (int) $dayCap)),
            );
        }
        $bookingWhatsAppPhone = self::normalizePhoneForWhatsApp((string) ($state['booking_whatsapp_phone'] ?? ''));
        SiteSetting::setValue('booking_whatsapp_phone', $bookingWhatsAppPhone);
        if ($bookingWhatsAppPhone !== '') {
            SiteSetting::setValue('social_whatsapp_url', 'https://wa.me/'.$bookingWhatsAppPhone);
        } else {
            SiteSetting::setValue('social_whatsapp_url', $state['social_whatsapp_url'] ?? null);
        }
        SiteSetting::setValue('social_instagram_url', $state['social_instagram_url'] ?? null);
        SiteSetting::setValue('social_facebook_url', $state['social_facebook_url'] ?? null);
        SiteSetting::setValue('social_tiktok_url', $state['social_tiktok_url'] ?? null);

        Notification::make()
            ->title(__('panel.pages.saved'))
            ->success()
            ->send();
    }

    private static function normalizePhoneForWhatsApp(string $phone): string
    {
        $from = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $to = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $phone = str_replace($from, $to, trim($phone));

        return preg_replace('/\D+/', '', $phone) ?: '';
    }

}
