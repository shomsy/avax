<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\GenerateApiDocumentation;

use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointVersion;

final class InMemoryApiDocumentationSource implements ApiDocumentationSource
{
    /**
     * @var list<EndpointDefinition>
     */
    private array $endpoints = [];

    public function registerEndpoint(EndpointDefinition $endpoint) : void
    {
        $this->endpoints[] = $endpoint;
    }

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
    public function getAllEndpoints() : array
    {
        return $this->endpoints;
    }

    /**
     * @return list<EndpointDefinition>
     */
    public function getEndpointsByVersion(EndpointVersion $version) : array
    {
        return array_values(
            array_filter(
                $this->endpoints,
                static fn (EndpointDefinition $endpoint) : bool => $endpoint->version === $version,
            ),
        );
    }

    public function clear() : void
    {
        $this->endpoints = [];
    }
}
