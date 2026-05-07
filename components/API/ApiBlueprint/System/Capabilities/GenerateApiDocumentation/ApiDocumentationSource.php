<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\GenerateApiDocumentation;

use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointVersion;

interface ApiDocumentationSource
{
    public function registerEndpoint(EndpointDefinition $endpoint) : void;

    public function findEndpoint(string $path, string $method) : ?EndpointDefinition;

    /**
     * @return list<EndpointDefinition>
     */
    public function getAllEndpoints() : array;

    /**
     * @return list<EndpointDefinition>
     */
    public function getEndpointsByVersion(EndpointVersion $version) : array;
}
