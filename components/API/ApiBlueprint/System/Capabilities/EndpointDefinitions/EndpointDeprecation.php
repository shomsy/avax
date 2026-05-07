<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions;

final class EndpointDeprecation
{
    public function __construct(
        public readonly string  $since,
        public readonly ?string $warning,
        public readonly ?string $removeIn,
        public readonly ?string $replacement,
    ) {}

    public function isDeprecated() : bool
    {
        return true;
    }

    public function severity() : string
    {
        if ($this->removeIn !== null) {
            return 'critical';
        }

        return 'warning';
    }
}
