<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\ReadApiDescriptions;

use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointVersion;

final class ReadFakeApiDescriptions implements ReadApiDescriptions
{
    /**
     * @var list<EndpointContract>
     */
    private array $endpoints = [];

    public function registerEndpoint(EndpointContract $endpoint): void
    {
        $this->endpoints[] = $endpoint;
    }

    public function findEndpoint(string $path, string $method): ?EndpointContract
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->path === $path && $endpoint->method === $method) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * @return list<EndpointContract>
     */
    public function getAllEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * @return list<EndpointContract>
     */
    public function getEndpointsByVersion(EndpointVersion $version): array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointContract $endpoint): bool => $endpoint->version === $version,
            ),
        );
    }

    public function clear(): void
    {
        $this->endpoints = [];
    }
}
