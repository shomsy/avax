<?php

declare(strict_types=1);

namespace Avax\API\OpenAPI\System\PublicSurface;

final readonly class OpenAPI
{
    public static function document(string $title = 'API', string $version = '1.0.0'): OpenApiDocument
    {
        return new OpenApiDocument(title: $title, version: $version);
    }

    public static function generate(array $contracts): OpenApiDocument
    {
        return GenerateOpenApiDocument::generate(contracts: $contracts);
    }

    public static function toJson(OpenApiDocument $document): string
    {
        return RenderOpenApiJson::render(document: $document);
    }

    public static function toYaml(OpenApiDocument $document): string
    {
        return RenderOpenApiYaml::render(document: $document);
    }

    public static function validate(OpenApiDocument $document): bool
    {
        return ValidateOpenApiDocument::validate(document: $document);
    }
}