<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Enums\Locale;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ListAnimals;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('offers only configured locales in their configured order with localized labels', function (): void {
    app()->setLocale('bg');
    Filament::getPlugin('filament-translatable')->locales([Locale::Bulgarian, Locale::English]);

    expect(SelectLocaleAction::make()->getOptions())->toBe([
        'bg' => 'Български',
        'en' => 'Английски',
    ]);
});

it('renders a disabled selector and escapes custom labels and placeholder text', function (): void {
    $html = SelectLocaleAction::make()
        ->disabled()
        ->label('<script>label</script>')
        ->placeholder('<choose>')
        ->options(['en' => '<English>'])
        ->toEmbeddedHtml();

    $document = new DOMDocument;
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);

    expect($xpath->query('//fieldset[@disabled]')->length)->toBe(1)
        ->and($xpath->query('//select[@disabled]')->length)->toBe(1)
        ->and($xpath->query('//option[@value=""]')->item(0)->textContent)->toBe('<choose>')
        ->and($xpath->query('//option[@value="en"]')->item(0)->textContent)->toContain('<English>')
        ->and($html)->toContain('&lt;script&gt;label&lt;/script&gt;')
        ->and($html)->not->toContain('<script>', '<English>', '<choose>');
});

it('marks the current locale as selected after a locale transition', function (): void {
    livewire(ListAnimals::class)
        ->call('setActiveLocale', 'bg')
        ->assertSeeHtml('value="bg" selected')
        ->assertDontSeeHtml('value="en" selected');
});
