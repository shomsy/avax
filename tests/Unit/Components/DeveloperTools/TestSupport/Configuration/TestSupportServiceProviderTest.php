<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\TestSupport\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\DeveloperTools\TestSupport\System\Configuration\TestSupportServiceProvider;
use PHPUnit\Framework\TestCase;

final class TestSupportServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private TestSupportServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new TestSupportServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_without_error(): void
    {
        // TestSupportServiceProvider has no DI dependencies — component not yet implemented.
        // This test verifies the provider registers and boots without error.
        $this->assertTrue(true);
    }
}
