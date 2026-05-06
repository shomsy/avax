<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\PublicSurface;

use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts\EndpointVersion;

final class ApiContract
{
    /**
     * @param  list<EndpointContract>  $endpoints
     */
    public function __construct(
        public readonly array $endpoints = [],
    ) {
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
    public function endpointsForVersion(EndpointVersion $version): array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointContract $endpoint): bool => $endpoint->version === $version,
            ),
        );
    }

    /**
     * @return list<EndpointContract>
     */
    public function deprecatedEndpoints(): array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointContract $endpoint): bool => $endpoint->deprecation !== null,
            ),
        );
    }
}
