<?php

declare(strict_types=1);

use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteExportValidator;
use Avax\HTTP\Router\System\Foundation\Exceptions\ReservedRouteNameException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for RouteExportValidator - cache exportability validation.
 */
class RouteExportValidatorTest extends TestCase
{
    private RouteExportValidator $validator;

    private $logger;

    /**
     * @test
     */
    public function validates_exportable_string_action_routes() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: 'Controller@action'
        );

        $this->assertTrue(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function validates_exportable_array_action_routes() : void
    {
        $route = new RouteDefinition(
            method: 'POST',
            path  : '/users',
            action: ['UserController', 'store']
        );

        $this->assertTrue(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function validates_routes_with_all_exportable_fields() : void
    {
        $route = new RouteDefinition(
            method       : 'GET',
            path         : '/api/users',
            action       : 'UserController@index',
            middleware   : ['auth', 'json'],
            name         : 'users.index',
            constraints  : ['id' => '\d+'],
            defaults     : ['page' => 1, 'limit' => 10],
            domain       : 'api.example.com',
            attributes   : ['version' => 'v1'],
            authorization: 'admin'
        );

        $this->assertTrue(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_closure_action_routes() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: static function () {
                return 'closure';
            }
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'action contains non-serializable data (closures or objects)', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_invalid_array_action_routes() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: ['Controller'] // Missing method
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'action contains non-serializable data (closures or objects)', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_non_string_middleware() : void
    {
        $route = new RouteDefinition(
            method    : 'GET',
            path      : '/test',
            action    : 'Controller@action',
            middleware: ['auth', static function () {}, 'json'] // Closure in middleware
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'middleware contains non-string values', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_non_scalar_defaults() : void
    {
        $route = new RouteDefinition(
            method  : 'GET',
            path    : '/test',
            action  : 'Controller@action',
            defaults: ['callback' => static function () {}] // Closure in defaults
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'defaults contain non-scalar values', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_non_scalar_attributes() : void
    {
        $route = new RouteDefinition(
            method    : 'GET',
            path      : '/test',
            action    : 'Controller@action',
            attributes: ['object' => new stdClass] // Object in attributes
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'attributes contain non-scalar values', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function rejects_non_string_domain() : void
    {
        $route = new RouteDefinition(
            method: 'GET',
            path  : '/test',
            action: 'Controller@action',
            domain: 123 // Integer instead of string
        );

        $this->logger->expects(invocationRule: $this->once())
            ->method(constraint: 'warning')
            ->with('Route cannot be cached', $this->callback(callback: static function ($context) {
                return isset($context['issues']) &&
                    in_array(needle: 'domain is not a string', haystack: $context['issues']);
            }));

        $this->assertFalse(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function filters_exportable_routes() : void
    {
        $exportableRoute = new RouteDefinition(
            method: 'GET',
            path  : '/exportable',
            action: 'Controller@action'
        );

        $nonExportableRoute = new RouteDefinition(
            method: 'POST',
            path  : '/closure',
            action: static function () {
                return 'closure';
            }
        );

        $routes = [$exportableRoute, $nonExportableRoute];

        $this->logger->expects(invocationRule: $this->once())->method(constraint: 'warning');
        $this->logger->expects(invocationRule: $this->once())->method(constraint: 'info')
            ->with('Some routes skipped during cache export', [
                'total_routes'      => 2,
                'exportable_routes' => 1,
                'skipped_routes'    => 1,
            ]);

        $filtered = $this->validator->filterExportable(routes: $routes);

        $this->assertCount(expectedCount: 1, haystack: $filtered);
        $this->assertSame(expected: $exportableRoute, actual: $filtered[0]);
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function allows_null_values_in_exportable_fields() : void
    {
        $route = new RouteDefinition(
            method    : 'GET',
            path      : '/test',
            action    : 'Controller@action',
            defaults  : ['optional' => null],
            attributes: ['meta' => null]
        );

        $this->assertTrue(condition: $this->validator->validate(route: $route));
    }

    /**
     * @test
     * @throws ReservedRouteNameException
     */
    public function allows_nested_scalar_arrays() : void
    {
        $route = new RouteDefinition(
            method    : 'GET',
            path      : '/test',
            action    : 'Controller@action',
            defaults  : ['nested' => ['key' => 'value', 'count' => 5]],
            attributes: ['config' => ['enabled' => true, 'options' => ['a', 'b']]]
        );

        $this->assertTrue(condition: $this->validator->validate(route: $route));
    }

    #[Override]
    protected function setUp() : void
    {
        $this->logger    = $this->createMock(LoggerInterface::class);
        $this->validator = new RouteExportValidator(logger: $this->logger);
    }
}
