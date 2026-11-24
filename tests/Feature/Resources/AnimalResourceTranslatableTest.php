<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Filament\Resources\AnimalResource\Pages\CreateAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\EditAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ListAnimals;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ViewAnimal;
use Workbench\App\Models\Animal;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

function createAnimalForTranslatableResourceTest(): Animal
{
    return Animal::factory()->create([
        'name' => [
            Locale::English->value => 'Wolf',
            Locale::Bulgarian->value => 'Вълк',
        ],
        'description' => [
            Locale::English->value => 'A social forest animal.',
            Locale::Bulgarian->value => 'Социално горско животно.',
        ],
        'is_active' => true,
    ]);
}

it('displays translated table fields using the active locale', function (): void {
    $animal = createAnimalForTranslatableResourceTest();

    livewire(ListAnimals::class)
        ->set('activeLocale', Locale::English->value)
        ->assertTableColumnStateSet('name', 'Wolf', $animal)
        ->assertTableColumnStateSet('description', 'A social forest animal.', $animal)
        ->assertTableColumnStateSet('is_active', true, $animal)
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertTableColumnStateSet('name', 'Вълк', $animal)
        ->assertTableColumnStateSet('description', 'Социално горско животно.', $animal)
        ->assertTableColumnStateSet('is_active', true, $animal);
});

it('stores all locale values entered before creating a record', function (): void {
    livewire(CreateAnimal::class)
        ->fillForm([
            'name' => 'Fox',
            'description' => 'A clever forest animal.',
            'is_active' => false,
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->fillForm([
            'name' => 'Лисица',
            'description' => 'Хитро горско животно.',
            'is_active' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $animal = Animal::query()->firstOrFail();

    expect($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Лисица')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Хитро горско животно.')
        ->and($animal->getTranslation('name', Locale::English->value))->toBe('Fox')
        ->and($animal->getTranslation('description', Locale::English->value))->toBe('A clever forest animal.')
        ->and($animal->is_active)->toBeFalse();
});

it('rehydrates translatable create form fields when the active locale changes', function (): void {
    livewire(CreateAnimal::class)
        ->fillForm([
            'name' => 'Otter',
            'description' => 'Plays in rivers.',
            'is_active' => false,
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => null,
            'description' => null,
            'is_active' => false,
        ])
        ->fillForm([
            'name' => 'Видра',
            'description' => 'Играе в реки.',
        ])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'name' => 'Otter',
            'description' => 'Plays in rivers.',
            'is_active' => false,
        ]);
});

it('updates only the active locale translation without overwriting other translations', function (): void {
    $animal = createAnimalForTranslatableResourceTest();

    livewire(EditAnimal::class, ['record' => $animal->getKey()])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
            'is_active' => true,
        ])
        ->fillForm([
            'name' => 'Лисица',
            'description' => 'Хитро горско животно.',
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $animal->refresh();

    expect($animal->getTranslation('name', Locale::English->value))->toBe('Wolf')
        ->and($animal->getTranslation('description', Locale::English->value))->toBe('A social forest animal.')
        ->and($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Лисица')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Хитро горско животно.')
        ->and($animal->is_active)->toBeFalse();
});

it('saves locale-specific edit state for all locales at once', function (): void {
    $animal = createAnimalForTranslatableResourceTest();

    livewire(EditAnimal::class, ['record' => $animal->getKey()])
        ->fillForm([
            'name' => 'Fox',
            'description' => 'Moves quietly.',
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
        ])
        ->fillForm([
            'name' => 'Лисица',
            'description' => 'Движи се тихо.',
        ])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'name' => 'Fox',
            'description' => 'Moves quietly.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $animal->refresh();

    expect($animal->getTranslation('name', Locale::English->value))->toBe('Fox')
        ->and($animal->getTranslation('description', Locale::English->value))->toBe('Moves quietly.')
        ->and($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Лисица')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Движи се тихо.');
});

it('displays translated view fields using the active locale', function (): void {
    $animal = createAnimalForTranslatableResourceTest();

    livewire(ViewAnimal::class, ['record' => $animal->getKey()])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'name' => 'Wolf',
            'description' => 'A social forest animal.',
            'is_active' => true,
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
            'is_active' => true,
        ]);
});

it('switches the displayed and edited translations when the active locale changes', function (): void {
    $animal = createAnimalForTranslatableResourceTest();

    livewire(EditAnimal::class, ['record' => $animal->getKey()])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'name' => 'Wolf',
            'description' => 'A social forest animal.',
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
        ])
        ->fillForm([
            'name' => 'Мечка',
            'description' => 'Голямо горско животно.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $animal->refresh();

    expect($animal->getTranslation('name', Locale::English->value))->toBe('Wolf')
        ->and($animal->getTranslation('description', Locale::English->value))->toBe('A social forest animal.')
        ->and($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Мечка')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Голямо горско животно.');
});
