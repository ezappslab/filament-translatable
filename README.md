# Filament Translatable

Filament Translatable adds locale-aware resource pages for Filament panels that use
[spatie/laravel-translatable](https://github.com/spatie/laravel-translatable).

The package provides:

- A Filament panel plugin for configuring available locales.
- Page concerns for create, edit, list, and view resource pages.
- A locale selector header action.
- A content driver that reads, writes, displays, and searches the active locale of Spatie translatable attributes.

## Requirements

- PHP 8.4 or higher
- Laravel 12 or 13
- Filament 5

## Installation

Install the package with Composer:

```bash
composer require ezappslab/filament-translatable
```

Run the install command:

```bash
php artisan filament-translatable:install
```

The installer can publish the configuration file and register the package service
provider in your application.

## Configuration

Configure the locales that should be available in your Filament panel:

```php
use Infinity\FilamentTranslatable\Enums\Locale;

return [
    'locales' => [
        Locale::English,
        Locale::German,
        Locale::Spanish,
    ],

    'fallback_locale' => Locale::English,
];
```

You can also configure locales directly when registering the plugin on a panel:

```php
use Filament\Panel;
use Filament\PanelProvider;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(
                FilamentTranslatablePlugin::make()
                    ->locales([
                        Locale::English,
                        Locale::German,
                    ])
                    ->fallbackLocale(Locale::English)
            );
    }
}
```

## Preparing Models

Use Spatie's `HasTranslations` trait on any model that has translated attributes.
The translated columns should be JSON-compatible columns in your database.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
```

Example migration columns:

```php
$table->json('name');
$table->json('description');
$table->boolean('is_active')->default(true);
```

## Using Translatable Resource Pages

Add the matching concern to each Filament resource page and add the locale selector
to the page header actions.

### List Page

```php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ListRecords;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableListRecords;

class ListProducts extends ListRecords
{
    use HasTranslatableListRecords;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SelectLocaleAction::make(),
        ];
    }
}
```

### Create Page

```php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableCreateRecord;

class CreateProduct extends CreateRecord
{
    use HasTranslatableCreateRecord;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SelectLocaleAction::make(),
        ];
    }
}
```

### Edit Page

```php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableEditRecord;

class EditProduct extends EditRecord
{
    use HasTranslatableEditRecord;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SelectLocaleAction::make(),
        ];
    }
}
```

### View Page

```php
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\Pages\Concerns\HasTranslatableViewRecord;

class ViewProduct extends ViewRecord
{
    use HasTranslatableViewRecord;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SelectLocaleAction::make(),
        ];
    }
}
```

## Resource Example

Once the page concerns are installed, build your resource form and table normally.
The package resolves translatable fields through the currently selected locale.

```php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required(),
            Textarea::make('description')
                ->required(),
            Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->searchable(),
            TextColumn::make('description'),
            IconColumn::make('is_active')
                ->boolean(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
```

When a user selects Bulgarian, `name` and `description` are read from and written to
the `bg` translation values. Non-translatable attributes, such as `is_active`, are
handled as normal model attributes.

## Relationship Fields

Translatable fields inside Filament relationship sections are also supported. Mark
the related model attributes as translatable and use the same page concerns on the
parent resource pages.

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('name')
            ->required(),
        TextInput::make('email')
            ->email()
            ->required(),
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
```

In this example, `profile.headline` and `profile.biography` can be translated per
locale when the `Profile` model uses Spatie's `HasTranslations` trait.

## Relation Managers

Use `HasTranslatableRelationManager` on Filament relation managers that manage
models with Spatie translatable attributes. Add `SelectLocaleAction` to the table
header actions so users can switch the active locale.

```php
namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Infinity\FilamentTranslatable\Actions\SelectLocaleAction;
use Infinity\FilamentTranslatable\Resources\RelationManagers\Concerns\HasTranslatableRelationManager;

class ProductsRelationManager extends RelationManager
{
    use HasTranslatableRelationManager;

    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required(),
            Textarea::make('description')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description'),
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
```

The relation manager uses its own active locale session key, scoped by panel, page,
and relation manager class.

## Scripts

- `composer lint`: Run Pint and PHPStan.
- `composer test`: Run Pest tests.
- `composer build`: Build workbench assets.
- `composer serve`: Serve the workbench application.

## Documentation

For more detailed information about the included tools, see [docs/tooling.md](docs/tooling.md).
