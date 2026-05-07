<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Flows\ExportOpenApiDocument;

use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;
use Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration\BuildOpenApiDocument;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;

final readonly class ExportOpenApiDocument
{
    public function __construct(private BuildOpenApiDocument $buildOpenApiDocument) {}

    public function export(ApiBlueprintDefinition $surface) : OpenApiDocument
    {
        return new OpenApiDocument($this->buildOpenApiDocument->build(surface: $surface));
    }
}
