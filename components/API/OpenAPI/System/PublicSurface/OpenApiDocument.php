<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\PublicSurface;

final readonly class OpenApiDocument
{
    /**
     * @param array<string, mixed> $document
     */
    public function __construct(private array $document) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return $this->document;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function path(string $path) : array|null
    {
        $paths = $this->document['paths'] ?? [];

        if (! is_array($paths)) {
            return null;
        }

        $pathDocument = $paths[$path] ?? null;

        return is_array($pathDocument) ? $pathDocument : null;
    }

    public function title() : string
    {
        $info = $this->document['info'] ?? [];

        if (! is_array($info)) {
            return '';
        }

        $title = $info['title'] ?? '';

        return is_string($title) ? $title : '';
    }

    public function version() : string
    {
        $info = $this->document['info'] ?? [];

        if (! is_array($info)) {
            return '';
        }

        $version = $info['version'] ?? '';

        return is_string($version) ? $version : '';
    }
}
