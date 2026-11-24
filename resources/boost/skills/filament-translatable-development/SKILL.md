---
name: filament-translatable-development
description: Configure and integrate ezappslab/filament-translatable in Filament applications, including translated models, resource pages, locale selection, relationship sections, and relation managers. Use when adding or troubleshooting this package's locale-aware workflows.
---

# Filament Translatable Development

## Scope and discovery

This skill covers `ezappslab/filament-translatable`, whose PHP namespace is
`Infinity\FilamentTranslatable`. It targets PHP 8.4+, Laravel 12 or 13, and Filament 5.
Inspect installed versions and existing panel, model, resource, and page code before
editing. Preserve the application's conventions and existing actions.

Use the installed package's `README.md` for full integration examples and its source
for behavior specific to that version. When available, use Boost's documentation
search for version-matched Filament, Laravel, and Spatie APIs. Do not substitute
another translatable plugin's classes or setup for this package's API.

## Configure the panel

For a new installation, the package commands are:

```bash
composer require ezappslab/filament-translatable
php artisan filament-translatable:install
```

The installer can publish configuration and register the service provider. Inspect
existing setup before repeating it. Register the plugin on the intended panel:

```php
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;

// In the panel provider's existing panel() method:
$panel->plugin(
    FilamentTranslatablePlugin::make()
        ->locales([Locale::English, Locale::Bulgarian])
        ->fallbackLocale(Locale::English)
);
```

Alternatively, set `locales` and `fallback_locale` in
`config/filament-translatable.php`. Both use this package's `Locale` enum; `locales`
is a nonempty array of enum cases. Explicit plugin settings take precedence over
configuration. The first configured locale is the default selection; the fallback
setting does not choose the initial selection.

## Prepare translated models

Translated columns must support JSON. Declare translated attributes on each model
with Spatie's trait and class attribute:

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\Attributes\Translatable;
use Spatie\Translatable\HasTranslations;

#[Translatable('name', 'description')]
class Product extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'description', 'is_active'];
}
```

Use JSON migration columns such as `$table->json('name')`. Ordinary attributes,
such as `is_active`, remain ordinary model fields. Prepare related models in the
same way when their attributes need translation.

## Wire resource pages

Add the matching concern to each resource page that needs translation:

| Filament page base class | Concern in `Infinity\FilamentTranslatable\Resources\Pages\Concerns` |
| --- | --- |
| `ListRecords` | `HasTranslatableListRecords` |
| `CreateRecord` | `HasTranslatableCreateRecord` |
| `EditRecord` | `HasTranslatableEditRecord` |
| `ViewRecord` | `HasTranslatableViewRecord` |

Import `Infinity\FilamentTranslatable\Actions\SelectLocaleAction` and add
`SelectLocaleAction::make()` to each page's existing `getHeaderActions()` array.
Keep create, delete, and other application actions already present.

Build resource schemas and tables normally with Filament 5's
`Filament\Schemas\Schema` and `Filament\Tables\Table`. Use ordinary field names
such as `TextInput::make('name')` and `TextColumn::make('name')->searchable()`.
The concerns supply the content driver; translated fields do not need locale
suffixes such as `name.en` or a manually registered replacement driver.

When reviewing generated forms, choose the input based on the attribute's meaning.
Filament may generate a `Textarea` because a translated column uses JSON storage.
Replace it with `TextInput` for single-line values such as `name`, `title`, or
`headline`. Keep `Textarea` for attributes that imply multiline content, such as
`description`, `biography`, or `notes`. Preserve existing validation and field
configuration when changing the component.

For programmatic changes, call `setActiveLocale(Locale::Bulgarian)` or
`setActiveLocale('bg')`. This validates the selection, caches outgoing form edits,
and refreshes dependent state. Assigning the property directly in PHP bypasses
that transition workflow.

## Relationships

For relationship sections, use the parent page concerns above and prepare the
related model's translated attributes. A normal Filament relationship section,
such as `Section::make('Profile')->relationship('profile')->schema([...])`,
provides the model context for its fields.

For relation managers:

- Add `Infinity\FilamentTranslatable\Resources\RelationManagers\Concerns\HasTranslatableRelationManager`
  to the relation manager class.
- Add `SelectLocaleAction::make()` to the table's `headerActions()` alongside its
  existing actions. Keep normal Filament create and edit actions.
- Treat its locale selection as independent: session storage is scoped by panel,
  owning page class, and relation manager class. Resource pages share their
  selection within a panel and resource.

The active locale propagates recursively through already-loaded relationships,
including collections and nested relationships, even through a non-translatable
parent. This traversal does not load relationships or issue queries. Relationships
loaded later receive the locale on the next content-driver processing pass or
edit/view page `getRecord()` call. Load needed relationships explicitly when an
application callback depends on them.

## Translation behavior and verification

- Create and edit pages cache translated form values when switching locales and
  merge entered locale values on save. Preserve these hooks when customizing page
  persistence; inspect the installed concern before overriding its methods.
- Driver updates preserve existing translations omitted from submitted data.
  Non-translatable fields continue to use ordinary model attributes.
- Form extraction reads the active translation without substituting a fallback
  translation when it is missing. Search constraints target the active locale.
- The driver recognizes a nonempty array as a translation map only when all keys
  are configured locale codes. Empty arrays, lists, and other structured arrays
  are treated as values for the active locale.

Verify the changed workflow using the application's existing test setup. Useful
integration checks cover switching locales with unsaved edits, saving translations
without overwriting other locales, keeping ordinary fields intact, and displaying
or searching the selected locale. Include relationship section or relation manager
checks when those integrations change. With Pest Livewire, switch via
`->call('setActiveLocale', 'bg')` and assert persisted translation maps as well as
visible form state. Run the relevant existing tests and formatting checks.
