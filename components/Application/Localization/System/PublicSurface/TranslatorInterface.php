<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\PublicSurface;

interface TranslatorInterface
{
    /**
     * Translate the given key.
     *
     * @param array<string, mixed> $replace
     */
    public function get(string $key, array $replace = [], ?string $locale = null) : string;

    /**
     * Get the current locale.
     */
    public function getLocale() : string;

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale) : void;

    /**
     * Get the fallback locale.
     */
    public function getFallback() : string;
}
