<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Flows\GenerateOpenApiDocument;

use Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration\BuildOpenApiDocument;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;

final readonly class GenerateOpenApiDocument
{
    public function __construct(private BuildOpenApiDocument $buildOpenApiDocument) {}

    public function generate(ApiSurfaceDefinition $surface) : OpenApiDocument
    {
        return new OpenApiDocument($this->buildOpenApiDocument->build(surface: $surface));
    }
}
