<?php

namespace Infinity\FilamentTranslatable\Resources\Pages\Concerns;

use Illuminate\Database\Eloquent\Model;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithActiveLocale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithTranslatableData;

/** @phpstan-ignore trait.unused */
trait HasTranslatableEditRecord
{
    use InteractsWithActiveLocale;
    use InteractsWithTranslatableData;

    /**
     * Fill the form with record data for the active locale.
     *
     * @param  array<string, mixed>  $extraData
     */
    protected function fillFormWithDataAndCallHooks(Model $record, array $extraData = []): void
    {
        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill([
            ...$this->getTranslatableRecordAttributesToArray($record),
            ...$extraData,
        ]);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }

    /**
     * Refresh form data for the active locale.
     *
     * @param  array<string>  $statePaths
     */
    public function refreshFormData(array $statePaths): void
    {
        $this->form->fillPartially(
            $this->mutateFormDataBeforeFill($this->getTranslatableRecordAttributesToArray($this->getRecord())),
            $statePaths,
        );
    }

    /**
     * Handle record updates for the active locale.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->updateTranslatableRecord($record, $this->mergeCachedTranslationsIntoData($data));
    }

    /**
     * Handle the event when the active locale is changed.
     */
    protected function activeLocaleChanged(): void
    {
        if (isset($this->record)) {
            $this->refreshTranslatableFormStateForActiveLocale($this->getRecord());
        }
    }
}
