<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Context\Configuration;

use Avax\Components\HTTP\Context\System\Capabilities\Globals\GlobalsProviderInterface;
use Avax\Components\HTTP\Context\System\Configuration\HttpContextServiceProvider;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContext;
use Avax\Components\HTTP\Context\System\PublicSurface\HttpContextInterface;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class HttpContextServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private HttpContextServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new HttpContextServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_globals_provider_interface_resolves(): void
    {
        $provider = $this->container->get(GlobalsProviderInterface::class);

        $this->assertInstanceOf(GlobalsProviderInterface::class, $provider);
    }

    public function test_http_context_interface_resolves(): void
    {
        $context = $this->container->get(HttpContextInterface::class);

        $this->assertInstanceOf(HttpContextInterface::class, $context);
    }

    public function test_http_context_concrete_resolves(): void
    {
        $context = $this->container->get(HttpContext::class);

        $this->assertInstanceOf(HttpContext::class, $context);
    }
}
