<?php

namespace Workbench\App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableEditRecord;
use Workbench\App\Filament\Resources\UserResource;

class EditUser extends EditRecord
{
    use HasTranslatableEditRecord;

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
