<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\EndpointDiscovery;

use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;

final readonly class ReadEndpointDefinitions
{
    /**
     * @return list<EndpointDefinition>
     */
    public function fromSurface(ApiBlueprintDefinition $surface) : array
    {
        return $surface->endpoints;
    }
}
