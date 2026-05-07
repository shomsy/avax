<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiJson;

use Avax\Components\API\OpenAPI\System\Foundation\Failure\OpenApiDocumentInvalid;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;
use JsonException;

final readonly class RenderOpenApiJson
{
    public function render(OpenApiDocument $document) : string
    {
        try {
            return json_encode(
                value: $document->toArray(),
                flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            );
        } catch (JsonException $jsonException) {
            throw new OpenApiDocumentInvalid($jsonException->getMessage(), previous: $jsonException);
        }
    }
}
