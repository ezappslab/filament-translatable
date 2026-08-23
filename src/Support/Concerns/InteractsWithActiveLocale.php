<?php

namespace Infinity\FilamentTranslatable\Support\Concerns;

use Filament\Facades\Filament;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;

/** @phpstan-ignore trait.unused */
trait InteractsWithActiveLocale
{
    public Locale $activeLocale;

    protected ?Locale $previousActiveLocale = null;

    /**
     * Mount the active locale state.
     */
    public function mountInteractsWithActiveLocale(): void
    {
        if (isset($this->activeLocale)) {
            return;
        }

        $this->activeLocale = $this->resolveActiveLocale();
    }

    /**
     * Get the active locale.
     */
    public function getActiveLocale(): Locale
    {
        return $this->activeLocale ??= $this->resolveActiveLocale();
    }

    /**
     * Set the active locale.
     */
    public function setActiveLocale(Locale|string|null $locale): void
    {
        $locale = $this->normalizeActiveLocale($locale);

        if (! $locale) {
            return;
        }

        if (! $this->isTranslatableLocale($locale)) {
            return;
        }

        $previousLocale = $this->activeLocale ?? null;

        if ($previousLocale === $locale) {
            return;
        }

        $this->cacheActiveLocaleDependentState($previousLocale);

        $this->applyActiveLocale($locale);
    }

    /**
     * Store the locale before Livewire updates it.
     */
    public function updatingActiveLocale(Locale|string|null $locale): void
    {
        $this->previousActiveLocale = $this->activeLocale ?? null;

        $this->cacheActiveLocaleDependentState($this->previousActiveLocale);
    }

    /**
     * Handle Livewire updates to the active locale.
     */
    public function updatedActiveLocale(Locale|string|null $locale): void
    {
        $locale = $this->normalizeActiveLocale($locale);

        if ((! $locale) || (! $this->isTranslatableLocale($locale))) {
            if ($this->previousActiveLocale) {
                $this->activeLocale = $this->previousActiveLocale;
            } else {
                unset($this->activeLocale);
            }

            return;
        }

        if ($this->previousActiveLocale === $locale) {
            return;
        }

        $this->applyActiveLocale($locale);
    }

    /**
     * Apply the active locale and refresh dependent state.
     */
    protected function applyActiveLocale(Locale $locale): void
    {
        $this->activeLocale = $locale;

        $this->storeActiveLocale($locale);

        $this->activeLocaleChanged();
    }

    /**
     * Get the locales available for translation.
     *
     * @return array<int, Locale>
     */
    public function getTranslatableLocales(): array
    {
        return $this->getFilamentTranslatablePlugin()->getLocales();
    }

    /**
     * Hook to handle logic after the active locale has changed.
     */
    protected function activeLocaleChanged(): void {}

    /**
     * Cache state that belongs to the current locale before it changes.
     */
    protected function cacheActiveLocaleDependentState(?Locale $locale): void
    {
        if (! $locale) {
            return;
        }

        if (method_exists($this, 'cacheTranslatableFormStateForLocale')) {
            $this->cacheTranslatableFormStateForLocale($locale);
        }
    }

    /**
     * Normalize a locale value.
     */
    protected function normalizeActiveLocale(Locale|string|null $locale): ?Locale
    {
        if ($locale instanceof Locale) {
            return $locale;
        }

        if (! is_string($locale)) {
            return null;
        }

        return Locale::tryFrom($locale);
    }

    /**
     * Resolve the active locale.
     */
    protected function resolveActiveLocale(): Locale
    {
        return $this->getStoredActiveLocale()
            ?? $this->getFilamentTranslatablePlugin()->getDefaultLocale();
    }

    /**
     * Get the stored active locale.
     */
    protected function getStoredActiveLocale(): ?Locale
    {
        $key = $this->getActiveLocaleStorageKey();
        $storedLocale = session()->get($key);

        if ($storedLocale instanceof Locale) {
            return $this->isTranslatableLocale($storedLocale) ? $storedLocale : null;
        }

        if (! is_string($storedLocale)) {
            return null;
        }

        $locale = Locale::tryFrom($storedLocale);

        return ($locale && $this->isTranslatableLocale($locale)) ? $locale : null;
    }

    /**
     * Store the active locale.
     */
    protected function storeActiveLocale(Locale $locale): void
    {
        session()->put($this->getActiveLocaleStorageKey(), $locale);
    }

    /**
     * Get the active locale session key.
     */
    protected function getActiveLocaleStorageKey(): string
    {
        return sprintf(
            'filament.%s.%s.active_locale',
            $this->getActiveLocaleStoragePanelKey(),
            $this->getActiveLocaleStorageScopeKey(),
        );
    }

    /**
     * Get the panel segment for the active locale session key.
     */
    protected function getActiveLocaleStoragePanelKey(): string
    {
        return Filament::getCurrentPanel()?->getId()
            ?? Filament::getDefaultPanel()->getId();
    }

    /**
     * Get the resource segment for the active locale session key.
     */
    protected function getActiveLocaleStorageScopeKey(): string
    {
        if (method_exists($this, 'getPageClass') && method_exists($this, 'getOwnerRecord')) {
            return sprintf(
                'relation-managers.%s.%s',
                $this->normalizeActiveLocaleStorageKeySegment($this->getPageClass()),
                $this->normalizeActiveLocaleStorageKeySegment(static::class),
            );
        }

        return sprintf(
            'resources.%s',
            $this->normalizeActiveLocaleStorageKeySegment(static::getResource()),
        );
    }

    /**
     * Normalize a class name for active locale session keys.
     */
    protected function normalizeActiveLocaleStorageKeySegment(string $key): string
    {
        return str($key)
            ->explode('\\')
            ->map(fn (string $segment): string => (string) str($segment)->kebab())
            ->implode('.');
    }

    /**
     * Determine whether the locale is available for translation.
     */
    protected function isTranslatableLocale(Locale $locale): bool
    {
        return in_array($locale, $this->getTranslatableLocales(), true);
    }

    /**
     * Get the Filament translatable plugin instance.
     */
    protected function getFilamentTranslatablePlugin(): FilamentTranslatablePlugin
    {
        /** @var FilamentTranslatablePlugin $plugin */
        $plugin = Filament::getPlugin('filament-translatable');

        return $plugin;
    }
}
