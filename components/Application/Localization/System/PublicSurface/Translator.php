<?php

declare(strict_types=1);

namespace Avax\Components\Application\Localization\System\PublicSurface;

use Avax\Components\Application\Localization\System\Capabilities\TranslationLoading\TranslationLoaderInterface;
use Override;

final class Translator implements TranslatorInterface
{
    /** @var array<string, array<string, array>> Cached translations */
    private array $loaded = [];

    public function __construct(
        private readonly TranslationLoaderInterface $loader,
        private string                              $locale,
        private readonly string                     $fallback = 'en',
    ) {}

    #[Override]
    public function get(string $key, array $replace = [], ?string $locale = null) : string
    {
        $locale ??= $this->locale;

        [$namespace, $group, $item] = $this->parseKey($key);

        $line = $this->getLine($locale, $group, $item, $namespace);

        if ($line === null && $locale !== $this->fallback) {
            $line = $this->getLine($this->fallback, $group, $item, $namespace);
        }

        if ($line === null) {
            return $key;
        }

        return $this->makeReplacements($line, $replace);
    }

    private function parseKey(string $key) : array
    {
        $segments = explode('::', $key);

        if (count($segments) === 2) {
            $namespace = $segments[0];
            $remainder = $segments[1];
        } else {
            $namespace = null;
            $remainder = $key;
        }

        $parts = explode('.', $remainder);
        $group = array_shift($parts);
        $item  = implode('.', $parts);

        return [$namespace, $group, $item];
    }

    private function getLine(string $locale, string $group, string $item, ?string $namespace) : ?string
    {
        $this->load($locale, $group, $namespace);

        $key = ($namespace ? $namespace . '::' : '') . $group;

        return $this->loaded[$locale][$key][$item] ?? null;
    }

    private function load(string $locale, string $group, ?string $namespace) : void
    {
        $key = ($namespace ? $namespace . '::' : '') . $group;

        if (isset($this->loaded[$locale][$key])) {
            return;
        }

        $this->loaded[$locale][$key] = $this->loader->load($locale, $group, $namespace);
    }

    private function makeReplacements(string $line, array $replace) : string
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':' . $key, ':' . strtoupper($key), ':' . ucfirst($key)],
                [$value, strtoupper((string) $value), ucfirst((string) $value)],
                $line
            );
        }

        return $line;
    }

    #[Override]
    public function getLocale() : string
    {
        return $this->locale;
    }

    #[Override]
    public function setLocale(string $locale) : void
    {
        $this->locale = $locale;
    }

    #[Override]
    public function getFallback() : string
    {
        return $this->fallback;
    }
}
