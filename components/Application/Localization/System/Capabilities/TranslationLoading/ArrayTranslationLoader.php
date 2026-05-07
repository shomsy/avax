<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\Capabilities\TranslationLoading;

use Override;

final class ArrayTranslationLoader implements TranslationLoaderInterface
{
    /** @var array<string, array> */
    private array $messages = [];

    #[Override]
    public function load(string $locale, string $group, ?string $namespace = null) : array
    {
        $key = ($namespace ? $namespace . '::' : '') . $group;

        return $this->messages[$locale][$key] ?? [];
    }

    /**
     * Add messages to the loader.
     */
    public function addMessages(string $locale, string $group, array $messages, ?string $namespace = null) : void
    {
        $key                           = ($namespace ? $namespace . '::' : '') . $group;
        $this->messages[$locale][$key] = array_merge(
            $this->messages[$locale][$key] ?? [],
            $messages
        );
    }
}
