<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
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
            'max_guest_count' => (int) SiteSetting::getValue('max_guest_count', 20),
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
                        TextInput::make('max_guest_count')
                            ->label(__('panel.pages.max_guest_count'))
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(500)
                            ->default(20),
                    ])
                    ->columns(3),
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
        SiteSetting::setValue('max_guest_count', (string) max(1, (int) ($state['max_guest_count'] ?? 20)));

        Notification::make()
            ->title(__('panel.pages.saved'))
            ->success()
            ->send();
    }
}
