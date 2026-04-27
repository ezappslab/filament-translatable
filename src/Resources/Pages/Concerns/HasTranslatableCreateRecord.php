<?php

namespace Infinity\FilamentTranslatable\Resources\Pages\Concerns;

use Illuminate\Database\Eloquent\Model;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithActiveLocale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithTranslatableData;

/** @phpstan-ignore trait.unused */
trait HasTranslatableCreateRecord
{
    use InteractsWithActiveLocale;
    use InteractsWithTranslatableData;

    /**
     * Handle record creation for the active locale.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = $this->makeTranslatableRecord($this->getModel(), $data);

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        $record->save();

        return $record;
    }

    /**
     * Handle the event when the active locale is changed.
     */
    protected function activeLocaleChanged(): void
    {
        $this->refreshTranslatableFormStateForActiveLocale();
    }
}
