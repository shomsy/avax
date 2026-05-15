<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\ApiVersioning;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry;
use Avax\Components\HTTP\ApiVersioning\System\Configuration\ApiVersioningServiceProvider;
use Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * ApiVersioningServiceProviderTest — proves provider boot wiring and single source of truth.
 *
 * Verifies:
 * - ApiVersioningServiceProvider registers VersionRegistry as singleton
 * - Provider boot wires VersionRegistry into ApiVersion facade
 * - Facade uses provider-created registry (no independent lazy allocation)
 * - No second VersionRegistry source of truth exists
 * - Reset clears facade state and unconfigured usage fails clearly
 */
final class ApiVersioningServiceProviderTest extends TestCase
{
    private SimpleContainer $container;

    protected function setUp(): void
    {
        ApiVersion::reset();
        $this->container = new SimpleContainer();
    }

    protected function tearDown(): void
    {
        ApiVersion::reset();
    }

    public function test_unconfigured_facade_fails_clearly(): void
    {
        ApiVersion::reset();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ApiVersion registry not configured');

        ApiVersion::current();
    }

    public function test_provider_registers_version_registry(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);

        $registry = $this->container->make(VersionRegistry::class);

        $this->assertInstanceOf(VersionRegistry::class, $registry);
        $this->assertSame(1, $registry->current());
    }

    public function test_provider_boots_facade_with_provider_created_registry(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Facade works after provider boot
        $this->assertSame(1, ApiVersion::current());
        $this->assertSame([1], ApiVersion::supported());
    }

    public function test_no_second_registry_source_of_truth(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Resolve registry from container
        $containerRegistry = $this->container->make(VersionRegistry::class);
        $this->assertInstanceOf(VersionRegistry::class, $containerRegistry);

        // Mutate through container registry
        $containerRegistry->support(2);

        // Facade sees the same state — same instance
        $this->assertSame([1, 2], ApiVersion::supported());
    }

    public function test_reset_clears_facade_state_and_unconfigured_usage_fails(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        // Facade is usable
        $this->assertSame(1, ApiVersion::current());

        // Reset clears static state
        ApiVersion::reset();

        // Unconfigured usage fails clearly
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ApiVersion registry not configured');

        ApiVersion::current();
    }

    public function test_facade_uses_provider_singleton_not_new_instance(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);

        // Resolve before boot to get the registered singleton
        $beforeBoot = $this->container->make(VersionRegistry::class);
        $this->assertInstanceOf(VersionRegistry::class, $beforeBoot);
        $beforeBoot->support(99);

        $provider->boot($this->container);

        // Facade should see the pre-boot mutation, proving same instance
        $this->assertContains(99, ApiVersion::supported());
    }

    public function test_provider_respects_custom_config(): void
    {
        $this->container->bind('config', static fn () => [
            'api.versioning.current' => 3,
            'api.versioning.supported' => [1, 2, 3],
        ]);

        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        $this->assertSame(3, ApiVersion::current());
        $this->assertSame([1, 2, 3], ApiVersion::supported());
    }

    public function test_double_boot_fails(): void
    {
        $provider = new ApiVersioningServiceProvider();
        $provider->register($this->container);
        $provider->boot($this->container);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already configured');

        // Second boot attempts to call setInstance again
        $provider->boot($this->container);
    }
}
