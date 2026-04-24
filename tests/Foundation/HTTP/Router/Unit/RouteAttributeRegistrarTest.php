<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\HTTP\Router\HttpMethod;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Attributes\AttributeRouteRegistrar;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Attributes\Route;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Constraints\RouteConstraintValidator;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\HTTP\Router\System\Foundation\Exceptions\DuplicateRouteException;
use Avax\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionException;

#[Route(path: '/api', name: 'api', middleware: ['api'], defaults: ['locale' => 'en'], constraints: ['locale' => '[a-z]+'])]
final class SampleAttributeController
{
    #[Route(path: '/items/{id?}', name: 'items.show', methods: ['GET', 'HEAD'], middleware: ['auth'], constraints: ['id' => '\\d+'], authorize: 'policy')]
    public function show() : void {}
}

final class RouteAttributeRegistrarTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws DuplicateRouteException
     * @throws ReservedRouteNameException
     */
    public function test_registers_routes_from_attributes() : void
    {
        $router     = new HttpRequestRouter(constraintValidator: new RouteConstraintValidator, matcher: new RouteMatcher(logger: new NullLogger));
        $registrar  = new AttributeRouteRegistrar(router: $router);
        $controller = SampleAttributeController::class;

        $registrar->register(controller: $controller);

        $routes = $router->allRoutes();

        $this->assertArrayHasKey(key: HttpMethod::GET->value, array: $routes);
        $this->assertArrayHasKey(key: HttpMethod::HEAD->value, array: $routes);

        $route = $routes[HttpMethod::GET->value][0];

        $this->assertSame(expected: '/api/items/{id?}', actual: $route->path);
        $this->assertSame(expected: 'api.items.show', actual: $route->name);
        $this->assertSame(expected: [$controller, 'show'], actual: $route->action);
        $this->assertSame(expected: ['api', 'auth'], actual: $route->middleware);
        $this->assertSame(expected: ['locale' => '[a-z]+', 'id' => '\\d+'], actual: $route->constraints);
        $this->assertSame(expected: 'policy', actual: $route->authorization);
    }
}
