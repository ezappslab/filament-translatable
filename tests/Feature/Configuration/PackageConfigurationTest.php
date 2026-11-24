<?php

use Filament\Panel;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;
use Infinity\FilamentTranslatable\FilamentTranslatableServiceProvider;

it('uses English defaults when configuration keys are absent', function (): void {
    config()->offsetUnset('filament-translatable');
    $plugin = FilamentTranslatablePlugin::make();
    $plugin->boot(Panel::make());

    expect($plugin->getId())->toBe('filament-translatable')
        ->and($plugin->getLocales())->toBe([Locale::English])
        ->and($plugin->getDefaultLocale())->toBe(Locale::English)
        ->and($plugin->getFallbackLocale())->toBe(Locale::English);
});

it('registers its configuration and translation publishing groups', function (): void {
    $provider = FilamentTranslatableServiceProvider::class;

    expect(config('filament-translatable.locales'))->toBeArray()->not->toBeEmpty()
        ->and($provider::pathsToPublish($provider, 'filament-translatable-config'))->not->toBeEmpty()
        ->and($provider::pathsToPublish($provider, 'filament-translatable-translations'))->not->toBeEmpty();
});

it('exposes its configured locales and fallback locale', function (): void {
    $plugin = FilamentTranslatablePlugin::make()
        ->locales([Locale::Bulgarian, Locale::English])
        ->fallbackLocale(Locale::Bulgarian);

    expect($plugin->getLocales())->toBe([Locale::Bulgarian, Locale::English])
        ->and($plugin->getDefaultLocale())->toBe(Locale::Bulgarian)
        ->and($plugin->getFallbackLocale())->toBe(Locale::Bulgarian);
});

it('fails clearly when no default locale is available', function (): void {
    FilamentTranslatablePlugin::make()
        ->locales([])
        ->getDefaultLocale();
})->throws(Exception::class, 'No locales defined for the filament-translatable plugin.');

it('boots plugin locales and fallback from configuration', function (): void {
    config()->set('filament-translatable.locales', [Locale::Bulgarian, Locale::English]);
    config()->set('filament-translatable.fallback_locale', Locale::Bulgarian);
    $plugin = FilamentTranslatablePlugin::make();
    $plugin->boot(Panel::make());

    expect($plugin->getLocales())->toBe([Locale::Bulgarian, Locale::English])
        ->and($plugin->getFallbackLocale())->toBe(Locale::Bulgarian);
});

it('preserves explicit plugin settings when booting', function (): void {
    config()->set('filament-translatable.locales', [Locale::German]);
    config()->set('filament-translatable.fallback_locale', Locale::German);
    $plugin = FilamentTranslatablePlugin::make()
        ->locales([Locale::Bulgarian, Locale::English])
        ->fallbackLocale(Locale::English);
    $plugin->boot(Panel::make());

    expect($plugin->getLocales())->toBe([Locale::Bulgarian, Locale::English])
        ->and($plugin->getFallbackLocale())->toBe(Locale::English);
});
