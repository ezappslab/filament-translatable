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

it('falls back to the enum name when no locale label exists', function (): void {
    app('translator')->addNamespace('filament-translatable', '/tmp/filament-translatable-missing-translations');
    app()->setLocale('zz');
    app('translator')->setFallback('zz');

    expect(Locale::Abkhazian->getLabel())->toBe('Abkhazian');
});

it('registers its configuration and translation publishing groups', function (): void {
    $provider = FilamentTranslatableServiceProvider::class;

    expect(config('filament-translatable.locales'))->toBeArray()->not->toBeEmpty()
        ->and($provider::pathsToPublish($provider, 'filament-translatable-config'))->not->toBeEmpty()
        ->and($provider::pathsToPublish($provider, 'filament-translatable-translations'))->not->toBeEmpty();
});
