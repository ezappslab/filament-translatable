<?php

namespace Workbench\App\Filament\Resources\AnimalResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableListRecords;
use Workbench\App\Filament\Resources\AnimalResource;

class ListAnimals extends ListRecords
{
    use HasTranslatableListRecords;

    protected static string $resource = AnimalResource::class;

    /**
     * Get the page header actions.
     */
    protected function getHeaderActions(): array
    {
        return [
            SelectLocaleAction::make(),
        ];
    }
}
