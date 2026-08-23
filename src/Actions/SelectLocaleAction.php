<?php

namespace Infinity\FilamentTranslatable\Actions;

use Filament\Actions\SelectAction;
use Filament\Facades\Filament;
use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
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
                $options[$locale->value] = $locale->getLabel();
            }

            return $options;
        });
    }

    /**
     * Render the selector with guards against overlapping schema updates.
     */
    public function toEmbeddedHtml(): string
    {
        $id = $this->getId();
        $isDisabled = $this->isDisabled();
        $livewire = $this->getLivewire();
        $activeLocale = $livewire && method_exists($livewire, 'getActiveLocale')
            ? $livewire->getActiveLocale()->value
            : null;

        $inputWrapperAttributes = (new FilamentComponentAttributeBag)
            ->merge([
                'disabled' => $isDisabled,
                'wire:loading.attr' => 'disabled',
            ], escape: false)
            ->class([
                'fi-input-wrp',
                'fi-disabled' => $isDisabled,
            ]);

        $inputAttributes = (new FilamentComponentAttributeBag)
            ->merge([
                'disabled' => $isDisabled,
                'id' => $id,
                'wire:change' => 'setActiveLocale($event.target.value)',
            ], escape: false)
            ->class([
                'fi-select-input',
            ]);

        ob_start(); ?>

        <div class="fi-ac-select-action">
            <label for="<?= $id ?>" class="fi-sr-only">
                <?= e($this->getLabel()) ?>
            </label>

            <fieldset <?= $inputWrapperAttributes->toHtml() ?>>
                <select <?= $inputAttributes->toHtml() ?>>
                    <?php if (($placeholder = $this->getPlaceholder()) !== null) { ?>
                        <option value=""><?= e($placeholder) ?></option>
                    <?php } ?>

                    <?php foreach ($this->getOptions() as $value => $label) { ?>
                        <option value="<?= e($value) ?>" <?= ((string) $value === $activeLocale) ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php } ?>
                </select>
            </fieldset>
        </div>

        <?php return ob_get_clean();
    }
}
