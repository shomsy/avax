<?php

declare(strict_types=1);

namespace Avax\Components\Documentation\Api\System\PublicSurface;

use Avax\Components\Documentation\Api\System\Capabilities\OpenApi\OpenApiGenerator;
use Avax\Components\Documentation\Api\System\Capabilities\Swagger\SwaggerUi;

final readonly class ApiDocumentation
{
    /**
     * @param list<array{method:string,path:string,summary?:string,tags?:list<string>}> $routes
     */
    public static function openApi(array $routes = []) : array
    {
        return (new OpenApiGenerator())->generate(routes: $routes);
    }

    public static function swagger(string $openApiUrl = '/api/docs/openapi.json') : string
    {
        return (new SwaggerUi())->html(openApiUrl: $openApiUrl);
    }
}
