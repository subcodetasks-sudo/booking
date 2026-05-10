<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\TodayOverviewStats;
use App\Filament\Widgets\TodayReservationsTable;
use App\Http\Middleware\SetPanelLocale;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->font('cairo')
            ->colors([
                'primary' => '#00aef6',
                'secondary' => '#c9903e', 
            ])
                        ->userMenuItems([
                Action::make('lang_ar')
                    ->label('العربية')
                    ->icon('heroicon-m-language')
                    ->url(fn (): string => route('panel.locale.switch', ['locale' => 'ar']))
                    ->sort(10),
                Action::make('lang_en')
                    ->label('English')
                    ->icon('heroicon-m-language')
                    ->url(fn (): string => route('panel.locale.switch', ['locale' => 'en']))
                    ->sort(11),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                TodayOverviewStats::class,
                TodayReservationsTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                SetPanelLocale::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
