<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Documentation\Api\System\Flows\RenderSwaggerUi;

use Avax\Components\DeveloperTools\Documentation\Api\System\Capabilities\Swagger\SwaggerUi;
use Avax\Components\DeveloperTools\Documentation\Api\System\Configuration\ApiDocumentationConfiguration;

final readonly class RenderSwaggerUi
{
    public function __construct(
        private ApiDocumentationConfiguration $config = new ApiDocumentationConfiguration(),
    ) {}

    public function execute() : string
    {
        if (! $this->config->enableSwaggerUi) {
            return '';
        }

        $swaggerUi = new SwaggerUi();

        return $swaggerUi->html(openApiUrl: $this->config->openApiJsonPath);
    }
}
