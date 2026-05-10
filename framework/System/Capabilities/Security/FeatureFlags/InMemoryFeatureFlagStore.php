<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags;

use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagName;

final class InMemoryFeatureFlagStore implements FeatureFlagStore
{
    /** @var array<string, FeatureFlag> */
    private array $flags = [];

    public function get(FeatureFlagName $name): ?FeatureFlag
    {
        return $this->flags[$name->value] ?? null;
    }

    public function all(): array
    {
        return array_values($this->flags);
    }

    public function set(FeatureFlag $flag): void
    {
        $this->flags[$flag->name->value] = $flag;
    }

    public function clear(): void
    {
        $this->flags = [];
    }
}
