<?php

namespace Workbench\App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableViewRecord;
use Workbench\App\Filament\Resources\UserResource;

class ViewUser extends ViewRecord
{
    use HasTranslatableViewRecord;

    protected static string $resource = UserResource::class;

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
