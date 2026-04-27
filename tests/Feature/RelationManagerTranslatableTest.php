<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Filament\Resources\UserResource\Pages\EditUser;
use Workbench\App\Filament\Resources\UserResource\RelationManagers\AnimalsRelationManager;
use Workbench\App\Models\Animal;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

function createUserWithAnimalForRelationManagerTest(): array
{
    $user = User::factory()->create([
        'name' => 'Taylor Reed',
        'email' => 'taylor@example.test',
    ]);

    $animal = Animal::factory()->create([
        'user_id' => $user->getKey(),
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

    return [$user, $animal];
}

function rawAnimalTranslationsForRelationManagerTest(Animal $animal, string $attribute): array
{
    $value = DB::table('animals')
        ->where('id', $animal->getKey())
        ->value($attribute);

    return json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
}

it('displays relation manager table fields using the active locale', function () {
    [$user, $animal] = createUserWithAnimalForRelationManagerTest();

    livewire(AnimalsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->set('activeLocale', Locale::English->value)
        ->assertTableColumnStateSet('name', 'Wolf', $animal)
        ->assertTableColumnStateSet('description', 'A social forest animal.', $animal)
        ->assertTableColumnStateSet('is_active', true, $animal)
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertTableColumnStateSet('name', 'Вълк', $animal)
        ->assertTableColumnStateSet('description', 'Социално горско животно.', $animal)
        ->assertTableColumnStateSet('is_active', true, $animal);
});

it('creates related records with translations under the active locale', function () {
    $user = User::factory()->create();

    livewire(AnimalsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->callTableAction('create', data: [
            'name' => 'Лисица',
            'description' => 'Хитро горско животно.',
            'is_active' => false,
        ])
        ->assertHasNoTableActionErrors();

    $animal = $user->animals()->firstOrFail();

    expect($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Лисица')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Хитро горско животно.')
        ->and($animal->getTranslations('name'))->not->toHaveKey(Locale::English->value)
        ->and($animal->is_active)->toBeFalse()
        ->and(rawAnimalTranslationsForRelationManagerTest($animal, 'name'))->toBe([
            Locale::Bulgarian->value => 'Лисица',
        ]);
});

it('rehydrates relation manager create action fields when the active locale changes', function () {
    $user = User::factory()->create();

    livewire(AnimalsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->mountTableAction('create')
        ->setTableActionData([
            'name' => 'Otter',
            'description' => 'Plays in rivers.',
            'is_active' => false,
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertTableActionDataSet([
            'name' => null,
            'description' => null,
            'is_active' => false,
        ])
        ->setTableActionData([
            'name' => 'Видра',
            'description' => 'Играе в реки.',
        ])
        ->set('activeLocale', Locale::English->value)
        ->assertTableActionDataSet([
            'name' => 'Otter',
            'description' => 'Plays in rivers.',
            'is_active' => false,
        ]);
});

it('updates only the active locale from a relation manager edit action', function () {
    [$user, $animal] = createUserWithAnimalForRelationManagerTest();

    livewire(AnimalsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->mountTableAction('edit', $animal)
        ->assertTableActionDataSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
            'is_active' => true,
        ])
        ->setTableActionData([
            'name' => 'Мечка',
            'description' => 'Голямо горско животно.',
            'is_active' => false,
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    $animal->refresh();

    expect($animal->getTranslation('name', Locale::English->value))->toBe('Wolf')
        ->and($animal->getTranslation('description', Locale::English->value))->toBe('A social forest animal.')
        ->and($animal->getTranslation('name', Locale::Bulgarian->value))->toBe('Мечка')
        ->and($animal->getTranslation('description', Locale::Bulgarian->value))->toBe('Голямо горско животно.')
        ->and($animal->is_active)->toBeFalse()
        ->and(rawAnimalTranslationsForRelationManagerTest($animal, 'description'))->toBe([
            Locale::English->value => 'A social forest animal.',
            Locale::Bulgarian->value => 'Голямо горско животно.',
        ]);
});

it('rehydrates relation manager edit action fields when the active locale changes', function () {
    [$user, $animal] = createUserWithAnimalForRelationManagerTest();

    livewire(AnimalsRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->mountTableAction('edit', $animal)
        ->setTableActionData([
            'name' => 'Fox',
            'description' => 'Moves quietly.',
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertTableActionDataSet([
            'name' => 'Вълк',
            'description' => 'Социално горско животно.',
            'is_active' => true,
        ])
        ->setTableActionData([
            'name' => 'Лисица',
            'description' => 'Движи се тихо.',
        ])
        ->set('activeLocale', Locale::English->value)
        ->assertTableActionDataSet([
            'name' => 'Fox',
            'description' => 'Moves quietly.',
            'is_active' => true,
        ]);
});
