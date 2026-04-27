<?php

namespace Workbench\App\Providers;

use Filament\Panel;
use Filament\PanelProvider;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;
use Workbench\App\Filament\Resources\AnimalResource;
use Workbench\App\Filament\Resources\UserResource;

class FilamentPanelServiceProvider extends PanelProvider
{
    /**
     * Configure the Workbench Filament panel.
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('workbench')
            ->path('workbench')
            ->resources([
                AnimalResource::class,
                UserResource::class,
            ])
            ->plugin(
                FilamentTranslatablePlugin::make()
                    ->locales([
                        Locale::English,
                        Locale::Bulgarian,
                    ])
            );
    }
}
