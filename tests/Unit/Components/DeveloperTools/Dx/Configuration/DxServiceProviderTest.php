<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Dx\Configuration;

use Avax\Components\DeveloperTools\Dx\System\Configuration\DxServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class DxServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DxServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new DxServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_without_error(): void
    {
        // DxServiceProvider has no DI dependencies — all Dx classes are pure builders.
        // This test verifies the provider registers and boots without error.
        $this->assertTrue(true);
    }
}
