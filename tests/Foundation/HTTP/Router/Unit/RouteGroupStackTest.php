<?php

declare(strict_types=1);

use Avax\HTTP\Router\Routing\RouteGroupContext;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use PHPUnit\Framework\TestCase;

/**
 * Tests for instance-based RouteGroupStack functionality.
 *
 * Ensures proper stack management and exception safety.
 */
class RouteGroupStackTest extends TestCase
{
    private RouteGroupStack $stack;

    protected function setUp() : void
    {
        $this->stack = new RouteGroupStack;
    }

    /**
     * @test
     */
    public function stack_starts_empty() : void
    {
        $this->assertTrue(condition: $this->stack->isEmpty());
        $this->assertEquals(expected: 0, actual: $this->stack->depth());
        $this->assertNull(actual: $this->stack->current());
    }

    /**
     * @test
     */
    public function can_push_and_pop_contexts() : void
    {
        $context1 = new RouteGroupContext;
        $context2 = new RouteGroupContext;

        // Push first context
        $this->stack->push(group: $context1);
        $this->assertFalse(condition: $this->stack->isEmpty());
        $this->assertEquals(expected: 1, actual: $this->stack->depth());
        $this->assertSame(expected: $context1, actual: $this->stack->current());

        // Push second context
        $this->stack->push(group: $context2);
        $this->assertEquals(expected: 2, actual: $this->stack->depth());
        $this->assertSame(expected: $context2, actual: $this->stack->current());

        // Pop second context
        $this->stack->pop();
        $this->assertEquals(expected: 1, actual: $this->stack->depth());
        $this->assertSame(expected: $context1, actual: $this->stack->current());

        // Pop first context
        $this->stack->pop();
        $this->assertTrue(condition: $this->stack->isEmpty());
        $this->assertNull(actual: $this->stack->current());
    }

    /**
     * @test
     */
    public function snapshot_and_restore_preserves_state() : void
    {
        $context1 = new RouteGroupContext;
        $context2 = new RouteGroupContext;

        // Build initial state
        $this->stack->push(group: $context1);
        $this->stack->push(group: $context2);

        // Take snapshot
        $snapshot = $this->stack->snapshot();
        $this->assertCount(expectedCount: 2, haystack: $snapshot);

        // Modify stack
        $this->stack->pop();
        $this->assertEquals(expected: 1, actual: $this->stack->depth());

        // Restore snapshot
        $this->stack->restore(stack: $snapshot);
        $this->assertEquals(expected: 2, actual: $this->stack->depth());
        $this->assertSame(expected: $context2, actual: $this->stack->current());
    }

    /**
     * @test
     */
    public function clear_resets_stack() : void
    {
        $context = new RouteGroupContext;
        $this->stack->push(group: $context);

        $this->assertFalse(condition: $this->stack->isEmpty());

        $this->stack->clear();

        $this->assertTrue(condition: $this->stack->isEmpty());
        $this->assertEquals(expected: 0, actual: $this->stack->depth());
    }

    /**
     * @test
     */
    public function exception_in_nested_operation_leaves_stack_clean() : void
    {
        $context1 = new RouteGroupContext;
        $context2 = new RouteGroupContext;

        // Simulate the group() method pattern
        $this->stack->push(group: $context1);

        try {
            $this->stack->push(group: $context2);
            // Simulate an exception during group processing
            throw new RuntimeException(message: 'Simulated exception in group callback');
        } finally {
            // This should always run to clean up the stack
            $this->stack->pop(); // Pop context2
            $this->stack->pop(); // Pop context1
        }

        // Stack should be clean even after exception
        $this->assertTrue(condition: $this->stack->isEmpty());
        $this->assertEquals(expected: 0, actual: $this->stack->depth());
    }

    /**
     * @test
     */
    public function multiple_instances_are_isolated() : void
    {
        $stack1 = new RouteGroupStack;
        $stack2 = new RouteGroupStack;

        $context = new RouteGroupContext;

        // Modify stack1
        $stack1->push(group: $context);
        $this->assertEquals(expected: 1, actual: $stack1->depth());
        $this->assertTrue(condition: $stack2->isEmpty());

        // Modify stack2
        $stack2->push(group: $context);
        $this->assertEquals(expected: 1, actual: $stack1->depth());
        $this->assertEquals(expected: 1, actual: $stack2->depth());
    }
}
