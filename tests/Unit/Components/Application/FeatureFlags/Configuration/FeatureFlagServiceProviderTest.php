<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\FeatureFlags\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Application\FeatureFlags\System\Configuration\FeatureFlagServiceProvider;
use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;
use Avax\Components\Application\FeatureFlags\System\PublicSurface\FlagStoreInterface;
use PHPUnit\Framework\TestCase;

final class FeatureFlagServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private FeatureFlagServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new FeatureFlagServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_in_memory_flag_store_resolves(): void
    {
        $store = $this->container->get(InMemoryFlagStore::class);

        $this->assertInstanceOf(InMemoryFlagStore::class, $store);
    }

    public function test_flag_store_interface_resolves(): void
    {
        $store = $this->container->get(FlagStoreInterface::class);

        $this->assertInstanceOf(FlagStoreInterface::class, $store);
        $this->assertInstanceOf(InMemoryFlagStore::class, $store);
    }
}
