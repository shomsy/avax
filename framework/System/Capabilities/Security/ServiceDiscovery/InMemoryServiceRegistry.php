<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\ServiceDiscovery;

use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceEndpoint;
use Avax\Framework\System\Capabilities\Security\ServiceDiscovery\Foundation\ServiceName;

final class InMemoryServiceRegistry implements ServiceRegistry
{
    /** @var array<string, list<ServiceEndpoint>> */
    private array $services = [];

    public function register(ServiceName $name, ServiceEndpoint $endpoint): void
    {
        $this->services[$name->value][] = $endpoint;
    }

    public function resolve(ServiceName $name): array
    {
        return $this->services[$name->value] ?? [];
    }

    public function listServices(): array
    {
        return array_map(
            static fn (string $name) => new ServiceName($name),
            array_keys($this->services),
        );
    }

    public function deregister(ServiceName $name, ServiceEndpoint $endpoint): void
    {
        $key = $name->value;
        if (!isset($this->services[$key])) {
            return;
        }

        $this->services[$key] = array_values(array_filter(
            $this->services[$key],
            static fn (ServiceEndpoint $e) => $e->url !== $endpoint->url,
        ));

        if ($this->services[$key] === []) {
            unset($this->services[$key]);
        }
    }

    public function clear(): void
    {
        $this->services = [];
    }
}
