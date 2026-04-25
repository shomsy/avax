<?php

require __DIR__ . '/vendor/autoload.php';

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Avax\HTTP\URI\Uri;
use Psr\Log\NullLogger;

$router = new HttpRequestRouter(constraintValidator: new RouteConstraintValidator, matcher: new RouteMatcher(logger: new NullLogger));
$router->registerRoute(method: 'GET', path: '/users/{id?}', action: 'handler');

$ref = new ReflectionMethod(objectOrMethod: HttpRequestRouter::class, method: 'compileRoutePattern');
$ref->setAccessible(accessible: true);
$pattern = $ref->invoke($router, '/users/{id?}', []);

$request = new Request(serverParams: ['REQUEST_METHOD' => 'GET'], uri: Uri::fromString(uri: 'https://example.com/users'));
$route = $router->resolve(request: $request);
