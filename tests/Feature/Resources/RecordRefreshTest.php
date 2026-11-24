<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Filament\Resources\AnimalResource\Pages\EditAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ViewAnimal;
use Workbench\App\Models\Animal;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('refreshes only requested fields from the database in the active locale', function (string $page): void {
    $animal = Animal::factory()->create([
        'name' => ['en' => 'Fox', 'bg' => 'Лисица'],
        'description' => ['en' => 'Forest animal', 'bg' => 'Горско животно'],
    ]);

    $component = livewire($page, ['record' => $animal->getKey()])
        ->call('setActiveLocale', Locale::Bulgarian->value)
        ->set('data.description', 'Unsaved description');

    $animal->setTranslation('name', 'bg', 'Вълк')->save();

    // Filament exposes refreshFormData to server-side actions only.
    $instance = $component->instance();
    $instance->getRecord()->refresh();
    $instance->refreshFormData(['name']);

    expect($instance->data)->toMatchArray([
        'name' => 'Вълк',
        'description' => 'Unsaved description',
    ]);

    expect($animal->fresh()->getTranslation('name', 'en'))->toBe('Fox');
})->with([EditAnimal::class, ViewAnimal::class]);
