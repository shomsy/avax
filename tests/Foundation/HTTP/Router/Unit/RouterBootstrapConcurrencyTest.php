<?php

declare(strict_types=1);

use Avax\HTTP\Router\System\Flows\BootstrapRoutes\State\RouterBootstrapState;
use Avax\Tests\TestCase;

/**
 * Tests for RouterBootstrapState thread-safety.
 *
 * Ensures bootstrap state cannot be executed multiple times.
 */
class RouterBootstrapConcurrencyTest extends TestCase
{
    private RouterBootstrapState $bootstrapState;

    /**
     * @test
     */
    public function allows_first_bootstrap_execution() : void
    {
        // Should not throw exception
        $this->bootstrapState->ensureNotBooted();
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     */
    public function prevents_duplicate_bootstrap_execution() : void
    {
        // First bootstrap should succeed
        $this->bootstrapState->ensureNotBooted();

        // Second bootstrap should fail
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Router bootstrapper has already been executed');

        $this->bootstrapState->ensureNotBooted();
    }

    /**
     * @test
     */
    public function reset_allows_new_bootstrap_after_cleanup() : void
    {
        // Bootstrap once
        $this->bootstrapState->ensureNotBooted();

        // Reset state (like in test teardown)
        $this->bootstrapState->reset();

        // Should allow bootstrap again
        $this->bootstrapState->ensureNotBooted();
        $this->assertTrue(condition: true);
    }

    /**
     * @test
     */
    public function is_booted_returns_correct_state() : void
    {
        $this->assertFalse(condition: $this->bootstrapState);

        $this->bootstrapState->ensureNotBooted();

        $this->assertTrue(condition: $this->bootstrapState);
    }

    /**
     * @test
     */
    public function reset_sets_booted_state_to_false() : void
    {
        $this->bootstrapState->ensureNotBooted();
        $this->assertTrue(condition: $this->bootstrapState);

        $this->bootstrapState->reset();
        $this->assertFalse(condition: $this->bootstrapState);
    }

    /**
     * @test
     */
    public function multiple_instances_are_independent() : void
    {
        $state1 = new RouterBootstrapState;
        $state2 = new RouterBootstrapState;

        // Bootstrap first instance
        $state1->ensureNotBooted();
        $this->assertTrue(condition: $state1->booted);
        $this->assertFalse(condition: $state2->booted);

        // Bootstrap second instance
        $state2->ensureNotBooted();
        $this->assertTrue(condition: $state1->booted);
        $this->assertTrue(condition: $state2->booted);

        // Reset first instance
        $state1->reset();
        $this->assertFalse(condition: $state1->booted);
        $this->assertTrue(condition: $state2->booted);
    }

    #[Override]
    protected function setUp() : void
    {
        $this->bootstrapState = new RouterBootstrapState;
    }
}
