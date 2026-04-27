<?php

use Infinity\FilamentTranslatable\Enums\Locale;

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Here you may specify the locales that will be available for translation
    | within your application. These locales will be used to generate the
    | translation fields and language switches in the Filament UI.
    |
    */

    'locales' => [
        Locale::English,
        Locale::German,
        Locale::Spanish,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale that will be used when the
    | current locale is not available. This is useful for ensuring that
    | your application always has a default language to fall back to.
    |
    */

    'fallback_locale' => Locale::English,

];
