<?php

namespace Workbench\App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\UserResource\Pages\CreateUser;
use Workbench\App\Filament\Resources\UserResource\Pages\EditUser;
use Workbench\App\Filament\Resources\UserResource\Pages\ListUsers;
use Workbench\App\Filament\Resources\UserResource\Pages\ViewUser;
use Workbench\App\Filament\Resources\UserResource\RelationManagers\AnimalsRelationManager;
use Workbench\App\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

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
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Section::make('Profile')
                    ->relationship('profile')
                    ->schema([
                        TextInput::make('headline')
                            ->required(),
                        TextInput::make('biography')
                            ->required(),
                        Toggle::make('is_public'),
                    ]),
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
                TextColumn::make('email'),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Get the resource relation managers.
     *
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            AnimalsRelationManager::class,
        ];
    }
}
