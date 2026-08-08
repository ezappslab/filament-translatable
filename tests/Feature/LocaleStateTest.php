<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;
use Workbench\App\Filament\Resources\AnimalResource\Pages\CreateAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ListAnimals;
use Workbench\App\Filament\Resources\UserResource\Pages\ListUsers;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('uses the first configured locale by default', function (): void {
    livewire(ListAnimals::class)
        ->assertSet('activeLocale', Locale::English);
});

it('persists the active locale across pages for the same resource', function (): void {
    livewire(ListAnimals::class)
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSet('activeLocale', Locale::Bulgarian);

    livewire(CreateAnimal::class)
        ->assertSet('activeLocale', Locale::Bulgarian);
});

it('isolates the active locale between resources', function (): void {
    livewire(ListAnimals::class)
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSet('activeLocale', Locale::Bulgarian);

    livewire(ListUsers::class)
        ->assertSet('activeLocale', Locale::English);
});

it('rejects locales that are not configured for the plugin', function (): void {
    livewire(ListAnimals::class)
        ->set('activeLocale', Locale::German->value)
        ->assertSet('activeLocale', Locale::English);
});

it('exposes its configured locales and fallback locale', function (): void {
    $plugin = FilamentTranslatablePlugin::make()
        ->locales([Locale::Bulgarian, Locale::English])
        ->fallbackLocale(Locale::Bulgarian);

    expect($plugin->getLocales())->toBe([Locale::Bulgarian, Locale::English])
        ->and($plugin->getDefaultLocale())->toBe(Locale::Bulgarian)
        ->and($plugin->getFallbackLocale())->toBe(Locale::Bulgarian);
});

it('fails clearly when no default locale is available', function (): void {
    FilamentTranslatablePlugin::make()
        ->locales([])
        ->getDefaultLocale();
})->throws(Exception::class, 'No locales defined for the filament-translatable plugin.');
