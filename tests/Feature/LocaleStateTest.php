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

it('prevents locale changes while the schema is updating', function (): void {
    livewire(ListAnimals::class)
        ->assertSeeHtml('<fieldset wire:loading.attr="disabled"')
        ->assertDontSeeHtml('wire:dirty.attr="disabled"')
        ->assertSeeHtml('<select id="activeLocale" wire:change="setActiveLocale($event.target.value)"')
        ->assertDontSeeHtml('wire:model.live="activeLocale"');
});

it('changes locale through an explicit state transition', function (): void {
    livewire(ListAnimals::class)
        ->call('setActiveLocale', Locale::Bulgarian->value)
        ->assertSet('activeLocale', Locale::Bulgarian);
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

it('localizes locale labels', function (string $applicationLocale, array $labels): void {
    app()->setLocale($applicationLocale);

    expect(Locale::English->getLabel())->toBe($labels['en'])
        ->and(Locale::Spanish->getLabel())->toBe($labels['es'])
        ->and(Locale::Portuguese->getLabel())->toBe($labels['pt'])
        ->and(Locale::German->getLabel())->toBe($labels['de'])
        ->and(Locale::Bulgarian->getLabel())->toBe($labels['bg']);
})->with([
    'English' => ['en', ['en' => 'English', 'es' => 'Spanish', 'pt' => 'Portuguese', 'de' => 'German', 'bg' => 'Bulgarian']],
    'Spanish' => ['es', ['en' => 'Inglés', 'es' => 'Español', 'pt' => 'Portugués', 'de' => 'Alemán', 'bg' => 'Búlgaro']],
    'Portuguese' => ['pt', ['en' => 'Inglês', 'es' => 'Espanhol', 'pt' => 'Português', 'de' => 'Alemão', 'bg' => 'Búlgaro']],
    'German' => ['de', ['en' => 'Englisch', 'es' => 'Spanisch', 'pt' => 'Portugiesisch', 'de' => 'Deutsch', 'bg' => 'Bulgarisch']],
    'Bulgarian' => ['bg', ['en' => 'Английски', 'es' => 'Испански', 'pt' => 'Португалски', 'de' => 'Немски', 'bg' => 'Български']],
]);

it('fails clearly when no default locale is available', function (): void {
    FilamentTranslatablePlugin::make()
        ->locales([])
        ->getDefaultLocale();
})->throws(Exception::class, 'No locales defined for the filament-translatable plugin.');
