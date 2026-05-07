<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\Configuration;

readonly class LocalizationConfig
{
    /** @param list<string> $supportedLocales */
    public function __construct(
        public string $locale = 'en',
        public string $fallbackLocale = 'en',
        public array  $supportedLocales = ['en'],
    ) {}

    public static function defaults() : self
    {
        return new self();
    }
}
