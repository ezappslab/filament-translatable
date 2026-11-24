<?php

namespace Infinity\FilamentTranslatable\Resources\Pages\Concerns;

use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Database\Eloquent\Model;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithActiveLocale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithTranslatableData;

/**
 * Display translated form state and apply the active locale when accessing the record.
 *
 * @phpstan-ignore trait.unused
 */
trait HasTranslatableViewRecord
{
    use InteractsWithActiveLocale;
    use InteractsWithTranslatableData;

    /**
     * Get the record with the active locale applied to its loaded relationships.
     */
    #[Unexposed]
    public function getRecord(): Model
    {
        $record = parent::getRecord();

        return $this->makeFilamentTranslatableContentDriver()?->setRecordLocale($record) ?? $record;
    }

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
     * Handle the event when the active locale is changed.
     */
    protected function activeLocaleChanged(): void
    {
        if (isset($this->record) && (! $this->hasInfolist())) {
            $this->refreshTranslatableFormStateForActiveLocale($this->getRecord());
        }
    }
}
