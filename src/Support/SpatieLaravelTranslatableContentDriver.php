<?php

namespace Infinity\FilamentTranslatable\Support;

use Filament\Facades\Filament;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Infinity\FilamentTranslatable\Enums\Locale;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;

class SpatieLaravelTranslatableContentDriver implements TranslatableContentDriver
{
    /**
     * @var array<class-string<Model>, array<string, bool>>
     */
    protected static array $translatableAttributeCache = [];

    protected Locale $activeLocale;

    /**
     * Create a new content driver instance.
     */
    public function __construct(string $activeLocale)
    {
        $this->activeLocale = Locale::tryFrom($activeLocale)
            ?? $this->getFilamentTranslatablePlugin()->getFallbackLocale();
    }

    /**
     * Determine whether the attribute is translatable.
     *
     * @param  class-string<Model>  $model
     */
    public function isAttributeTranslatable(string $model, string $attribute): bool
    {
        if (isset(static::$translatableAttributeCache[$model][$attribute])) {
            return static::$translatableAttributeCache[$model][$attribute];
        }

        $record = app($model);

        if (! method_exists($record, 'isTranslatableAttribute')) {
            return static::$translatableAttributeCache[$model][$attribute] = false;
        }

        return static::$translatableAttributeCache[$model][$attribute] = $record->isTranslatableAttribute($attribute);
    }

    /**
     * Get the record attributes for the active locale.
     *
     * @return array<string, mixed>
     */
    public function getRecordAttributesToArray(Model $record): array
    {
        $data = $record->attributesToArray();

        if (! method_exists($record, 'getTranslation')) {
            return $data;
        }

        foreach ($this->getTranslatableAttributes($record) as $attribute) {
            if (! array_key_exists($attribute, $data)) {
                continue;
            }

            $data[$attribute] = $record->getTranslation($attribute, $this->activeLocale->value, useFallbackLocale: false);
        }

        return $data;
    }

    /**
     * Make a new record for the active locale.
     *
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $data
     */
    public function makeRecord(string $model, array $data): Model
    {
        $record = new $model;

        return $this->fillRecord($record, $data);
    }

    /**
     * Set the active locale on the record.
     */
    public function setRecordLocale(Model $record): Model
    {
        if (method_exists($record, 'setLocale')) {
            $record->setLocale($this->activeLocale->value);
        }

        return $record;
    }

    /**
     * Update the record for the active locale.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRecord(Model $record, array $data): Model
    {
        $this->fillRecord($record, $data);

        $record->save();

        return $record;
    }

    /**
     * Apply a search constraint for the active locale.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function applySearchConstraintToQuery(
        Builder $query,
        string $column,
        string $search,
        string $whereClause,
        ?bool $isSearchForcedCaseInsensitive = null,
    ): Builder {
        $searchColumn = (string) str($column)->replace('.', '->').'->'.$this->activeLocale->value;

        return $query->{$whereClause}(
            $searchColumn,
            'like',
            "%{$search}%",
        );
    }

    /**
     * Fill the record attributes for the active locale.
     *
     * @param  array<string, mixed>  $data
     */
    protected function fillRecord(Model $record, array $data): Model
    {
        $translatableAttributes = $this->getTranslatableAttributeMap($record);
        $canSetTranslation = method_exists($record, 'setTranslation');

        foreach ($data as $attribute => $value) {
            if (isset($translatableAttributes[$attribute])) {
                if (! $canSetTranslation) {
                    continue;
                }

                if ($this->isTranslationMap($value)) {
                    foreach ($value as $locale => $translation) {
                        $record->setTranslation($attribute, $locale, $translation);
                    }

                    continue;
                }

                $record->setTranslation($attribute, $this->activeLocale->value, $value);

                continue;
            }

            $record->setAttribute($attribute, $value);
        }

        return $record;
    }

    /**
     * Determine whether a value contains translations keyed by configured locale.
     */
    protected function isTranslationMap(mixed $value): bool
    {
        if ((! is_array($value)) || blank($value)) {
            return false;
        }

        $availableLocales = array_fill_keys(array_map(
            fn (Locale $locale): string => $locale->value,
            $this->getFilamentTranslatablePlugin()->getLocales(),
        ), true);

        return array_diff_key($value, $availableLocales) === [];
    }

    /**
     * Get the translatable attributes for the record.
     *
     * @return array<string>
     */
    protected function getTranslatableAttributes(Model $record): array
    {
        if (! method_exists($record, 'getTranslatableAttributes')) {
            return [];
        }

        return array_keys($this->getTranslatableAttributeMap($record));
    }

    /**
     * Get the translatable attribute lookup map for the record.
     *
     * @return array<string, true>
     */
    protected function getTranslatableAttributeMap(Model $record): array
    {
        if (! method_exists($record, 'getTranslatableAttributes')) {
            return [];
        }

        return array_fill_keys(
            $record->getTranslatableAttributes(),
            true,
        );
    }

    /**
     * Get the Filament translatable plugin instance.
     */
    protected function getFilamentTranslatablePlugin(): FilamentTranslatablePlugin
    {
        /** @var FilamentTranslatablePlugin $plugin */
        $plugin = Filament::getPlugin('filament-translatable');

        return $plugin;
    }
}
