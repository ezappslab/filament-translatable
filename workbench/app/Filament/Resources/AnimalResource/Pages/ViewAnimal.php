<?php

namespace Workbench\App\Filament\Resources\AnimalResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableViewRecord;
use Workbench\App\Filament\Resources\AnimalResource;

class ViewAnimal extends ViewRecord
{
    use HasTranslatableViewRecord;

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
