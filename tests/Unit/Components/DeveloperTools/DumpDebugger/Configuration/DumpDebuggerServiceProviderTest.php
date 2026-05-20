<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\DumpDebugger\Configuration;

use Avax\Components\DeveloperTools\DumpDebugger\System\Configuration\DumpDebuggerServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class DumpDebuggerServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DumpDebuggerServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new DumpDebuggerServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_no_dependencies(): void
    {
        // DumpDebuggerServiceProvider is not yet implemented — no DI dependencies to register.
        // This test verifies the provider can be instantiated, registered, and booted without error.
        $this->assertInstanceOf(DumpDebuggerServiceProvider::class, $this->provider);
    }
}
