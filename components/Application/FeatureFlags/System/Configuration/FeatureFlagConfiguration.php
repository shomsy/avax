<?php

declare(strict_types=1);

namespace Avax\Components\Application\FeatureFlags\System\Configuration;

final readonly class FeatureFlagConfiguration
{
    /**
     * @param array<string, mixed> $defaultFlags
     */
    public function __construct(
        public array $defaultFlags = [],
        public bool  $strictMode = false,
    ) {}
}
