<?php

use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithActiveLocale;
use Workbench\App\Filament\Resources\AnimalResource;

beforeEach(function (): void {
    $this->component = new class
    {
        use InteractsWithActiveLocale;

        public array $cachedLocales = [];

        public int $changes = 0;

        public static function getResource(): string
        {
            return AnimalResource::class;
        }

        public function cacheTranslatableFormStateForLocale(Locale $locale): void
        {
            $this->cachedLocales[] = $locale;
        }

        protected function activeLocaleChanged(): void
        {
            $this->changes++;
        }
    };
});

it('does not cache or refresh state when the same locale is selected', function (): void {
    $this->component->mountInteractsWithActiveLocale();
    $this->component->setActiveLocale(Locale::English);

    expect($this->component->cachedLocales)->toBe([])
        ->and($this->component->changes)->toBe(0);
});

it('caches the outgoing locale before refreshing the new locale exactly once', function (): void {
    $this->component->mountInteractsWithActiveLocale();
    $this->component->setActiveLocale(Locale::Bulgarian);

    expect($this->component->cachedLocales)->toBe([Locale::English])
        ->and($this->component->changes)->toBe(1)
        ->and($this->component->getActiveLocale())->toBe(Locale::Bulgarian);
});

it('resolves the locale lazily and preserves initialized state on mount', function (): void {
    expect($this->component->getActiveLocale())->toBe(Locale::English);
    $this->component->activeLocale = Locale::Bulgarian;
    $this->component->mountInteractsWithActiveLocale();

    expect($this->component->getActiveLocale())->toBe(Locale::Bulgarian);
});

it('ignores a redundant Livewire update without refreshing dependent state', function (): void {
    $this->component->mountInteractsWithActiveLocale();
    $this->component->updatingActiveLocale(Locale::English);
    $this->component->updatedActiveLocale(Locale::English);

    expect($this->component->changes)->toBe(0)
        ->and($this->component->getActiveLocale())->toBe(Locale::English);
});

it('restores the previous locale after an invalid Livewire update', function (?string $locale): void {
    $this->component->activeLocale = Locale::Bulgarian;
    $this->component->updatingActiveLocale($locale);
    $this->component->updatedActiveLocale($locale);

    expect($this->component->getActiveLocale())->toBe(Locale::Bulgarian)
        ->and($this->component->changes)->toBe(0);
})->with([null, 'invalid', 'de']);

it('leaves an uninitialized locale resolvable after an invalid Livewire update', function (): void {
    $this->component->updatingActiveLocale(null);
    $this->component->updatedActiveLocale(null);

    expect(isset($this->component->activeLocale))->toBeFalse()
        ->and($this->component->getActiveLocale())->toBe(Locale::English)
        ->and($this->component->cachedLocales)->toBe([]);
});
