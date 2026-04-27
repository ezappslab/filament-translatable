<?php

namespace Workbench\App\Filament\Resources;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\AnimalResource\Pages\CreateAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\EditAnimal;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ListAnimals;
use Workbench\App\Filament\Resources\AnimalResource\Pages\ViewAnimal;
use Workbench\App\Models\Animal;

class AnimalResource extends Resource
{
    protected static ?string $model = Animal::class;

    protected static bool $shouldSkipAuthorization = true;

    /**
     * Configure the resource form.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->required(),
                Toggle::make('is_active'),
            ]);
    }

    /**
     * Configure the resource table.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('description'),
                IconColumn::make('is_active')
                    ->boolean(),
            ]);
    }

    /**
     * Get the resource pages.
     *
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAnimals::route('/'),
            'create' => CreateAnimal::route('/create'),
            'view' => ViewAnimal::route('/{record}'),
            'edit' => EditAnimal::route('/{record}/edit'),
        ];
    }
}
