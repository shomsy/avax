<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\Contracts\Configuration;

use Avax\Components\API\Contracts\System\Configuration\ContractsServiceProvider;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class ContractsServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private ContractsServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new ContractsServiceProvider();
    }

    public function test_register_does_not_throw(): void
    {
        // Contracts component contains interfaces only — register is a no-op
        $this->provider->register($this->container);
        $this->provider->boot($this->container);

        // No exceptions means the provider behaved correctly
        $this->assertTrue(true);
    }

    public function test_provider_implements_service_provider_interface(): void
    {
        $this->assertInstanceOf(
            \Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider::class,
            $this->provider,
        );
    }
}
