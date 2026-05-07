<?php

declare(strict_types=1);

use Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\Diagnostics;
use Avax\Components\DeveloperTools\Documentation\Api\System\PublicSurface\ApiDocumentation;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

$router = static function (RouterInterface $routes): void {
    $routes->get('/', static fn () => [
        'name' => 'Avax',
        'version' => '1.0.0',
        'message' => 'Welcome to Avax Framework',
        'docs' => '/api/docs',
    ]);

    $routes->get('/health', static fn () => Diagnostics::health());

    $routes->get('/api/docs/openapi.json', static fn () => ApiDocumentation::openApi([
        ['method' => 'GET', 'path' => '/', 'summary' => 'Framework welcome endpoint', 'tags' => ['Framework']],
        ['method' => 'GET', 'path' => '/health', 'summary' => 'Application health check', 'tags' => ['Operations']],
        ['method' => 'GET', 'path' => '/api/docs', 'summary' => 'Swagger UI', 'tags' => ['Documentation']],
        ['method' => 'GET', 'path' => '/api/docs/openapi.json', 'summary' => 'OpenAPI document', 'tags' => ['Documentation']],
    ]));

    $routes->get('/api/docs', static fn () => ApiDocumentation::swagger());
};

return $router;
