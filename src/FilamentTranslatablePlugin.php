<?php

namespace Infinity\FilamentTranslatable;

use Exception;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Infinity\FilamentTranslatable\Enums\Locale;

class FilamentTranslatablePlugin implements Plugin
{
    /**
     * @var array<int, Locale>
     */
    protected array $locales = [];

    /**
     * The fallback locale.
     */
    protected Locale $fallbackLocale;

    /**
     * Create a new plugin instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the plugin identifier.
     */
    public function getId(): string
    {
        return 'filament-translatable';
    }

    /**
     * Register the plugin.
     */
    public function register(Panel $panel): void
    {
        //
    }

    /**
     * Bootstrap the plugin.
     */
    public function boot(Panel $panel): void
    {
        if (blank($this->locales)) {
            $this->locales = config('filament-translatable.locales', [Locale::English]);
        }

        if (! isset($this->fallbackLocale)) {
            $this->fallbackLocale = config('filament-translatable.fallback_locale', Locale::English);
        }
    }

    /**
     * Set the locales available for translation.
     *
     * @param  array<int, Locale>  $locales
     */
    public function locales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Set the fallback locale.
     */
    public function fallbackLocale(Locale $locale): static
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Get the locales available for translation.
     *
     * @return array<int, Locale>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallbackLocale(): Locale
    {
        return $this->fallbackLocale;
    }

    /**
     * Get the default locale from the available locales.
     *
     * @throws Exception
     */
    public function getDefaultLocale(): Locale
    {
        $locales = $this->getLocales();

        if (blank($locales)) {
            throw new Exception('No locales defined for the filament-translatable plugin.');
        }

        return reset($locales);
    }
}
