<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Documentation\Api\System\Configuration;

final readonly class ApiDocumentationConfiguration
{
    public function __construct(
        public string $title = 'Avax API',
        public string $version = '1.0.0',
        public string $swaggerUiPath = '/api/docs',
        public string $openApiJsonPath = '/api/docs/openapi.json',
        public bool   $enableSwaggerUi = true,
    ) {}

    public static function make() : self
    {
        return new self();
    }

    public function withTitle(string $title) : self
    {
        return new self(
            title          : $title,
            version        : $this->version,
            swaggerUiPath  : $this->swaggerUiPath,
            openApiJsonPath: $this->openApiJsonPath,
            enableSwaggerUi: $this->enableSwaggerUi,
        );
    }

    public function withVersion(string $version) : self
    {
        return new self(
            title          : $this->title,
            version        : $version,
            swaggerUiPath  : $this->swaggerUiPath,
            openApiJsonPath: $this->openApiJsonPath,
            enableSwaggerUi: $this->enableSwaggerUi,
        );
    }
}
