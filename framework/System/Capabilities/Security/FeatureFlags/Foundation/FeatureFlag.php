<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\FeatureFlags\Foundation;

final readonly class FeatureFlag
{
    public function __construct(
        public FeatureFlagName $name,
        public FeatureFlagState $state = FeatureFlagState::Disabled,
        /** @var array<string, string> $environmentOverrides */
        public array $environmentOverrides = [],
        public string $description = '',
    ) {
    }

    public function isEnabled(?string $environment = null): bool
    {
        if ($environment !== null && isset($this->environmentOverrides[$environment])) {
            return $this->environmentOverrides[$environment] === FeatureFlagState::Enabled->value;
        }

        return $this->state === FeatureFlagState::Enabled;
    }
}
