<?php

declare(strict_types=1);

use Avax\Components\Documentation\Api\System\PublicSurface\ApiDocumentation;
use Avax\Components\Operations\Monitoring\System\PublicSurface\Monitoring;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;

$router = static function (RouteBuilder $routes) : void {
    $routes->get('/', static fn () => [
        'name'    => 'Avax',
        'version' => '1.0.0',
        'message' => 'Welcome to Avax Framework',
        'docs'    => '/api/docs',
    ]);

    $routes->get('/health', static fn () => Monitoring::health()->toArray());

    $routes->get('/api/docs/openapi.json', static fn () => ApiDocumentation::openApi([
                                                                                         ['method' => 'GET', 'path' => '/', 'summary' => 'Framework welcome endpoint', 'tags' => ['Framework']],
                                                                                         ['method' => 'GET', 'path' => '/health', 'summary' => 'Application health check', 'tags' => ['Operations']],
                                                                                         ['method' => 'GET', 'path' => '/api/docs', 'summary' => 'Swagger UI', 'tags' => ['Documentation']],
                                                                                         ['method' => 'GET', 'path' => '/api/docs/openapi.json', 'summary' => 'OpenAPI document', 'tags' => ['Documentation']],
                                                                                     ]));

    $routes->get('/api/docs', static fn () => ApiDocumentation::swagger());
};

return $router;
