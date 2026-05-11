<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags;

use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlag;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagName;
use Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation\FeatureFlagState;

final readonly class EvaluateFeatureFlag
{
    public function __construct(
        private FeatureFlagStore $store,
        private string|null $environment = null,
    ) {
    }

    public function isEnabled(FeatureFlagName $name): bool
    {
        $flag = $this->store->get($name);

        if ($flag === null) {
            return false;
        }

        return $flag->isEnabled($this->environment);
    }

    public function get(FeatureFlagName $name) : FeatureFlag|null
    {
        return $this->store->get($name);
    }

    /** @return list<FeatureFlag> */
    public function all(): array
    {
        return $this->store->all();
    }
}
