<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\DataTransfer\Configuration;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class DataTransferServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private DataTransferServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new DataTransferServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_no_dependencies(): void
    {
        // DataTransferServiceProvider is currently empty — no dependencies to register.
        // This test verifies the provider can be instantiated, registered, and booted without error.
        $this->assertInstanceOf(DataTransferServiceProvider::class, $this->provider);
    }
}
