<?php

declare(strict_types=1);

use Avax\HTTP\Router\Routing\RouteBuilder;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteGroupContext;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use PHPUnit\Framework\TestCase;

/**
 * Core functionality tests - ensures basic components work without full DI.
 *
 * Focuses on the critical bug: RouteBuilder::make() calling static RouteGroupStack::apply().
 * This catches issues that integration tests miss due to mocking limitations.
 */
class RouterCoreFunctionalityTest extends TestCase
{
    /**
     * @test
     */
    public function route_builder_make_does_not_call_static_methods() : void
    {
        // This is the critical test - RouteBuilder::make() should NOT call RouteGroupStack::apply()
        // If it does, we'll get a fatal error since RouteGroupStack is now instance-based

        $builder = RouteBuilder::make(method: 'GET', path: '/test');

        $this->assertNotNull(actual: $builder);
        $this->assertInstanceOf(expected: RouteBuilder::class, actual: $builder);
        $this->assertEquals(expected: 'GET', actual: $builder->getMethod());
        $this->assertEquals(expected: '/test', actual: $builder->getPath());
    }

    /**
     * @test
     */
    public function route_group_stack_has_applyTo_method() : void
    {
        $stack = new RouteGroupStack();

        $builder = RouteBuilder::make(method: 'GET', path: '/test');
        $result  = $stack->applyTo(builder: $builder);

        // Should return the same builder when no groups are active
        $this->assertSame(expected: $builder, actual: $result);
    }

    /**
     * @test
     */
    public function route_group_stack_snapshot_restore_works() : void
    {
        $stack = new RouteGroupStack();

        $initialState = $stack->snapshot();

        // Add a context
        $context = new RouteGroupContext();
        $stack->push(group: $context);

        $this->assertEquals(expected: 1, actual: $stack->depth());

        // Restore
        $stack->restore(stack: $initialState);

        $this->assertEquals(expected: 0, actual: $stack->depth());
        $this->assertTrue(condition: $stack->isEmpty());
    }

    /**
     * @test
     */
    public function route_builder_fluent_api_works() : void
    {
        $builder = RouteBuilder::make(method: 'POST', path: '/users')
            ->action(action: 'UserController@store')
            ->middleware(middleware: ['auth', 'validate'])
            ->name(name: 'users.store')
            ->where(parameter: 'id', pattern: '\d+')
            ->defaults(defaults: ['format' => 'json']);

        $definition = $builder->build();

        $this->assertInstanceOf(expected: RouteDefinition::class, actual: $definition);
        $this->assertEquals(expected: 'POST', actual: $definition->method);
        $this->assertEquals(expected: '/users', actual: $definition->path);
        $this->assertEquals(expected: 'UserController@store', actual: $definition->action);
        $this->assertEquals(expected: ['auth', 'validate'], actual: $definition->middleware);
        $this->assertEquals(expected: 'users.store', actual: $definition->name);
        $this->assertEquals(expected: ['id' => '\d+'], actual: $definition->constraints);
        $this->assertEquals(expected: ['format' => 'json'], actual: $definition->defaults);
    }

    /**
     * @test
     */
    public function route_constraint_validation_without_suppressions() : void
    {
        // This test ensures regex validation works without @ suppressions
        $builder = RouteBuilder::make(method: 'GET', path: '/users/{id}');

        // Valid constraint should work
        $builder->where(parameter: 'id', pattern: '\d+');
        $this->assertEquals(expected: ['id' => '\d+'], actual: $builder->getConstraints());

        // Invalid constraint should throw exception (not suppressed)
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Invalid constraint regex');
        $builder->where(parameter: 'id', pattern: '[invalid');
    }
}