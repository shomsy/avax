<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointVersion;

final class ApiBlueprintDefinition
{
    /**
     * @param list<EndpointDefinition> $endpoints
     */
    public function __construct(
        public readonly array $endpoints = [],
    ) {}

    public function findEndpoint(string $path, string $method) : ?EndpointDefinition
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->path === $path && $endpoint->method === $method) {
                return $endpoint;
            }
        }

        return null;
    }

    /**
     * @return list<EndpointDefinition>
     */
    public function endpointsForVersion(EndpointVersion $version) : array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointDefinition $endpoint) : bool => $endpoint->version === $version,
            ),
        );
    }

    /**
     * @return list<EndpointDefinition>
     */
    public function deprecatedEndpoints() : array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointDefinition $endpoint) : bool => $endpoint->deprecation !== null,
            ),
        );
    }
}
