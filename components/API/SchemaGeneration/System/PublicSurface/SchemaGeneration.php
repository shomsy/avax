<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\PublicSurface;

use Avax\Components\API\SchemaGeneration\System\Configuration\BuildSchemaGeneration;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateOpenApiFromRoutes\GenerateOpenApiFromRoutes;
use Avax\Components\API\SchemaGeneration\System\Foundation\JsonSchemaDocument;
use Avax\Components\API\SchemaGeneration\System\Foundation\PayloadValidationResult;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;

/**
 * SchemaGeneration — thin public API for generating JSON Schema from DataObject metadata.
 */
final class SchemaGeneration
{
    private static ?\Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationAssembly $assembly = null;

    private static function assembly() : \Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationAssembly
    {
        return self::$assembly ??= BuildSchemaGeneration::make();
    }

    /**
     * Generate JSON Schema from a DataObject class or instance.
     *
     * @template T of \Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject
     * @param class-string<T>|T $dataObject
     */
    public static function fromDataObject(string|object $dataObject) : JsonSchemaDocument
    {
        return self::assembly()->generateSchema->execute(dataObject: $dataObject);
    }

    /**
     * Generate JSON Schema for an incoming request payload.
     *
     * @template T of \Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject
     * @param class-string<T> $requestClass
     */
    public static function request(string $requestClass) : JsonSchemaDocument
    {
        return self::assembly()->generateRequest->execute(requestClass: $requestClass);
    }

    /**
     * Generate JSON Schema for an outgoing response payload.
     *
     * @template T of \Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject
     * @param class-string<T> $responseClass
     */
    public static function response(string $responseClass) : JsonSchemaDocument
    {
        return self::assembly()->generateResponse->execute(responseClass: $responseClass);
    }

    /**
     * Validate a payload array against a JSON Schema document.
     *
     * @param array<string, mixed> $payload
     */
    public static function validatePayload(array $payload, JsonSchemaDocument $schema) : PayloadValidationResult
    {
        return self::assembly()->validatePayload->execute(payload: $payload, schema: $schema);
    }

    /**
     * Generate OpenAPI 3.0 spec from registered Router routes.
     *
     * Produces a valid OpenAPI 3.0.3 document with paths inferred from routes.
     * Path parameters are extracted from {param} patterns.
     * Request/response schemas are not attached (routes do not store schema metadata).
     *
     * @return array<string, mixed>
     */
    public static function openApi(Router $router, ?string $title = null, ?string $version = null) : array
    {
        $generator = new GenerateOpenApiFromRoutes(
            title: $title ?? 'AvaX API',
            version: $version ?? '1.0.0',
        );

        return $generator->fromRouter($router);
    }

    /**
     * Replace the default assembly (for testing).
     */
    public static function setAssembly(?\Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationAssembly $assembly) : void
    {
        self::$assembly = $assembly;
    }
}
