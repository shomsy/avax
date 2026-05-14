<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation;

final readonly class ServiceName
{
    /**
     * @throws \InvalidArgumentException When service name is empty
     */
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('Service name must not be empty.');
        }
    }
}
