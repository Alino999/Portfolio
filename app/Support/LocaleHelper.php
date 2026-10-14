<?php

namespace App\Support;

class LocaleHelper
{
    public static function text(mixed $value, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if (is_array($value)) {
            if (isset($value[$locale])) {
                return (string) $value[$locale];
            }

            if (isset($value['fr'])) {
                return (string) $value['fr'];
            }

            return (string) reset($value);
        }

        return (string) $value;
    }
}
