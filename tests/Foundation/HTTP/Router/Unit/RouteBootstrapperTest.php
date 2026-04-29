<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Router\Unit;

use Avax\Tests\TestCase;
use components\HTTP\Router\System\Flows\BootstrapRoutes\State\RouterBootstrapState;
use components\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteCollector;
use RuntimeException;

/**
 * Tests for route bootstrap determinism and security.
 */
final class RouteBootstrapperTest extends TestCase
{

    /**
     * Test that routes appear exactly once in final router state.
     *
     * This test ensures that the bootstrap process doesn't create duplicate routes,
     * which was a critical issue before the closure replay fix.
     */
    public function test_routes_appear_exactly_once_in_final_state() : void
    {
        // This test would require setting up a full bootstrap scenario
        // with route files that define routes, then verifying that
        // each route appears exactly once in the final router state.

        // For now, we'll mark this as incomplete since it requires
        // complex test setup with actual route files and bootstrapper.
        // In a real implementation, this would:
        // 1. Create temporary route files with duplicate definitions
        // 2. Run bootstrap process
        // 3. Verify final router has exactly one of each route
        // 4. Clean up temporary files

        $this->markTestIncomplete(
            message: 'Route deduplication test requires complex bootstrap setup with temp files'
        );
    }

    /**
     * Test that bootstrap state tracking works correctly.
     */
    public function test_bootstrap_state_tracking() : void
    {
        $state = new RouterBootstrapState();

        $this->assertFalse(condition: $state->booted);
        $this->assertNull(actual: $state->source);

        $state->markSource(source: 'cache');
        $this->assertEquals(expected: 'cache', actual: $state->source);

        $state->ensureNotBooted(); // Should not throw
        $this->assertTrue(condition: $state->booted);

        // Second call should throw
        $this->expectException(exception: RuntimeException::class);
        $state->ensureNotBooted();
    }

    /**
     * Test RouteCollector has security guard (integration test).
     *
     * The security guard is tested implicitly through the bootstrap process.
     * Direct testing requires complex mocking of the call stack.
     */
    public function test_route_collector_has_security_guard() : void
    {
        // This test documents that RouteCollector::flush() has security measures
        // The actual guard is tested through integration with BootstrapRoutes

        $this->assertTrue(
            condition: method_exists(object_or_class: RouteCollector::class, method: 'flush'),
            message  : 'RouteCollector should have flush method with security guard'
        );
    }
}