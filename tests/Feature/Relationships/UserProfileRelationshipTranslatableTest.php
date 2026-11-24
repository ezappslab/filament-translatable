<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Filament\Resources\UserResource\Pages\CreateUser;
use Workbench\App\Filament\Resources\UserResource\Pages\EditUser;
use Workbench\App\Filament\Resources\UserResource\Pages\ViewUser;
use Workbench\App\Models\Profile;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

function createUserWithTranslatableProfileForRelationshipTest(): User
{
    $user = User::factory()
        ->has(Profile::factory()->state([
            'headline' => [
                Locale::English->value => 'Product strategist',
                Locale::Bulgarian->value => 'Продуктов стратег',
            ],
            'biography' => [
                Locale::English->value => 'Builds focused product systems.',
                Locale::Bulgarian->value => 'Изгражда фокусирани продуктови системи.',
            ],
            'is_public' => true,
        ]))
        ->create([
            'name' => 'Taylor Reed',
            'email' => 'taylor@example.test',
        ]);

    return $user->refresh();
}

function rawProfileTranslationsForRelationshipTest(Profile $profile, string $attribute): array
{
    $value = DB::table('profiles')
        ->where('id', $profile->getKey())
        ->value($attribute);

    return json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
}

it('creates translatable profile fields from a relationship section for the active locale', function (): void {
    livewire(CreateUser::class)
        ->set('activeLocale', Locale::Bulgarian->value)
        ->fillForm([
            'name' => 'Maria Ivanova',
            'email' => 'maria@example.test',
            'password' => 'password',
            'profile' => [
                'headline' => 'Ръководител екип',
                'biography' => 'Води екипи за продуктова разработка.',
                'is_public' => false,
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->with('profile')->firstOrFail();
    $profile = $user->profile;

    expect($profile)->toBeInstanceOf(Profile::class)
        ->and($profile->getTranslation('headline', Locale::Bulgarian->value))->toBe('Ръководител екип')
        ->and($profile->getTranslation('biography', Locale::Bulgarian->value))->toBe('Води екипи за продуктова разработка.')
        ->and($profile->getTranslations('headline'))->not->toHaveKey(Locale::English->value)
        ->and($profile->is_public)->toBeFalse()
        ->and(rawProfileTranslationsForRelationshipTest($profile, 'headline'))->toBe([
            Locale::Bulgarian->value => 'Ръководител екип',
        ])
        ->and(rawProfileTranslationsForRelationshipTest($profile, 'biography'))->toBe([
            Locale::Bulgarian->value => 'Води екипи за продуктова разработка.',
        ]);
});

it('rehydrates relationship section create form fields when the active locale changes', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Jordan Hale',
            'email' => 'jordan@example.test',
            'password' => 'password',
            'profile' => [
                'headline' => 'Research lead',
                'biography' => 'Turns insight into direction.',
                'is_public' => false,
            ],
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'name' => 'Jordan Hale',
            'email' => 'jordan@example.test',
            'profile.headline' => null,
            'profile.biography' => null,
            'profile.is_public' => false,
        ])
        ->fillForm([
            'profile' => [
                'headline' => 'Ръководител проучвания',
                'biography' => 'Превръща прозренията в посока.',
            ],
        ])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'profile.headline' => 'Research lead',
            'profile.biography' => 'Turns insight into direction.',
            'profile.is_public' => false,
        ]);
});

it('edits only the active locale on an existing relationship profile', function (): void {
    $user = createUserWithTranslatableProfileForRelationshipTest();

    livewire(EditUser::class, ['record' => $user->getKey()])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'profile.headline' => 'Продуктов стратег',
            'profile.biography' => 'Изгражда фокусирани продуктови системи.',
            'profile.is_public' => true,
        ])
        ->fillForm([
            'profile' => [
                'headline' => 'Директор продукт',
                'biography' => 'Ръководи продуктови стратегии.',
                'is_public' => false,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $profile = $user->profile()->firstOrFail();

    expect($profile->getTranslation('headline', Locale::English->value))->toBe('Product strategist')
        ->and($profile->getTranslation('biography', Locale::English->value))->toBe('Builds focused product systems.')
        ->and($profile->getTranslation('headline', Locale::Bulgarian->value))->toBe('Директор продукт')
        ->and($profile->getTranslation('biography', Locale::Bulgarian->value))->toBe('Ръководи продуктови стратегии.')
        ->and($profile->is_public)->toBeFalse()
        ->and(rawProfileTranslationsForRelationshipTest($profile, 'headline'))->toBe([
            Locale::English->value => 'Product strategist',
            Locale::Bulgarian->value => 'Директор продукт',
        ]);
});

it('switches relationship section fields between locales without overwriting existing translations', function (): void {
    $user = createUserWithTranslatableProfileForRelationshipTest();

    livewire(EditUser::class, ['record' => $user->getKey()])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'profile.headline' => 'Product strategist',
            'profile.biography' => 'Builds focused product systems.',
        ])
        ->fillForm([
            'profile' => [
                'headline' => 'Customer researcher',
                'biography' => 'Turns research into product decisions.',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'profile.headline' => 'Продуктов стратег',
            'profile.biography' => 'Изгражда фокусирани продуктови системи.',
        ])
        ->fillForm([
            'profile' => [
                'headline' => 'Изследовател клиенти',
                'biography' => 'Превръща проучванията в продуктови решения.',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $profile = $user->profile()->firstOrFail();

    expect($profile->getTranslation('headline', Locale::English->value))->toBe('Customer researcher')
        ->and($profile->getTranslation('biography', Locale::English->value))->toBe('Turns research into product decisions.')
        ->and($profile->getTranslation('headline', Locale::Bulgarian->value))->toBe('Изследовател клиенти')
        ->and($profile->getTranslation('biography', Locale::Bulgarian->value))->toBe('Превръща проучванията в продуктови решения.')
        ->and(rawProfileTranslationsForRelationshipTest($profile, 'biography'))->toBe([
            Locale::English->value => 'Turns research into product decisions.',
            Locale::Bulgarian->value => 'Превръща проучванията в продуктови решения.',
        ]);
});

it('views translatable profile fields from a relationship section using the active locale', function (): void {
    $user = createUserWithTranslatableProfileForRelationshipTest();

    livewire(ViewUser::class, ['record' => $user->getKey()])
        ->set('activeLocale', Locale::English->value)
        ->assertSchemaStateSet([
            'name' => 'Taylor Reed',
            'email' => 'taylor@example.test',
            'profile.headline' => 'Product strategist',
            'profile.biography' => 'Builds focused product systems.',
            'profile.is_public' => true,
        ])
        ->set('activeLocale', Locale::Bulgarian->value)
        ->assertSchemaStateSet([
            'profile.headline' => 'Продуктов стратег',
            'profile.biography' => 'Изгражда фокусирани продуктови системи.',
            'profile.is_public' => true,
        ]);
});
