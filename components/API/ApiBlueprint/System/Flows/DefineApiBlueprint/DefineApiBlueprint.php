<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\DefineApiBlueprint;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Documentation\ApiDocumentationSource;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;

final class DefineApiBlueprint
{
    public function __construct(private readonly ApiDocumentationSource $apiDocumentation) {}

    public function build() : ApiBlueprintDefinition
    {
        return new ApiBlueprintDefinition($this->apiDocumentation->getAllEndpoints());
    }
}
