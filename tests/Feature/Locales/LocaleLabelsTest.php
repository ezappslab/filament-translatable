<?php

use Infinity\FilamentTranslatable\Enums\Locale;

it('localizes locale labels', function (string $applicationLocale, array $labels): void {
    app()->setLocale($applicationLocale);

    expect(Locale::English->getLabel())->toBe($labels['en'])
        ->and(Locale::Spanish->getLabel())->toBe($labels['es'])
        ->and(Locale::Portuguese->getLabel())->toBe($labels['pt'])
        ->and(Locale::German->getLabel())->toBe($labels['de'])
        ->and(Locale::Bulgarian->getLabel())->toBe($labels['bg']);
})->with([
    'English' => ['en', ['en' => 'English', 'es' => 'Spanish', 'pt' => 'Portuguese', 'de' => 'German', 'bg' => 'Bulgarian']],
    'Spanish' => ['es', ['en' => 'Inglés', 'es' => 'Español', 'pt' => 'Portugués', 'de' => 'Alemán', 'bg' => 'Búlgaro']],
    'Portuguese' => ['pt', ['en' => 'Inglês', 'es' => 'Espanhol', 'pt' => 'Português', 'de' => 'Alemão', 'bg' => 'Búlgaro']],
    'German' => ['de', ['en' => 'Englisch', 'es' => 'Spanisch', 'pt' => 'Portugiesisch', 'de' => 'Deutsch', 'bg' => 'Bulgarisch']],
    'Bulgarian' => ['bg', ['en' => 'Английски', 'es' => 'Испански', 'pt' => 'Португалски', 'de' => 'Немски', 'bg' => 'Български']],
]);

it('falls back to the enum name when no locale label exists', function (): void {
    app('translator')->addNamespace('filament-translatable', '/tmp/filament-translatable-missing-translations');
    app()->setLocale('zz');
    app('translator')->setFallback('zz');

    expect(Locale::Abkhazian->getLabel())->toBe('Abkhazian');
});
