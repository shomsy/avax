<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Capabilities\Flags;

use Avax\Components\Application\FeatureFlags\System\PublicSurface\FlagStoreInterface;

final class ConfigFlagStore implements FlagStoreInterface
{
    /**
     * @param string $configKey The root config key for flags (e.g. 'features')
     */
    public function __construct(
        private string $configKey = 'features',
    ) {}

    public function get(string $flag) : mixed
    {
        return config($this->configKey . '.' . $flag);
    }

    public function set(string $flag, mixed $value) : void
    {
        // Config is usually immutable at runtime via the config helper
    }

    /** @return array<string, mixed> */
    public function all() : array
    {
        $all = config($this->configKey);

        return is_array($all) ? $all : [];
    }
}
