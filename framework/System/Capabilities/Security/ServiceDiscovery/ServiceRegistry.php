<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\ServiceDiscovery;

use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceEndpoint;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceName;

interface ServiceRegistry
{
    public function register(ServiceName $name, ServiceEndpoint $endpoint): void;

    /** @return list<ServiceEndpoint> */
    public function resolve(ServiceName $name): array;

    /** @return list<ServiceName> */
    public function listServices(): array;

    public function deregister(ServiceName $name, ServiceEndpoint $endpoint): void;
}
