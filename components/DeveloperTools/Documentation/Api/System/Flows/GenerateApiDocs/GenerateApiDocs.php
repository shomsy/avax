<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Documentation\Api\System\Flows\GenerateApiDocs;

use Avax\Components\DeveloperTools\Documentation\Api\System\Capabilities\OpenApi\OpenApiGenerator;
use Avax\Components\DeveloperTools\Documentation\Api\System\Configuration\ApiDocumentationConfiguration;

final readonly class GenerateApiDocs
{
    public function __construct(
        private ApiDocumentationConfiguration $config = new ApiDocumentationConfiguration(),
    ) {}

    /**
     * @param list<array{method:string,path:string,summary?:string,tags?:list<string>}> $routes
     *
     * @return array<string, mixed>
     */
    public function execute(array $routes = []) : array
    {
        $generator = new OpenApiGenerator(
            title  : $this->config->title,
            version: $this->config->version,
        );

        return $generator->generate(routes: $routes);
    }
}
