<?php

namespace App\Support;

/** The interface languages Qualora offers. */
final class Locales
{
    public const COOKIE = 'locale';

    /** @var array<string, string> code => native name */
    public const SUPPORTED = [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ];

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::SUPPORTED);
    }
}
