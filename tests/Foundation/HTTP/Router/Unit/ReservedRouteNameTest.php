<?php

declare(strict_types=1);

use Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\Routing\RouteDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Tests for reserved route name validation.
 */
class ReservedRouteNameTest extends TestCase
{
    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function allows_normal_route_names() : void
    {
        // Should not throw exception
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: static function () {
                return 'ok';
            },
            name  : 'normal.route.name'
        );

        $this->assertEquals(expected: 'normal.route.name', actual: $route->name);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function allows_empty_route_names() : void
    {
        // Should not throw exception
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: static function () {
                return 'ok';
            },
            name  : ''
        );

        $this->assertEquals(expected: '', actual: $route->name);
    }

    /**
     * @test
     */
    public function rejects_reserved_route_names() : void
    {
        $this->expectException(exception: ReservedRouteNameException::class);
        $this->expectExceptionMessage(message: 'Route name \'__avax.internal.route\' is reserved');

        new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: static function () {
                return 'ok';
            },
            name  : '__avax.internal.route'
        );
    }

    /**
     * @test
     */
    public function rejects_various_reserved_prefixes() : void
    {
        $reservedNames = [
            '__avax.router.fallback',
            '__avax.kernel.internal',
            '__avax.cache.manifest',
            '__avax.middleware.chain',
        ];

        foreach ($reservedNames as $name) {
            try {
                new RouteDefinition(
                    method: 'GET',
                    path  : '/test',
                    action: static function () {
                        return 'ok';
                    },
                    name  : $name
                );
                $this->fail(message: "Expected ReservedRouteNameException for name: {$name}");
            } catch (ReservedRouteNameException $e) {
                $this->assertStringContains('is reserved', $e->getMessage());
            }
        }
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function allows_similar_but_not_reserved_names() : void
    {
        $allowedNames = [
            'my__avax.route',  // doesn't start with __avax.
            '__other.prefix',  // different prefix
            'avax.route',      // no double underscore
            'route.__avax',    // prefix at end
        ];

        foreach ($allowedNames as $name) {
            $route = new RouteDefinition(
                method: 'GET',
                path  : '/test',
                action: static function () {
                    return 'ok';
                },
                name  : $name
            );

            $this->assertEquals(expected: $name, actual: $route->name);
        }
    }
}
