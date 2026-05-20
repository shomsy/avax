<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence\Configuration;

use Avax\Components\DataStack\Persistence\System\Configuration\PersistenceServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class PersistenceServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private PersistenceServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new PersistenceServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_provider_registers_no_dependencies(): void
    {
        // PersistenceServiceProvider is currently empty — no dependencies to register.
        // This test verifies the provider can be instantiated, registered, and booted without error.
        $this->assertInstanceOf(PersistenceServiceProvider::class, $this->provider);
    }
}
