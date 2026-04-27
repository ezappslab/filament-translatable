<?php

namespace Infinity\FilamentTranslatable\Support\Concerns;

use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\Support\SpatieLaravelTranslatableContentDriver;

/** @phpstan-ignore trait.unused */
trait InteractsWithTranslatableData
{
    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    public array $translatableFormStateByLocale = [];

    /**
     * @var array<string, array<string, bool>>
     */
    protected array $formFieldStatePathsByTranslatabilityCache = [];

    /**
     * @var array<string, TranslatableContentDriver>
     */
    protected array $filamentTranslatableContentDriverCache = [];

    /**
     * @var array<string, array<string>>
     */
    protected array $formFieldStatePathsCache = [];

    /**
     * @var array<string, array<string>>
     */
    protected array $translatableFormStatePathsCache = [];

    /**
     * @var array<string, array<string, true>>
     */
    protected array $translatableFormStatePathLookupCache = [];

    /**
     * @var array<string, array<string>>
     */
    protected array $nonTranslatableFormStatePathsCache = [];

    /**
     * Get the Filament translatable content driver class.
     *
     * @return class-string<SpatieLaravelTranslatableContentDriver>
     */
    public function getFilamentTranslatableContentDriver(): ?string
    {
        return SpatieLaravelTranslatableContentDriver::class;
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        $driver = $this->getFilamentTranslatableContentDriver();

        if (! $driver) {
            return null;
        }

        $activeLocale = $this->getActiveSchemaLocale() ?? app()->getLocale();
        $cacheKey = "{$driver}:{$activeLocale}";

        return $this->filamentTranslatableContentDriverCache[$cacheKey] ??= app($driver, [
            'activeLocale' => $activeLocale,
        ]);
    }

    /**
     * Get the active schema locale.
     */
    public function getActiveSchemaLocale(): ?string
    {
        return $this->getActiveLocale()->value;
    }

    /**
     * Get the record attributes for the active locale.
     *
     * @return array<string, mixed>
     */
    protected function getTranslatableRecordAttributesToArray(Model $record): array
    {
        return $this->makeFilamentTranslatableContentDriver()
            ?->getRecordAttributesToArray($record)
            ?? $record->attributesToArray();
    }

    /**
     * Make a new translatable record.
     *
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $data
     */
    protected function makeTranslatableRecord(string $model, array $data): Model
    {
        return $this->makeFilamentTranslatableContentDriver()
            ?->makeRecord($model, $data)
            ?? new $model($data);
    }

    /**
     * Update a translatable record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateTranslatableRecord(Model $record, array $data): Model
    {
        return $this->makeFilamentTranslatableContentDriver()
            ?->updateRecord($record, $data)
            ?? tap($record)->update($data);
    }

    /**
     * Cache the current translatable form field values before switching locale.
     *
     * @param  Locale  $locale  The locale to cache state for.
     */
    public function cacheTranslatableFormStateForLocale(Locale $locale): void
    {
        foreach ($this->getTranslatableFormSchemas() as $schemaName => $schema) {
            $translatableStatePaths = $this->getTranslatableFormStatePaths($schema);

            if (blank($translatableStatePaths)) {
                continue;
            }

            $rawState = $schema->getRawState();

            $this->translatableFormStateByLocale[$locale->value][$schemaName] = array_intersect_key(
                Arr::dot($rawState),
                $this->getTranslatableFormStatePathLookup($schema),
            );
        }
    }

    /**
     * Rehydrate translatable form fields for the active locale.
     *
     * @param  Model|null  $record  The record to pull data from, if any.
     */
    protected function refreshTranslatableFormStateForActiveLocale(?Model $record = null): void
    {
        $activeLocale = $this->getActiveLocale();
        $recordState = null;

        foreach ($this->getTranslatableFormSchemas() as $schemaName => $schema) {
            $translatableStatePaths = $this->getTranslatableFormStatePaths($schema);

            if (blank($translatableStatePaths)) {
                continue;
            }

            $cachedState = $this->translatableFormStateByLocale[$activeLocale->value][$schemaName] ?? null;

            if ($cachedState !== null) {
                $schema->fillPartially(Arr::undot($cachedState), $translatableStatePaths);

                continue;
            }

            if (! $record) {
                $schema->fillPartially(
                    Arr::undot(array_fill_keys($translatableStatePaths, null)),
                    $translatableStatePaths,
                );

                continue;
            }

            if ($recordState === null) {
                $recordState = $this->getTranslatableRecordAttributesToArray($record);

                if (method_exists($this, 'mutateFormDataBeforeFill')) {
                    $recordState = $this->mutateFormDataBeforeFill($recordState);
                }
            }

            $currentState = Arr::dot($schema->getRawState());
            $nonTranslatableStatePaths = $this->getNonTranslatableFormStatePaths($schema);

            $mergedState = Arr::undot(array_merge(
                Arr::only($currentState, $nonTranslatableStatePaths),
                Arr::only($recordState, $translatableStatePaths),
            ));

            $schema->fill($mergedState);
        }
    }

