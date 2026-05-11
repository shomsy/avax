<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\Capabilities\TranslationLoading;

interface TranslationLoaderInterface
{
    /**
     * Load the messages for the given locale and group.
     *
     * @return array<string, string>
     */
    public function load(string $locale, string $group, string|null $namespace = null) : array;
}
