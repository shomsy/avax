<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags;

use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagName;

interface FeatureFlagStore
{
    public function get(FeatureFlagName $name): ?FeatureFlag;

    /** @return list<FeatureFlag> */
    public function all(): array;

    public function set(FeatureFlag $flag): void;
}
