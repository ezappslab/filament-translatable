<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\Support\SpatieLaravelTranslatableContentDriver;
use Workbench\App\Models\Animal;
use Workbench\App\Models\User;

pest()->use(RefreshDatabase::class);

it('identifies translated attributes without treating ordinary model fields as translated', function (): void {
    $driver = new SpatieLaravelTranslatableContentDriver('en');

    foreach (range(1, 2) as $attempt) {
        expect($driver->isAttributeTranslatable(Animal::class, 'name'))->toBeTrue()
            ->and($driver->isAttributeTranslatable(Animal::class, 'is_active'))->toBeFalse()
            ->and($driver->isAttributeTranslatable(User::class, 'name'))->toBeFalse();
    }
});

it('uses the configured fallback locale for an unknown locale', function (): void {
    Filament::getPlugin('filament-translatable')
        ->fallbackLocale(Locale::Bulgarian);

    $animal = (new SpatieLaravelTranslatableContentDriver('invalid'))->makeRecord(Animal::class, ['name' => 'Fox']);

    expect($animal->getTranslations('name'))->toBe(['bg' => 'Fox']);
});

it('does not substitute a fallback translation when extracting a missing translation', function (): void {
    $animal = new Animal(['name' => ['en' => 'Fox'], 'is_active' => false]);
    $animal->makeHidden('description');

    $attributes = (new SpatieLaravelTranslatableContentDriver('bg'))->getRecordAttributesToArray($animal);

    expect($attributes['name'])->toBe('')
        ->and($attributes['is_active'])->toBeFalse()
        ->and($attributes)->not->toHaveKey('description');
});

it('keeps hidden translated attributes out of the extracted data', function (): void {
    $animal = new Animal(['name' => ['en' => 'Fox', 'bg' => 'Лисица']]);
    $animal->makeHidden('name');

    expect((new SpatieLaravelTranslatableContentDriver('bg'))->getRecordAttributesToArray($animal))
        ->not->toHaveKey('name');
});

it('makes an unsaved record with translation maps and ordinary attributes', function (): void {
    $animal = (new SpatieLaravelTranslatableContentDriver('bg'))->makeRecord(Animal::class, [
        'name' => ['en' => 'Fox', 'bg' => 'Лисица'],
        'description' => 'Описание',
        'is_active' => false,
    ]);

    expect($animal->exists)->toBeFalse()
        ->and(Animal::query()->count())->toBe(0)
        ->and($animal->getTranslations('name'))->toBe(['en' => 'Fox', 'bg' => 'Лисица'])
        ->and($animal->getTranslations('description'))->toBe(['bg' => 'Описание'])
        ->and($animal->is_active)->toBeFalse();
});

it('merges supplied translations while preserving translations omitted from an update', function (): void {
    $animal = Animal::factory()->create([
        'name' => ['en' => 'Fox', 'bg' => 'Лисица', 'de' => 'Fuchs'],
    ]);

    $updated = (new SpatieLaravelTranslatableContentDriver('bg'))->updateRecord($animal, [
        'name' => ['en' => 'Wolf', 'bg' => 'Вълк'],
        'is_active' => false,
    ]);

    expect($updated)->toBe($animal)
        ->and($animal->fresh()->getTranslations('name'))->toBe(['en' => 'Wolf', 'bg' => 'Вълк', 'de' => 'Fuchs'])
        ->and($animal->fresh()->is_active)->toBeFalse();
});

it('handles records without translation support using ordinary attributes', function (): void {
    $driver = new SpatieLaravelTranslatableContentDriver('bg');
    $user = $driver->makeRecord(User::class, ['name' => 'Taylor']);

    expect($user->name)->toBe('Taylor')
        ->and($driver->getRecordAttributesToArray($user)['name'])->toBe('Taylor');
});

it('searches only the active locale', function (string $locale, string $search, string $expectedName): void {
    $fox = Animal::factory()->create(['name' => ['en' => 'Fox', 'bg' => 'Лисица']]);
    $wolf = Animal::factory()->create(['name' => ['en' => 'Wolf', 'bg' => 'Fox']]);
    $driver = new SpatieLaravelTranslatableContentDriver($locale);
    $query = Animal::query();

    expect($driver->applySearchConstraintToQuery($query, 'name', $search, 'where'))->toBe($query)
        ->and($query->pluck('id')->all())->toBe([$expectedName === 'fox' ? $fox->id : $wolf->id]);
})->with([
    'English' => ['en', 'Fox', 'fox'],
    'Bulgarian' => ['bg', 'Fox', 'wolf'],
]);

it('supports an orWhere search constraint', function (): void {
    $animal = Animal::factory()->create(['name' => ['bg' => 'Лисица']]);
    $query = Animal::query()->whereRaw('1 = 0');

    (new SpatieLaravelTranslatableContentDriver('bg'))->applySearchConstraintToQuery($query, 'name', 'Лис', 'orWhere');

    expect($query->pluck('id')->all())->toBe([$animal->id]);
});