    /**
     * Get the state paths for all translatable fields in a schema.
     *
     * @param  mixed  $schema  The schema to inspect.
     * @return array<string>
     */
    protected function getTranslatableFormStatePaths(mixed $schema): array
    {
        $schemaKey = spl_object_hash($schema);

        return $this->translatableFormStatePathsCache[$schemaKey] ??= array_keys(array_filter(
            $this->getFormFieldStatePathsByTranslatability($schema),
        ));
    }

    /**
     * Get all form field state paths for a schema.
     *
     * @param  mixed  $schema  The schema to inspect.
     * @return array<string>
     */
    protected function getFormFieldStatePaths(mixed $schema): array
    {
        $schemaKey = spl_object_hash($schema);

        return $this->formFieldStatePathsCache[$schemaKey] ??= array_keys(
            $this->getFormFieldStatePathsByTranslatability($schema),
        );
    }

    /**
     * Get the state path lookup for all translatable fields in a schema.
     *
     * @param  mixed  $schema  The schema to inspect.
     * @return array<string, true>
     */
    protected function getTranslatableFormStatePathLookup(mixed $schema): array
    {
        $schemaKey = spl_object_hash($schema);

        return $this->translatableFormStatePathLookupCache[$schemaKey] ??= array_fill_keys(
            $this->getTranslatableFormStatePaths($schema),
            true,
        );
    }

    /**
     * Get the state paths for all non-translatable fields in a schema.
     *
     * @param  mixed  $schema  The schema to inspect.
     * @return array<string>
     */
    protected function getNonTranslatableFormStatePaths(mixed $schema): array
    {
        $schemaKey = spl_object_hash($schema);

        return $this->nonTranslatableFormStatePathsCache[$schemaKey] ??= array_keys(array_filter(
            $this->getFormFieldStatePathsByTranslatability($schema),
            fn (bool $isTranslatable): bool => ! $isTranslatable,
        ));
    }

    /**
     * Get form field state paths and their translatability status.
     *
     * @param  mixed  $schema  The schema to inspect.
     * @return array<string, bool>
     */
    protected function getFormFieldStatePathsByTranslatability(mixed $schema): array
    {
        $schemaKey = spl_object_hash($schema);

        if (isset($this->formFieldStatePathsByTranslatabilityCache[$schemaKey])) {
            return $this->formFieldStatePathsByTranslatabilityCache[$schemaKey];
        }

        $schemaStatePath = $schema->getStatePath();
        $contentDriver = $this->makeFilamentTranslatableContentDriver();
        $statePaths = [];

        foreach ($schema->getFlatFields(withHidden: true) as $field) {
            $statePath = $field->getStatePath();

            if (blank($statePath)) {
                continue;
            }

            if (filled($schemaStatePath)) {
                $statePath = (string) str($statePath)->after("{$schemaStatePath}.");
            }

            $model = $field->getModel();
            $attribute = $field->getStatePath(isAbsolute: false);

            $statePaths[$statePath] = (bool) (
                filled($model)
                && filled($attribute)
                && $contentDriver?->isAttributeTranslatable($model, $attribute)
            );
        }

        return $this->formFieldStatePathsByTranslatabilityCache[$schemaKey] = $statePaths;
    }

    /**
     * Get all translatable form schemas.
     *
     * @return array<string, mixed>
     */
    protected function getTranslatableFormSchemas(): array
    {
        $schemas = [];

        if (isset($this->form)) {
            $schemas['form'] = $this->form;
        }

        if (method_exists($this, 'getMountedActionSchemaName')) {
            $mountedActionSchemaName = $this->getMountedActionSchemaName();

            if (filled($mountedActionSchemaName) && isset($this->{$mountedActionSchemaName})) {
                $schemas[$mountedActionSchemaName] = $this->{$mountedActionSchemaName};
            }
        }

        return $schemas;
    }
}
