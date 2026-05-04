<?php

declare(strict_types=1);

namespace Avax\Components\Config\System\Capabilities\Runtime;

use Avax\Components\Config\System\Capabilities\Repository\ConfigurationRepository;

/**
 * RuntimeConfig handles per-request overrides in long-lived processes (workers).
 */
final class RuntimeConfig
{
    private array $overrides = [];

    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
    ) {}

    public function get(string $key, mixed $default = null) : mixed
    {
        if (isset($this->overrides[$key])) {
            return $this->overrides[$key];
        }

        // Dot notation for overrides too
        $val = $this->getDot($key);
        if ($val !== null) {
            return $val;
        }

        return $this->configurationRepository->get($key, $default);
    }

    public function set(string $key, mixed $value) : void
    {
        $this->overrides[$key] = $value;
    }

    public function clear() : void
    {
        $this->overrides = [];
    }

    private function getDot(string $key) : mixed
    {
        $array = $this->overrides;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return null;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}
