<?php

declare(strict_types=1);

namespace Avax\API\OpenAPI\System\PublicSurface;

final readonly class OpenApiDocument
{
    public array $paths;
    public array $components;
    public array $info;

    public function __construct(
        public string  $title,
        public string  $version,
        public ?string $description = null,
    )
    {
        $this->paths = [];
        $this->components = [];
        $this->info = [
            'title' => $title,
            'version' => $version,
            'description' => $description,
        ];
    }

    public function addPath(string $path, array $methods): self
    {
        $this->paths[$path] = $methods;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => $this->info,
            'paths' => $this->paths,
            'components' => $this->components,
        ];
    }
}