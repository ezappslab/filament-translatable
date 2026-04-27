<?php

namespace Tests;

// use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Infinity\FilamentTranslatable\FilamentTranslatableServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Workbench\App\Providers\FilamentPanelServiceProvider;

class TestCase extends BaseTestCase
{
    use WithWorkbench;

    /**
     * Retrieves the list of package providers used by the application.
     *
     * @param  mixed  $app  The application instance, typically used to resolve dependencies or context.
     * @return array An array of fully qualified class names representing the package providers.
     */
    protected function getPackageProviders($app): array
    {
        $providers = [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentPanelServiceProvider::class,
            FilamentTranslatableServiceProvider::class,
        ];

        sort($providers);

        return $providers;
    }
}
