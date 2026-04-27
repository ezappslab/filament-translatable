<?php

namespace Workbench\App\Filament\Resources\AnimalResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableCreateRecord;
use Workbench\App\Filament\Resources\AnimalResource;

class CreateAnimal extends CreateRecord
{
    use HasTranslatableCreateRecord;

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
