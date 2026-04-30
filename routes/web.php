<?php

declare(strict_types=1);

use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;

$router = function (RouteBuilder $routes) {
    $routes->get('/', fn () => [
        'name'    => 'Avax',
        'version' => '1.0.0',
        'message' => 'Welcome to Avax Framework',
        'docs'    => '/api/docs',
    ]);

    $routes->get('/health', fn () => [
        'status'    => 'healthy',
        'timestamp' => time(),
    ]);
};

return $router;