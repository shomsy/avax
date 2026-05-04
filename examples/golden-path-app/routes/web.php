<?php

declare(strict_types=1);

use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteBuilder;

/**
 * Golden Path App Routes
 * This is an example of how to define routes in Avax.
 */
$router = static function (RouteBuilder $routes): void {
    $routes->get('/', static fn() => [
        'name' => 'Avax Golden Path App',
        'version' => '1.0.0',
        'message' => 'Welcome to Avax Framework',
    ]);
};

return $router;
