<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Enums\Locale;
use Tests\Fixtures\TranslatableForm;
use Workbench\App\Models\User;

pest()->use(RefreshDatabase::class);

it('discovers hidden translated fields and separates ordinary fields', function (): void {
    $component = new TranslatableForm;
    $schema = $component->form;

    // Repeated reads must retain the same classification when served from cache.
    foreach (range(1, 2) as $attempt) {
        expect($component->getFormFieldStatePaths($schema))->toBe(['name', 'description', 'is_active'])
            ->and($component->getTranslatableFormStatePaths($schema))->toBe(['name', 'description'])
            ->and($component->getNonTranslatableFormStatePaths($schema))->toBe(['is_active']);
    }
});

it('reuses drivers within a locale and isolates drivers between locales and components', function (): void {
    $component = new TranslatableForm;
    $english = $component->makeFilamentTranslatableContentDriver();

    expect($component->makeFilamentTranslatableContentDriver())->toBe($english);
    $component->activeLocale = Locale::Bulgarian;
    expect($component->makeFilamentTranslatableContentDriver())->not->toBe($english);
    $component->activeLocale = Locale::English;
    expect($component->makeFilamentTranslatableContentDriver())->toBe($english)
        ->and((new TranslatableForm)->makeFilamentTranslatableContentDriver())->not->toBe($english);
});

it('preserves null and empty cached translations without adding fields absent from submitted data', function (): void {
    $component = new TranslatableForm;
    $component->translatableFormStateByLocale = [
        'bg' => ['form' => ['name' => null, 'description' => 'Cached description']],
    ];

    expect($component->mergeCachedTranslationsIntoData(['name' => '', 'is_active' => false]))->toBe([
        'name' => ['en' => '', 'bg' => null],
        'is_active' => false,
    ]);

    $component->translatableFormStateByLocale['bg']['form']['name'] = '';
    expect($component->mergeCachedTranslationsIntoData(['name' => 'Fox'])['name'])->toBe(['en' => 'Fox', 'bg' => ''])
        ->and($component->mergeCachedTranslationsIntoData(['name' => 'Fox'], 'missing'))->toBe(['name' => 'Fox']);
});

it('uses ordinary model operations when the content driver is disabled', function (): void {
    $component = new TranslatableForm;
    $component->driverEnabled = false;
    $user = $component->makeTranslatableRecord(User::class, ['name' => 'Taylor']);

    expect($component->makeFilamentTranslatableContentDriver())->toBeNull()
        ->and($user->exists)->toBeFalse()
        ->and($component->getTranslatableRecordAttributesToArray($user))->toBe(['name' => 'Taylor'])
        ->and($component->getTranslatableFormStatePaths($component->form))->toBe([]);

    $user = User::factory()->create(['name' => 'Taylor']);
    expect($component->updateTranslatableRecord($user, ['name' => 'Alex']))->toBe($user)
        ->and($user->fresh()->name)->toBe('Alex');
});
