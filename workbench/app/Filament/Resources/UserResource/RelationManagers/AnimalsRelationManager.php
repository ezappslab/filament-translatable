<?php

namespace Workbench\App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\RelationManagers\Concerns\HasTranslatableRelationManager;

class AnimalsRelationManager extends RelationManager
{
    use HasTranslatableRelationManager;

    protected static string $relationship = 'animals';

    /**
     * Configure the relation manager form.
     */
    public function form(Schema $schema): Schema
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
     * Configure the relation manager table.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description'),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->headerActions([
                SelectLocaleAction::make(),
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
