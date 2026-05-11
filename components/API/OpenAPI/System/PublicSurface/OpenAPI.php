<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiJson\RenderOpenApiJson;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiYaml\RenderOpenApiYaml;
use Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration\BuildOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Configuration\OpenApiConfiguration;
use Avax\Components\API\OpenAPI\System\Flows\CompareOpenApiDocuments\CompareOpenApiDocuments;
use Avax\Components\API\OpenAPI\System\Flows\ExportOpenApiDocument\ExportOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Flows\ValidateOpenApiDocument\ValidateOpenApiDocument;

final readonly class OpenAPI
{
    public static function fromSurface(
        ApiBlueprintDefinition $surface, OpenApiConfiguration|null $configuration = null,
    ) : OpenApiDocument
    {
        return new ExportOpenApiDocument(
            buildOpenApiDocument: new BuildOpenApiDocument(
                                      configuration: $configuration ?? new OpenApiConfiguration(),
                                  ),
        )->export(surface: $surface);
    }

    public static function validate(OpenApiDocument $document) : OpenApiValidationReport
    {
        return new ValidateOpenApiDocument()->validate(document: $document);
    }

    public static function compare(OpenApiDocument $oldDocument, OpenApiDocument $newDocument) : OpenApiComparisonReport
    {
        return new CompareOpenApiDocuments()->compare(
            oldDocument: $oldDocument,
            newDocument: $newDocument,
        );
    }

    public static function json(OpenApiDocument $document) : string
    {
        return new RenderOpenApiJson()->render(document: $document);
    }

    public static function yaml(OpenApiDocument $document) : string
    {
        return new RenderOpenApiYaml()->render(document: $document);
    }
}
