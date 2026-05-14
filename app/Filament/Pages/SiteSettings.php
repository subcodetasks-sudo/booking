<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
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
            'booking_start_time' => SiteSetting::getValue('booking_start_time', '12:00'),
            'booking_end_time' => SiteSetting::getValue('booking_end_time', '23:00'),
            'booking_is_active' => (bool) SiteSetting::getValue('booking_is_active', true),
            'max_guest_count' => (int) SiteSetting::getValue('max_guest_count', 20),
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
                            ->placeholder('12:00')
                            ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/'),
                        TextInput::make('booking_end_time')
                            ->label(__('panel.pages.booking_end_time'))
                            ->required()
                            ->placeholder('23:00')
                            ->regex('/^([01]\d|2[0-3]):([0-5]\d)$/'),
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
                        TextInput::make('booking_whatsapp_phone')
                            ->label(__('panel.pages.booking_whatsapp_phone'))
                            ->tel()
                            ->required()
                            ->maxLength(32)
                            ->placeholder('905528255694'),
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
        SiteSetting::setValue('booking_start_time', $state['booking_start_time'] ?? '12:00');
        SiteSetting::setValue('booking_end_time', $state['booking_end_time'] ?? '23:00');
        SiteSetting::setValue('booking_is_active', ! empty($state['booking_is_active']) ? '1' : '0');
        SiteSetting::setValue('max_guest_count', (string) max(1, (int) ($state['max_guest_count'] ?? 20)));
        SiteSetting::setValue('booking_whatsapp_phone', self::normalizePhoneForWhatsApp((string) ($state['booking_whatsapp_phone'] ?? '')));
        SiteSetting::setValue('social_instagram_url', $state['social_instagram_url'] ?? null);
        SiteSetting::setValue('social_facebook_url', $state['social_facebook_url'] ?? null);
        SiteSetting::setValue('social_tiktok_url', $state['social_tiktok_url'] ?? null);
        SiteSetting::setValue('social_whatsapp_url', $state['social_whatsapp_url'] ?? null);

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
