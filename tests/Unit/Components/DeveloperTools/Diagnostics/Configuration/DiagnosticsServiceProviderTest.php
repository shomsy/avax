<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Diagnostics\Configuration;

use Avax\Components\DeveloperTools\Diagnostics\System\Configuration\DiagnosticsServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class DiagnosticsServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DiagnosticsServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new DiagnosticsServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_no_dependencies(): void
    {
        // DiagnosticsServiceProvider is currently empty — all Diagnostics classes use static methods only.
        // This test verifies the provider can be instantiated, registered, and booted without error.
        $this->assertInstanceOf(DiagnosticsServiceProvider::class, $this->provider);
    }
}
