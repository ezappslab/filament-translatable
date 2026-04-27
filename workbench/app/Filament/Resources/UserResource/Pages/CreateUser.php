<?php

namespace Workbench\App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableCreateRecord;
use Workbench\App\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    use HasTranslatableCreateRecord;

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
