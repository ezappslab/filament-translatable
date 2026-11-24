<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
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

it('ignores invalid explicit locale selections', function (?string $locale): void {
    livewire(ListAnimals::class)
        ->call('setActiveLocale', Locale::Bulgarian->value)
        ->call('setActiveLocale', $locale)
        ->assertSet('activeLocale', Locale::Bulgarian);
})->with([null, 'invalid', Locale::German->value]);

it('uses the default locale when a stored selection is unusable', function (mixed $storedLocale): void {
    session()->put(
        'filament.workbench.resources.workbench.app.filament.resources.animal-resource.active_locale',
        $storedLocale,
    );

    livewire(ListAnimals::class)->assertSet('activeLocale', Locale::English);
})->with([['invalid'], [123], [Locale::German], [Locale::German->value]]);

it('restores a valid locale stored as a string', function (): void {
    session()->put(
        'filament.workbench.resources.workbench.app.filament.resources.animal-resource.active_locale',
        Locale::Bulgarian->value,
    );

    livewire(ListAnimals::class)->assertSet('activeLocale', Locale::Bulgarian);
});
