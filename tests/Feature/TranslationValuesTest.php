<?php

use Infinity\FilamentTranslatable\Support\SpatieLaravelTranslatableContentDriver;
use Workbench\App\Models\Animal;

it('stores arrays that are not configured locale maps as a single translated value', function (array $value): void {
    $record = (new SpatieLaravelTranslatableContentDriver('bg'))->makeRecord(Animal::class, [
        'description' => $value,
    ]);

    expect($record->getTranslations('description'))->toBe(['bg' => $value]);
})->with([
    'empty array' => [[]],
    'list' => [['Forest', 'River']],
    'structured value' => [['title' => 'Forest']],
    'unconfigured locale key' => [['de' => 'Wald']],
]);
