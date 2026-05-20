<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\URI\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\HTTP\URI\System\Configuration\UriServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * UriServiceProviderTest — verifies UriServiceProvider boot behavior.
 *
 * The Uri class and all URI parsing/building logic is entirely static.
 * No DI registration is performed, so the provider is a no-op.
 */
final class UriServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private UriServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new UriServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_nothing(): void
    {
        // UriServiceProvider is a no-op — Uri is entirely static
        // Verify the container has no bindings from this provider
        $this->assertFalse($this->container->has('Avax\\Components\\HTTP\\URI\\System\\Uri'));
    }

    public function test_provider_boots_without_error(): void
    {
        // Boot should complete without throwing
        $this->provider->boot($this->container);

        // Verify no bindings were added
        $this->assertFalse($this->container->has('Avax\\Components\\HTTP\\URI\\System\\Uri'));
    }
}
