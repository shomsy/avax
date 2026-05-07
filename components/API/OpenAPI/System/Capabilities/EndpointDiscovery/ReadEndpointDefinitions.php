<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\EndpointDiscovery;

use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;

final readonly class ReadEndpointDefinitions
{
    /**
     * @return list<EndpointDefinition>
     */
    public function fromSurface(ApiSurfaceDefinition $surface) : array
    {
        return $surface->endpoints;
    }
}
