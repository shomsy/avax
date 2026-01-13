<?php

declare(strict_types=1);

use Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException;
use Avax\HTTP\Router\Routing\RouteBuilder;
use Avax\HTTP\Router\Routing\RouteDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Tests for centralized route validation - ensures @ suppressions are removed.
 *
 * Verifies that regex constraint validation works without error suppression
 * and provides proper error messages for invalid patterns.
 */
class RouteValidationCentralizationTest extends TestCase
{
    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function route_definition_validates_constraints_without_suppression() : void
    {
        // Should not throw exception for valid constraints
        $route = new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'Controller@show',
            constraints: ['id' => '\d+']
        );

        $this->assertEquals(expected: ['id' => '\d+'], actual: $route->constraints);
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function route_definition_rejects_invalid_regex_patterns() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid regex constraint "[invalid": invalid regex syntax');

        new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'Controller@show',
            constraints: ['id' => '[invalid'] // Missing closing bracket
        );
    }

    /**
     * @test
     * @throws \Avax\HTTP\Router\Routing\Exceptions\ReservedRouteNameException
     */
    public function route_definition_handles_regex_compilation_errors() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid regex constraint "(unclosed": invalid regex syntax');

        new RouteDefinition(
            method     : 'GET',
            path       : '/users/{id}',
            action     : 'Controller@show',
            constraints: ['id' => '(unclosed'] // Unclosed parenthesis
        );
    }

    /**
     * @test
     */
    public function route_builder_validates_constraints_without_suppression() : void
    {
        $builder = RouteBuilder::make(method: 'GET', path: '/users/{id}')
            ->action(action: 'Controller@show')
            ->where(parameter: 'id', pattern: '\d+');

        $route = $builder->build();
        $this->assertEquals(expected: ['id' => '\d+'], actual: $route->constraints);
    }

    /**
     * @test
     */
    public function route_builder_rejects_invalid_regex_patterns() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid constraint regex "[invalid": invalid regex syntax');

        RouteBuilder::make(method: 'GET', path: '/users/{id}')
            ->action(action: 'Controller@show')
            ->where(parameter: 'id', pattern: '[invalid'); // Missing closing bracket
    }

    /**
     * @test
     */
    public function route_builder_handles_regex_compilation_errors() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid constraint regex "(unclosed": invalid regex syntax');

        RouteBuilder::make(method: 'GET', path: '/users/{id}')
            ->action(action: 'Controller@show')
            ->where(parameter: 'id', pattern: '(unclosed'); // Unclosed parenthesis
    }

    /**
     * @test
     */
    public function route_builder_where_in_validates_all_constraints() : void
    {
        $builder = RouteBuilder::make(method: 'GET', path: '/users/{id}/posts/{slug}')
            ->action(action: 'Controller@show')
            ->whereIn(constraints: [
                'id'   => '\d+',
                'slug' => '[a-z0-9-]+',
            ]);

        $route = $builder->build();
        $this->assertEquals(expected: [
            'id'   => '\d+',
            'slug' => '[a-z0-9-]+',
        ],                  actual  : $route->constraints);
    }

    /**
     * @test
     */
    public function route_builder_where_in_fails_on_first_invalid_constraint() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid constraint regex "[invalid": invalid regex syntax');

        RouteBuilder::make(method: 'GET', path: '/users/{id}/posts/{slug}')
            ->action(action: 'Controller@show')
            ->whereIn(constraints: [
                'id'   => '[invalid', // Invalid first
                'slug' => '[a-z0-9-]+', // Valid second
            ]);
    }

    /**
     * @test
     */
    public function reserved_route_names_still_rejected() : void
    {
        $this->expectException(exception: ReservedRouteNameException::class);

        new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: 'Controller@show',
            name  : '__avax.reserved.name'
        );
    }

    /**
     * @test
     */
    public function path_validation_still_works() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid route path');

        new RouteDefinition(
            method: 'GET',
            path  : '/invalid/{param*}/extra', // Wildcard not at end
            action: 'Controller@show'
        );
    }
}
