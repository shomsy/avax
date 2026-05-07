<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\BuildApiSurface;

use Avax\Components\API\Surface\System\Capabilities\GenerateApiDocumentation\ApiDocumentationSource;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;

final class BuildApiSurface
{
    public function __construct(private readonly ApiDocumentationSource $apiDocumentation) {}

    public function build() : ApiSurfaceDefinition
    {
        return new ApiSurfaceDefinition($this->apiDocumentation->getAllEndpoints());
    }
}
