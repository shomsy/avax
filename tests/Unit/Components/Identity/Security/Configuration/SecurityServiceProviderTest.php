<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Security\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;
use Avax\Components\Identity\Security\System\Configuration\SecurityServiceProvider;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApplySecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApproveSecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\BeginSecurityChange;
use Avax\Components\Identity\Security\System\PublicSurface\Security;
use Avax\Components\Identity\Security\System\PublicSurface\SecurityInterface;
use PHPUnit\Framework\TestCase;

final class SecurityServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private SecurityServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new SecurityServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_security_configuration_store_resolves(): void
    {
        $store = $this->container->get(SecurityConfigurationStore::class);

        $this->assertInstanceOf(SecurityConfigurationStore::class, $store);
    }

    public function test_begin_security_change_resolves(): void
    {
        $flow = $this->container->get(BeginSecurityChange::class);

        $this->assertInstanceOf(BeginSecurityChange::class, $flow);
    }

    public function test_approve_security_change_resolves(): void
    {
        $flow = $this->container->get(ApproveSecurityChange::class);

        $this->assertInstanceOf(ApproveSecurityChange::class, $flow);
    }

    public function test_apply_security_change_resolves(): void
    {
        $flow = $this->container->get(ApplySecurityChange::class);

        $this->assertInstanceOf(ApplySecurityChange::class, $flow);
    }

    public function test_security_interface_resolves(): void
    {
        $security = $this->container->get(SecurityInterface::class);

        $this->assertInstanceOf(SecurityInterface::class, $security);
        $this->assertInstanceOf(Security::class, $security);
    }
}
