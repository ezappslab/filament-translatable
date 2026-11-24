<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\Support\SpatieLaravelTranslatableContentDriver;
use Workbench\App\Filament\Resources\UserResource\Pages\EditUser;
use Workbench\App\Filament\Resources\UserResource\Pages\ViewUser;
use Workbench\App\Models\Animal;
use Workbench\App\Models\Profile;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('propagates through loaded singular and collection relationships without queries', function (): void {
    $user = new User;
    $profile = new Profile(['headline' => ['en' => 'Writer', 'bg' => 'Писател']]);
    $animal = new Animal(['name' => ['en' => 'Cat', 'bg' => 'Котка']]);
    $otherAnimal = new Animal(['name' => ['en' => 'Dog', 'bg' => 'Куче']]);

    $user->setRelation('profile', $profile);
    $profile->setRelation('user', $user);
    $profile->setRelation('animals', new Collection([$animal, $otherAnimal]));
    $animal->setRelation('user', $user);
    $otherAnimal->setRelation('missing', null);
    $otherAnimal->setRelation('empty', new Collection);

    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        $driver = new SpatieLaravelTranslatableContentDriver('bg');

        expect($driver->setRecordLocale($user))->toBe($user)
            ->and($profile->headline)->toBe('Писател')
            ->and($animal->name)->toBe('Котка')
            ->and($otherAnimal->name)->toBe('Куче');

        (new SpatieLaravelTranslatableContentDriver('en'))->setRecordLocale($user);
        $driver->setRecordLocale($user);

        expect($profile->getLocale())->toBe('bg')
            ->and($animal->getLocale())->toBe('bg')
            ->and($otherAnimal->getLocale())->toBe('bg')
            ->and(DB::getQueryLog())->toBe([]);
    } finally {
        DB::disableQueryLog();
    }
});

it('applies the locale before extracting attributes from a non-translatable parent', function (): void {
    $user = new User(['name' => 'Taylor']);
    $profile = new Profile(['headline' => ['en' => 'Writer', 'bg' => 'Писател']]);
    $user->setRelation('profile', $profile);

    $data = (new SpatieLaravelTranslatableContentDriver('bg'))->getRecordAttributesToArray($user);

    expect($data['name'])->toBe('Taylor')
        ->and($profile->headline)->toBe('Писател')
        ->and($data)->not->toHaveKey('profile');
});

it('leaves unloaded relationships alone and handles relationships loaded later on the next call', function (): void {
    $animal = new Animal(['name' => ['en' => 'Cat', 'bg' => 'Котка']]);
    $driver = new SpatieLaravelTranslatableContentDriver('bg');

    $driver->setRecordLocale($animal);

    expect($animal->name)->toBe('Котка')
        ->and($animal->relationLoaded('user'))->toBeFalse();

    $user = new User;
    $profile = new Profile(['headline' => ['en' => 'Writer', 'bg' => 'Писател']]);
    $user->setRelation('profile', $profile);
    $animal->setRelation('user', $user);
    $driver->setRecordLocale($animal);

    expect($profile->headline)->toBe('Писател');
});

it('applies the current page locale whenever an edit or view record is accessed', function (string $page): void {
    $user = User::factory()->create();
    $component = livewire($page, ['record' => $user->getKey()]);
    $instance = $component->instance();
    $profile = new Profile(['headline' => ['en' => 'Writer', 'bg' => 'Писател']]);
    $instance->getRecord()->setRelation('profile', $profile);

    $instance->activeLocale = Locale::Bulgarian;

    expect($instance->getRecord()->profile->headline)->toBe('Писател');

    $instance->activeLocale = Locale::English;

    expect($instance->getRecord()->profile->headline)->toBe('Writer');
})->with([EditUser::class, ViewUser::class]);
