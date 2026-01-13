<?php

require __DIR__ . '/vendor/autoload.php';

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Validation\RouteConstraintValidator;
use Avax\HTTP\URI\UriBuilder;

$router = new HttpRequestRouter(constraintValidator: new RouteConstraintValidator);
$router->registerRoute(method: 'GET', path: '/users/{id?}', action: 'handler', defaults: ['id' => '42']);

$ref = new ReflectionMethod(objectOrMethod: HttpRequestRouter::class, method: 'compileRoutePattern');
$ref->setAccessible(accessible: true);
$pattern = $ref->invoke($router, '/users/{id?}', []);
var_dump($pattern);

$request = new Request(serverParams: ['REQUEST_METHOD' => 'GET'], uri: UriBuilder::createFromString(uri: 'https://example.com/users'));
var_dump($request->getUri()->getPath());
var_dump(preg_match($pattern, $request->getUri()->getPath(), $m));
var_dump($m);
$route = $router->resolve(request: $request);
var_dump($route);
