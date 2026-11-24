<?php

namespace Tests\Fixtures;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithTranslatableData;
use Livewire\Component;
use Workbench\App\Models\Animal;

class TranslatableForm extends Component implements HasSchemas
{
    use InteractsWithSchemas, InteractsWithTranslatableData {
        InteractsWithTranslatableData::getFilamentTranslatableContentDriver as protected defaultContentDriver;
        InteractsWithTranslatableData::makeFilamentTranslatableContentDriver insteadof InteractsWithSchemas;
        InteractsWithTranslatableData::getActiveSchemaLocale insteadof InteractsWithSchemas;
        getFormFieldStatePaths as public;
        getTranslatableFormStatePaths as public;
        getNonTranslatableFormStatePaths as public;
        mergeCachedTranslationsIntoData as public;
        getTranslatableRecordAttributesToArray as public;
        makeTranslatableRecord as public;
        updateTranslatableRecord as public;
    }

    public array $data = [];

    public Locale $activeLocale = Locale::English;

    public bool $driverEnabled = true;

    public function getActiveLocale(): Locale
    {
        return $this->activeLocale;
    }

    public function getTranslatableLocales(): array
    {
        return [Locale::English, Locale::Bulgarian];
    }

    public function getFilamentTranslatableContentDriver(): ?string
    {
        return $this->driverEnabled ? $this->defaultContentDriver() : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model(Animal::class)
            ->components([
                TextInput::make('name'),
                TextInput::make('description')->hidden(),
                Toggle::make('is_active'),
            ]);
    }
}
