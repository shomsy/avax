<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Flows\ValidateOpenApiDocument;

use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiValidationReport;

final readonly class ValidateOpenApiDocument
{
    public function validate(OpenApiDocument $document) : OpenApiValidationReport
    {
        $payload = $document->toArray();
        $errors  = [];

        if (($payload['openapi'] ?? null) !== '3.1.0') {
            $errors[] = 'OpenAPI version must be 3.1.0.';
        }

        if ($document->title() === '') {
            $errors[] = 'OpenAPI info.title must not be empty.';
        }

        if ($document->version() === '') {
            $errors[] = 'OpenAPI info.version must not be empty.';
        }

        if (! isset($payload['paths']) || ! is_array($payload['paths']) || $payload['paths'] === []) {
            $errors[] = 'OpenAPI paths must not be empty.';
        }

        return new OpenApiValidationReport(errors: $errors);
    }
}
