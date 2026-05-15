<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Pipeline;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry;
use Avax\Components\Application\Pipeline\System\Configuration\PipelineServiceProvider;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;
use Closure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * PipelineServiceProviderTest — proves provider boot wiring and single source of truth.
 *
 * Verifies:
 * - PipelineServiceProvider registers HookRegistry as singleton
 * - Provider boot wires HookRegistry into Pipeline facade
 * - Facade uses provider-created registry (no independent lazy allocation)
 * - No second HookRegistry source of truth exists
 * - Reset clears facade state and unconfigured usage fails clearly
 */
final class PipelineServiceProviderTest extends TestCase
{
    private SimpleContainer $container;

    protected function setUp(): void
    {
        Pipeline::reset();
        $this->container = new SimpleContainer();
    }

    protected function tearDown(): void
    {
        Pipeline::reset();
    }

    public function test_unconfigured_facade_fails_clearly(): void
    {
        Pipeline::reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pipeline registry not configured');

        Pipeline::hooks();
    }

    public function test_provider_registers_hook_registry(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);

        $registry = $this->container->make(HookRegistry::class);

        $this->assertInstanceOf(HookRegistry::class, $registry);
    }

    public function test_provider_boots_facade_with_provider_created_registry(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Facade works after provider boot
        Pipeline::beforeRoute(static fn () => 'booted');

        $hooks = Pipeline::hooks();
        $this->assertArrayHasKey('beforeRoute', $hooks);
    }

    public function test_no_second_registry_source_of_truth(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Resolve registry from container
        $containerRegistry = $this->container->make(HookRegistry::class);
        $this->assertInstanceOf(HookRegistry::class, $containerRegistry);

        // Mutate through container registry directly
        $containerRegistry->add('beforeRoute', static fn () => 'direct');

        // Facade sees the same state — same instance
        $this->assertArrayHasKey('beforeRoute', Pipeline::hooks());
        $this->assertSame(1, Pipeline::hooks()['beforeRoute'] ? count(Pipeline::hooks()['beforeRoute']) : 0);
    }

    public function test_reset_clears_facade_state_and_unconfigured_usage_fails(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Facade is usable
        Pipeline::beforeRoute(static fn () => 'test');
        $this->assertNotEmpty(Pipeline::hooks());

        // Reset clears static state
        Pipeline::reset();

        // Unconfigured usage fails clearly
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pipeline registry not configured');

        Pipeline::hooks();
    }

    public function test_facade_uses_provider_singleton_not_new_instance(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);

        // Resolve before boot to get the registered singleton
        $beforeBoot = $this->container->make(HookRegistry::class);
        $this->assertInstanceOf(HookRegistry::class, $beforeBoot);
        $beforeBoot->add('beforeRoute', static fn () => 'pre-boot');

        $provider->boot($this->container);

        // Facade should see the pre-boot mutation, proving same instance
        $hooks = Pipeline::hooks();
        $this->assertArrayHasKey('beforeRoute', $hooks);
        $this->assertSame(1, count($hooks['beforeRoute']));
    }

    public function test_hook_execution_through_provider_wired_facade(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        $results = [];
        Pipeline::beforeRoute(static function () use (&$results) {
            $results[] = 'first';
        });
        Pipeline::beforeRoute(static function () use (&$results) {
            $results[] = 'second';
        });

        Pipeline::execute('beforeRoute');

        $this->assertSame(['first', 'second'], $results);
    }

    public function test_double_boot_fails(): void
    {
        $provider = new PipelineServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already configured');

        // Second boot attempts to call setInstance again
        $provider->boot($this->container);
    }
}
