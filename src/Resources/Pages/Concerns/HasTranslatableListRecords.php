<?php

namespace Infinity\FilamentTranslatable\Resources\Pages\Concerns;

use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithActiveLocale;
use Infinity\FilamentTranslatable\Support\Concerns\InteractsWithTranslatableData;

/** @phpstan-ignore trait.unused */
trait HasTranslatableListRecords
{
    use InteractsWithActiveLocale;
    use InteractsWithTranslatableData;

    /**
     * Get the active table locale.
     */
    public function getActiveTableLocale(): ?string
    {
        return $this->getActiveSchemaLocale();
    }

    /**
     * Handle the event when the active locale is changed.
     */
    protected function activeLocaleChanged(): void
    {
        if (method_exists($this, 'flushCachedTableRecords')) {
            $this->flushCachedTableRecords();
        }

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
}
