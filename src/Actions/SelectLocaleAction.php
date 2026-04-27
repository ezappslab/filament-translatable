<?php

namespace Infinity\FilamentTranslatable\Actions;

use Filament\Actions\SelectAction;
use Filament\Facades\Filament;
use Infinity\FilamentTranslatable\FilamentTranslatablePlugin;

class SelectLocaleAction extends SelectAction
{
    /**
     * Get the default Livewire property name used by the action.
     */
    public static function getDefaultName(): ?string
    {
        return 'activeLocale';
    }

    /**
     * Set up the action.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(function () {
            /** @var FilamentTranslatablePlugin $plugin */
            $plugin = Filament::getPlugin('filament-translatable');
            $options = [];

            foreach ($plugin->getLocales() as $locale) {
                $options[$locale->value] = $locale->name;
            }

            return $options;
        });
    }
}
