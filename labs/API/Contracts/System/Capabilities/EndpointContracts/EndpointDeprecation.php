<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Capabilities\EndpointContracts;

final class EndpointDeprecation
{
    public function __construct(
        public readonly string      $since,
        public readonly string|null $warning,
        public readonly string|null $removeIn,
        public readonly string|null $replacement,
    )
    {
    }

    public function isDeprecated(): bool
    {
        return true;
    }

    public function severity(): string
    {
        if ($this->removeIn !== null) {
            return 'critical';
        }

        return 'warning';
    }
}
