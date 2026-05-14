<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation;

final readonly class FeatureFlagName
{
    /**
     * @throws \InvalidArgumentException When feature flag name is empty
     */
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Feature flag name must not be empty.');
        }
    }
}
